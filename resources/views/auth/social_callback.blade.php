<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Logging in...') }}</title>
</head>
<body>
    <script>
        // Store the token in localStorage
        const token = "{{ $token }}";
        if (token) {
            localStorage.setItem('access_token', token);
        }
        // Redirect to the intended page
        window.location.href = "{{ $redirect }}";
    </script>
</body>
</html>
