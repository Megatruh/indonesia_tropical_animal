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
    <link rel="stylesheet" href="assets/css/login.css">
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