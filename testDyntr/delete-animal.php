<?php
session_start();
require_once 'DBC.php';

if (!isset($_SESSION['id'])) {
    header('Location: login-page.php');
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $animal_id = $_POST['animal_id'];

    $sql = "DELETE FROM Animal WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $animal_id, $_SESSION['id']);
    if (mysqli_stmt_execute($stmt)) {
        echo "Animal deleted successfully!";
    } else {
        echo "Error: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
    header('Location: owned-list.php'); // Redirect as appropriate
}
?>
