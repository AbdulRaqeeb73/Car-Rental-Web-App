<?php
session_start();
include_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is not an admin (redirect to admin panel if they are)
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: ../admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Car Rental System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="../index.php">AR Car Rental</a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="dashboard.php">Browse Cars</a></li>
                <li><a href="bookings.php">My Bookings</a></li>
                <li><a href="profile.php">My Profile</a></li>
            </ul>
            <div class="auth-links">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="../includes/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </nav>
    </header>
    <main> 