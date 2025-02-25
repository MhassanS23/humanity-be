<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ValidateOAuthRequest
{
    public function handle(Request $request, Closure $next)
{
    try {
        if ($request->is('oauth/token')) {
            $validator = Validator::make($request->all(), [
                'grant_type' => 'required|string|in:password,refresh_token'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
            }

            if ($request->grant_type === 'password') {
                $validator = Validator::make($request->all(), [
                    'username' => 'required|string|email',
                    'password' => 'required|string|min:8',
                ]);

                if ($validator->fails()) {
                    return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
                }

                $user = User::where('email', $request->username)->firstOrFail();

                if (!Hash::check($request->password, $user->password)) {
                    return response()->json(['status' => 'error', 'message' => 'Wrong password, Please try again!'], 401);
                }

                if (!$user->hasVerifiedEmail()) {
                    return response()->json(['status' => 'error', 'message' => 'Email not verified, Please verify now!'], 403);
                }
            }

            $client = DB::table('oauth_clients')
                ->where('password_client', true)
                ->first();

            if (!$client) {
                return response()->json(['status' => 'error', 'message' => 'OAuth Client not found!'], 500);
            }

            $request->merge([
                'client_id'     => $client->id,
                'client_secret' => $client->secret,
            ]);
        }

        return $next($request);
    } catch (ModelNotFoundException $e) {
        return response()->json(['status' => 'error', 'message' => 'Email not found, Please try again!'], 404);
    } catch (Throwable $e) {
        return response()->json(['status' => 'error', 'message' => 'Something went wrong!', 'error' => $e->getMessage()], 500);
    }
}

}
