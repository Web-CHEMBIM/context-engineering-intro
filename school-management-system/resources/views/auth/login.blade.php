<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/feather.min.css">
    
    <!-- Cuba Admin Theme CSS -->
    <link rel="stylesheet" href="{{ asset('cuba-theme/css/cuba-theme.css') }}">
    
    <style>
        .auth-wrapper {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-700) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .auth-card {
            background: var(--white);
            border-radius: var(--rounded-xl);
            box-shadow: var(--shadow-2xl);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            border: none;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo h2 {
            color: var(--primary-600);
            font-family: var(--font-primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .auth-logo p {
            color: var(--secondary-600);
            font-size: 14px;
            margin: 0;
        }
        
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-floating > .form-control {
            height: 3rem;
            line-height: 1.25;
            padding-top: 1.125rem;
            padding-bottom: 0.375rem;
            border: 1px solid var(--secondary-300);
            border-radius: var(--rounded-md);
            transition: var(--transition-colors);
        }
        
        .form-floating > .form-control:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
        }
        
        .form-floating > label {
            color: var(--secondary-600);
            font-size: 14px;
            padding: 0.75rem;
        }
        
        .btn-login {
            background: var(--primary-500);
            border: none;
            color: var(--white);
            height: 3rem;
            font-weight: 600;
            font-size: 16px;
            border-radius: var(--rounded-md);
            transition: var(--transition-all);
            width: 100%;
        }
        
        .btn-login:hover {
            background: var(--primary-600);
            color: var(--white);
        }
        
        .btn-login:focus {
            background: var(--primary-600);
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
        }
        
        .divider {
            text-align: center;
            position: relative;
            margin: 1.5rem 0;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--secondary-200);
        }
        
        .divider span {
            background: var(--white);
            color: var(--secondary-500);
            padding: 0 1rem;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .demo-credentials {
            background: var(--secondary-50);
            border-radius: var(--rounded-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--secondary-200);
        }
        
        .demo-credentials h6 {
            font-size: 12px;
            color: var(--secondary-700);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .demo-credential {
            display: flex;
            justify-content: between;
            align-items: center;
            font-size: 11px;
            margin-bottom: 0.25rem;
        }
        
        .demo-credential:last-child {
            margin-bottom: 0;
        }
        
        .demo-credential .role {
            color: var(--secondary-600);
            font-weight: 500;
        }
        
        .demo-credential .email {
            color: var(--secondary-500);
            font-family: var(--font-mono);
        }
        
        @media (max-width: 576px) {
            .auth-card {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <h2>School MS</h2>
                <p>Management System</p>
            </div>
            
            <!-- Demo Credentials -->
            <div class="demo-credentials">
                <h6><i data-feather="key" style="width: 12px; height: 12px;"></i> 🔑 Click to Login</h6>
                <div class="demo-credential" onclick="fillCredentials('superadmin@school.edu')" style="cursor: pointer; padding: 4px; border-radius: 4px;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="role">🔹 SuperAdmin:</span>
                    <span class="email">superadmin@school.edu</span>
                </div>
                <div class="demo-credential" onclick="fillCredentials('admin@school.edu')" style="cursor: pointer; padding: 4px; border-radius: 4px;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="role">🔸 Admin:</span>
                    <span class="email">admin@school.edu</span>
                </div>
                <div class="demo-credential" onclick="fillCredentials('teacher@school.edu')" style="cursor: pointer; padding: 4px; border-radius: 4px;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="role">🔸 Teacher:</span>
                    <span class="email">teacher@school.edu</span>
                </div>
                <div class="demo-credential" onclick="fillCredentials('student@school.edu')" style="cursor: pointer; padding: 4px; border-radius: 4px;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="role">🔸 Student:</span>
                    <span class="email">student@school.edu</span>
                </div>
                <div class="text-center mt-2">
                    <small class="f-11 font-secondary">
                        <strong>Password:</strong> <code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px; font-weight: bold;">password</code> for all accounts
                        <br><span style="color: #059669;">👆 Click any email above to auto-fill login form</span>
                    </small>
                </div>
            </div>
            
            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email -->
                <div class="form-floating">
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           placeholder="name@example.com"
                           value="{{ old('email') }}" 
                           required 
                           autofocus>
                    <label for="email">Email Address</label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="form-floating">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Password"
                           required>
                    <label for="password">Password</label>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Remember Me -->
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label f-14" for="remember">
                        Remember me
                    </label>
                </div>
                
                <!-- Login Button -->
                <button type="submit" class="btn btn-login">
                    <i data-feather="log-in" style="width: 16px; height: 16px;" class="me-2"></i>
                    Sign In
                </button>
            </form>
            
            <!-- Additional Links -->
            @if (Route::has('password.request'))
            <div class="divider">
                <span>or</span>
            </div>
            
            <div class="text-center">
                <a href="{{ route('password.request') }}" class="f-14 font-primary text-decoration-none">
                    Forgot your password?
                </a>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/feather.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather Icons
            feather.replace();
        });

        // Auto-fill demo credentials function
        function fillCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            
            // Add visual feedback
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            emailField.style.backgroundColor = '#dcfce7';
            passwordField.style.backgroundColor = '#dcfce7';
            
            setTimeout(() => {
                emailField.style.backgroundColor = '';
                passwordField.style.backgroundColor = '';
            }, 1000);
        }
    </script>
</body>
</html>