<?php
session_start();
// Check login session
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
require 'function.php';

// Get species by slug
$slug = $_GET["slug"] ?? null;

if (!$slug) {
    header("Location: index.php");
    exit;
}

// Sanitize slug
$slug = mysqli_real_escape_string($conn, $slug);

// Get species data to delete image file
$data = query("SELECT * FROM anima_table WHERE slug = '$slug'");

if (empty($data)) {
    echo "<script>
        alert('Species not found!');
        document.location.href = 'index.php';
    </script>";
    exit;
}

$species = $data[0];
$imagePath = 'assets/IMG/' . $species['image'];

// Delete the species from database
$deleteQuery = "DELETE FROM anima_table WHERE slug = '$slug'";
$result = mysqli_query($conn, $deleteQuery);

if ($result && mysqli_affected_rows($conn) > 0) {
    // Delete image file if exists
    if (file_exists($imagePath) && !empty($species['image'])) {
        unlink($imagePath);
    }
    
    echo "<script>
        alert('Species deleted successfully!');
        document.location.href = 'index.php';
    </script>";
} else {
    $error = mysqli_error($conn);
    echo "<script>
        alert('Failed to delete species! Error: " . addslashes($error) . "');
        document.location.href = 'index.php';
    </script>";
}
?>
