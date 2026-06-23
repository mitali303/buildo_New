<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ company_setting('company_name') ?? 'BUILDO' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url("{{ asset('Logos/login_bg.jpg') }}") no-repeat center top fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            background: rgba(220, 220, 220, 0.65); /* light gray with transparency */
            padding: 2rem;
            backdrop-filter: blur(3px); /* optional glass effect */
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-header h3 {
            margin-bottom: 0.5rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header text-center">
        <img src="{{ asset('Logos/BUILDO.png') }}" 
            alt="Buildo Construction" 
            height="100">

        <!-- Optional text -->
        <!--
        <h3>Welcome Back</h3>
        <p class="text-muted">Login to your Buildo Construction account</p>
        -->
    </div>


    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="UserID" class="form-label">User Name</label>
            <input type="text" id="UserID" name="UserID" class="form-control @error('UserID') is-invalid @enderror" placeholder="Enter your User Name" value="{{ old('UserID') }}" required autofocus>
            @error('UserID')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="Password" class="form-label">Password</label>
            <input type="password" id="Password" name="Password" class="form-control @error('Password') is-invalid @enderror" placeholder="Enter your password" required>
            @error('Password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Remember Me</label>
        </div> -->

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Sign In</button>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
