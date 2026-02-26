<?php
session_start();
require 'function.php';

// Redirect jika sudah login
if (isset($_SESSION["login"]) && $_SESSION["login"] === true){
    header("Location: index.php");
    exit;
}

$success = false;
$error = "";

if(isset($_POST['register'])){
    $result = registrasi($_POST);
    if($result > 0) {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AnimaBase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
  <div class="login-card">

    <!-- HEADER (sama persis seperti login) -->
    <div class="login-header">
      <div class="login-logo">
        <!-- icon sama -->
        
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="4" r="2"></circle>
            <circle cx="18" cy="8" r="2"></circle>
            <circle cx="20" cy="16" r="2"></circle>
            <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
        </svg>
      </div>

      <h1>AnimaBase</h1>
      <p>Create your account</p>
    </div>

    <!-- BODY -->
    <div class="login-body">
      <form action="regist_process.php" method="POST">

        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Job / Occupation</label>
          <input type="text" name="job" class="form-control" placeholder="e.g. Student, Researcher, Veterinarian" required>
        </div>

        <!-- pakai button style login -->
        <button type="submit" class="btn-login">
          Register
        </button>

      </form>
    </div>

    <!-- FOOTER -->
    <div class="login-footer">
      <p>
        Already have an account?
        <a href="login.php">Login now</a>
      </p>
    </div>

  </div>
</div>
</body>
</html>

