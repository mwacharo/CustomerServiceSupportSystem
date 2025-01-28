<!-- resources/views/emails/welcome.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Welcome, {{ $name }}!</h1>
    <p>Your account has been created successfully.</p>
    <p>Here are your login details:</p>
    <p>Email: {{ $email }}</p>
    <p>Password: {{ $password }}</p>
    <!-- <p>Please change your password after logging in for the first time.</p> -->
    <p><a href="{{ url('/login') }}" class="button">Login</a></p>
    <p>Thank you for joining us!</p>
</body>
</html>
