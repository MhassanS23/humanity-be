<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
@media only screen and (max-width: 600px) {
    .inner-body {
        width: 100% !important;
    }
}

@media only screen and (max-width: 500px) {
    .button {
        width: 100% !important;
    }
}
</style>
{{ $head ?? '' }}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{{ $header ?? '' }}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->

<td align="center" class="padTop">
    <a href="" style="display: inline-block;">
        <img src="https://res.cloudinary.com/daxtmjhf7/image/upload/v1740007073/Humanity__1_-removebg-preview_mamoz0.png" class="logo" alt="Humanity Logo">
    </a>
</td>
<tr>
<td class="content-cell">
    {{ Illuminate\Mail\Markdown::parse($slot) }}

    <!-- Social Media Section (Now inside content, NOT footer) -->
    <table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 20px;">
        <tr>
            <td align="left">
                <p style="font-size: 14px; color: #6c757d; font-weight:bold;">Stay connected with us:</p>
                <a href="https://instagram.com/yourpage" style="text-decoration: none;">
                    <img src="https://img.icons8.com/fluency/48/000000/instagram-new.png" alt="Instagram" width="24">
                </a>
                <a href="https://wa.me/yourwhatsapp" style="text-decoration: none;">
                    <img src="https://img.icons8.com/color/48/000000/whatsapp--v1.png" alt="WhatsApp" width="24">
                </a>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</td>
</tr>

{{ $footer ?? '' }}

</table>
</td>
</tr>
</table>
</body>
</html>
