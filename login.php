<?php
session_start();
require "function.php";

// Cek Cookie untuk auto-login
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])){
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];
    $result = mysqli_query($conn, "SELECT username FROM users WHERE idUser = $id");
    $data = mysqli_fetch_assoc($result);
    if ($data && $key === hash('sha256', $data['username'])){
        $_SESSION["login"] = true;
        $_SESSION["idUser"] = $id;
    }
}

// Redirect jika sudah login
if (isset($_SESSION["login"]) && $_SESSION["login"] === true){
    header("Location: index.php");
    exit;
}

$error = false;

// Proses Login
if (isset($_POST["login"])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $user = login($username, $password);
    
    if($user) {
        $_SESSION["login"] = true;
        $_SESSION["idUser"] = $user['idUser'];
        $_SESSION["username"] = $user['username'];
        $_SESSION["job"] = $user['job'];
        
        // Set cookie jika remember me dicentang
        if (isset($_POST["remember"])){
            setcookie('id', $user['idUser'], time() + 60 * 60 * 24 * 7); // 7 days
            setcookie('key', hash('sha256', $user['username']), time() + 60 * 60 * 24 * 7);
        }
        
        header("Location: index.php");
        exit;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AnimaBase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --deep-blue: #0369a1;
            --deep-blue-dark: #075985;
            --deep-blue-light: #0ea5e9;
            --deep-blue-subtle: #e0f2fe;
            --neutral-grey: #374151;
            --soft-white: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--deep-blue-dark) 0%, #0c4a6e 50%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--deep-blue) 0%, var(--deep-blue-dark) 100%);
            padding: 32px;
            text-align: center;
            color: white;
        }
        
        .login-logo {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        
        .login-logo svg {
            width: 36px;
            height: 36px;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .login-header p {
            font-size: 0.875rem;
            opacity: 0.85;
        }
        
        .login-body {
            padding: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--neutral-grey);
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--deep-blue);
            box-shadow: 0 0 0 3px var(--deep-blue-subtle);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            accent-color: var(--deep-blue);
        }
        
        .form-check-label {
            font-size: 0.875rem;
            color: var(--neutral-grey);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--deep-blue) 0%, var(--deep-blue-dark) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(3, 105, 161, 0.35);
        }
        
        .login-footer {
            text-align: center;
            padding: 24px 32px;
            background: var(--soft-white);
            border-top: 1px solid #e5e7eb;
        }
        
        .login-footer p {
            font-size: 0.875rem;
            color: var(--neutral-grey);
        }
        
        .login-footer a {
            color: var(--deep-blue);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="4" r="2"></circle>
                        <circle cx="18" cy="8" r="2"></circle>
                        <circle cx="20" cy="16" r="2"></circle>
                        <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
                    </svg>
                </div>
                <h1>AnimaBase</h1>
                <p>Indonesian Tropical Animal Database</p>
            </div>
            
            <div class="login-body">
                <?php if($error): ?>
                <div class="alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Incorrect username or password!
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input autocomplete="off" type="text" name="username" class="form-control" placeholder="Enter your username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input autocomplete="off" type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>
                    
                    <button type="submit" name="login" class="btn-login">Log in</button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>Don't have account yet? <a href="regist.php">Register now</a></p>
            </div>
        </div>
    </div>
</body>
</html>