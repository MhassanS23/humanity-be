<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->sendEmailVerificationNotification();

            return response()->json(['status' => 'success', 'message' => 'User registered. Please verify your email.'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Registration failed. Please try again later.'], 500);
        }
    }

    public function verify(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $frontendUrl = config('app.frontend_url') . "/email/verify";

            if (!URL::hasValidSignature($request)) {
                return Redirect::to("$frontendUrl?status=error&message=Invalid verification link");
            }

            if ($request->has('expires') && time() > $request->expires) {
                return Redirect::to("$frontendUrl?status=error&message=Verification link has expired");
            }

            if ($user->hasVerifiedEmail()) {
                return Redirect::to("$frontendUrl?status=error&message=Email already verified");
            }

            $user->markEmailAsVerified();

            return Redirect::to("$frontendUrl?status=success&message=Email successfully verified");
        } catch (\Exception $e) {
            return Redirect::to("$frontendUrl?status=error&message=Failed to verify email. Please try again later");
        }
    }

    public function resend(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json(['status' => 'error', 'message' => 'Email already verified.'], 400);
            }

            $user->sendEmailVerificationNotification();
            return response()->json(['status' => 'success', 'message' => 'Email verification link sent.'], 200);

        } catch (ThrottleRequestsException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.'
            ], 429);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to resend verification email. Please try again later.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            DB::beginTransaction();

            $user->token()->revoke();

            DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $user->token()->id)
            ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleAuthGoogleCallback()
    {
        try {
            if (request()->has('error')) {
                $error = request()->get('error');
                if ($error === 'access_denied') {
                    $backendUrl = env('APP_URL') . "api/auth/google";
                    return redirect()->to($backendUrl);
                }
                return response()->json(['error' => $error], 400);
            }
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')->stateless()->user();

            /** @var User $user */
                $user = User::query()
                ->firstOrCreate(
                    [
                        'email' => $socialiteUser->getEmail(),
                    ],
                    [
                        'google_id' => $socialiteUser->getId(),
                        'name' => $socialiteUser->getName(),
                        'google_name' => $socialiteUser->getName(),
                        'photo' => $socialiteUser->getAvatar(),
                        'email_verified_at' => Carbon::now(),
                    ]
                );

            return response()->json(['status' => 'error', 'user' => $socialiteUser], 200);
            $token = $user->createToken('authToken')->accessToken;

            $frontendUrl = env('APP_URL_FRONTEND') . "/auth/google?token=$token";
            return redirect()->to($frontendUrl);

        } catch (ClientException $e) {
            $statusCode = $e->getCode() ? $e->getCode() : 422;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }

    }
}
