<?php
// ==========================================================================================
// DATABASE CONNECTION
// ==========================================================================================
$conn = mysqli_connect("localhost", "root", "", "indonesian_tropical_species");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ==========================================================================================
// QUERY FUNCTION
// ==========================================================================================
function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)){
        $rows[] = $row;
    }
    return $rows;
}

// ==========================================================================================
// REGISTRASI USER
// ==========================================================================================
function registrasi($data){
    global $conn;
    
    // Sanitize input
    $username = strtolower(stripslashes($data["username"]));
    $email = strtolower(stripslashes($data["email"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $konfirpassword = mysqli_real_escape_string($conn, $data["konfirpassword"]);
    $job = mysqli_real_escape_string($conn, $data["job"]);

    // Cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if(mysqli_fetch_assoc($result)){
        echo "<script>alert('Username already in use!');</script>";
        return false;
    }

    // Cek email sudah ada atau belum
    $result = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if(mysqli_fetch_assoc($result)){
        echo "<script>alert('Email already in use!');</script>";
        return false;
    }

    // Cek konfirmasi password
    if($password !== $konfirpassword){
        echo "<script>alert('Confirm password does not match!');</script>";
        return false;
    }

    // Enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user baru ke database (sesuai struktur tabel users baru)
    $query = "INSERT INTO users (username, email, password, job) VALUES ('$username', '$email', '$password', '$job')";
    mysqli_query($conn, $query);
    
    return mysqli_affected_rows($conn);
}

// ==========================================================================================
// LOGIN USER
// ==========================================================================================
function login($username, $password) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $username);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if(mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if(password_verify($password, $user['password'])) {
            // Update last_login
            mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE idUser = {$user['idUser']}");
            return $user;
        }
    }
    return false;
}

// ==========================================================================================
// CEK LOGIN SESSION
// ==========================================================================================
function isLoggedIn() {
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

// ==========================================================================================
// GET CURRENT USER
// ==========================================================================================
function getCurrentUser() {
    global $conn;
    if(isset($_SESSION['idUser'])) {
        $id = $_SESSION['idUser'];
        $result = mysqli_query($conn, "SELECT * FROM users WHERE idUser = $id");
        return mysqli_fetch_assoc($result);
    }
    return null;
}