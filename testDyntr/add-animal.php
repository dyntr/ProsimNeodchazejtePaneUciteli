<?php
session_start();
require_once 'DBC.php';

if (!isset($_SESSION['id'])) {
    header('Location: login-page.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle Delete Operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $animal_id = $_POST['animal_id'];
    $sql = "DELETE FROM Animal WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $animal_id, $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Handle Add Operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    $sql = "INSERT INTO Animal (name, type, owned, user_id, is_public) VALUES (?, ?, 0, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $name, $type, $_SESSION['id'], $is_public);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Animal</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add your styles here */
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h1>Add Animal</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Animal Name" required class="form-control mb-2">
        <input type="text" name="type" placeholder="Animal Type" required class="form-control mb-2">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_public" id="isPublic">
            <label class="form-check-label" for="isPublic">Make Public</label>
        </div>
        <button type="submit" name="add" class="btn">Add Animal</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
