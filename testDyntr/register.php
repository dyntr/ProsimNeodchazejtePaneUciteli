<?php
require_once 'DBC.php';

ob_start(); // Start output buffering

$db = Database::getInstance();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $check_sql = "SELECT * FROM User WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Username already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO User (username, password) VALUES (?, ?)";
        $insert_stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);
        if (mysqli_stmt_execute($insert_stmt)) {
            header("Location: auth-page.php"); // Redirect to login page after successful registration
            exit;
        } else {
            $error = "Error registering user.";
        }
        mysqli_stmt_close($insert_stmt);
    }
    mysqli_stmt_close($stmt);
}

ob_end_flush(); // Send output buffer and turn off output buffering
?>

<!-- Display registration errors -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Error</title>
</head>
<body>
<p><?php echo $error ?? "Please try registering again."; ?></p>
<a href="auth-page.php">Back to registration</a>
</body>
</html>
