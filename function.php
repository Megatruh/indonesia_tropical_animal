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

// ==========================================================================================
// GENERATE SLUG FROM NAME
// ==========================================================================================
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove special characters
    $slug = preg_replace('/[\s-]+/', '-', $slug); // Replace spaces and multiple dashes with single dash
    $slug = trim($slug, '-'); // Trim dashes from ends
    return $slug;
}

// fungsi untuk mengupload gambar yang akan digunakan untuk tambah atau ubah data -----------------------------------------------------
function upload(){
    $namaFile = $_FILES["gambar"]["name"];
    $ukuranFile = $_FILES["gambar"]["size"];// menggunaan satuan byte, 1 kb = 1000 byte, 1 mb = 1000000 byte
    $errorFile =  $_FILES["gambar"]["error"];
    $tempFile = $_FILES["gambar"]["tmp_name"];

    // cek apakah tidak ada gambar yang diupload
    if ($errorFile === 4){
        echo "<script>
        alert('Please select an image to upload!')
        </script>";
        return false;
    }

    //cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];//ekstensi yang diizinkan
    $ekstensiGambar = explode('.',$namaFile);// mengambil ekstensi file yang diupload
    $ekstensiGambar = strtolower(end($ekstensiGambar));// menjadikan ekstensi yang sudah diambil menjadi lowcase

    if(!in_array($ekstensiGambar, $ekstensiGambarValid)){
        echo "<script>
        alert('Invalid file type! Please upload JPG, JPEG, or PNG only.')
        </script>";
        return false;
    }

    //cek jika ukurannya terlalu besar
    if($ukuranFile > 5000000 ) {
        echo "<script>
        alert('File size too large! Maximum size is 5MB.')
        </script>";
        return false;
    }

    //generate nama gambar baru untuk mencegah penumpaan foto
    $namaFileBaru = uniqid();
    $namaFileBaru .='.'  ;
    $namaFileBaru  .= $ekstensiGambar;

    //lolos pengecekan
    move_uploaded_file($tempFile, 'assets/IMG/'.$namaFileBaru);

    return $namaFileBaru;
}

// ==========================================================================================
// TAMBAH HEWAN/SPECIES BARU
// ==========================================================================================
function tambahHewan($data){
    global $conn;
    
    // Ambil data dari form
    $name = htmlspecialchars($data["name"]);
    $habitat = htmlspecialchars($data["habitat"]);
    $describe = htmlspecialchars($data["describe"]);
    $kingdom = htmlspecialchars($data["kingdom"] ?? 'Animalia');
    $pylum = htmlspecialchars($data["pylum"] ?? '');
    $class = htmlspecialchars($data["class"] ?? '');
    $ordo = htmlspecialchars($data["ordo"] ?? '');
    $famili = htmlspecialchars($data["famili"] ?? '');
    $genus = htmlspecialchars($data["genus"] ?? '');
    $status = htmlspecialchars($data["status"]);
    $nationalPlanting = htmlspecialchars($data["nationalPlanting"] ?? '');
    $internationalPlanting = htmlspecialchars($data["internationalPlanting"] ?? '');

    // Upload gambar
    $gambar = upload();
    if (!$gambar) {
        return false;
    }

    // Generate slug from name
    $slug = generateSlug($name);
    
    // Query insert data
    $query = "INSERT INTO anima_table (
                    name,
                    slug, 
                    image, 
                    habitat, 
                    `describe`, 
                    kingdom, 
                    pylum, 
                    `class`, 
                    ordo, 
                    famili, 
                    genus, 
                    status, 
                    nationalPlanting, 
                    internationalPlanting) 
              VALUES (
                    '$name',
                    '$slug', 
                    '$gambar', 
                    '$habitat', 
                    '$describe', 
                    '$kingdom', 
                    '$pylum', 
                    '$class', 
                    '$ordo', 
                    '$famili', 
                    '$genus', 
                    '$status', 
                    '$nationalPlanting', 
                    '$internationalPlanting')";
    
    mysqli_query($conn, $query);
    
    return mysqli_affected_rows($conn);
}

function ubah($data){
    global $conn;

    // Ambil data dari form
    $idAnima = htmlspecialchars($data["idAnima"]);
    $name = htmlspecialchars($data["name"]);
    $habitat = htmlspecialchars($data["habitat"]);
    $describe = htmlspecialchars($data["describe"]);
    $kingdom = htmlspecialchars($data["kingdom"] ?? 'Animalia');
    $pylum = htmlspecialchars($data["pylum"] ?? '');
    $class = htmlspecialchars($data["class"] ?? '');
    $ordo = htmlspecialchars($data["ordo"] ?? '');
    $famili = htmlspecialchars($data["famili"] ?? '');
    $genus = htmlspecialchars($data["genus"] ?? '');
    $status = htmlspecialchars($data["status"]);
    $nationalPlanting = htmlspecialchars($data["nationalPlanting"] ?? '');
    $internationalPlanting = htmlspecialchars($data["internationalPlanting"] ?? '');

    $gambarLama = htmlspecialchars($data["image"]);

    //cek apakah user pilih gambar baru atau tidak
    if( $_FILES['gambar']['error'] === 4 ){
        $image = $gambarLama;
    } else {
        $image = upload();
    }
  

    // Generate slug from name
    $slug = generateSlug($name);

    // Query update data
    $query = "UPDATE anima_table SET 
                name = '$name',
                slug = '$slug',
                habitat = '$habitat',
                `describe` = '$describe',
                kingdom = '$kingdom',
                pylum = '$pylum',
                `class` = '$class',
                ordo = '$ordo',
                famili = '$famili',
                genus = '$genus',
                status = '$status',
                nationalPlanting = '$nationalPlanting',
                internationalPlanting = '$internationalPlanting',
                image = '$image'
                WHERE idAnima = '$idAnima'";

    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

function cari($keyword){

    $query = "SELECT * FROM anima_table 
                WHERE 
                name LIKE '%$keyword%' OR
                slug LIKE '%$keyword%' OR
                habitat LIKE '%$keyword%' OR
                `describe` LIKE '%$keyword%' OR
                kingdom LIKE '%$keyword%' OR
                pylum LIKE '%$keyword%' OR
                `class` LIKE '%$keyword%' OR
                ordo LIKE '%$keyword%' OR
                famili LIKE '%$keyword%' OR
                genus LIKE '%$keyword%' OR
                status LIKE '%$keyword%' OR
                nationalPlanting LIKE '%$keyword%' OR
                internationalPlanting LIKE '%$keyword%' ";
    return query($query);
}