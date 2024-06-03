<?php
session_start();
require_once 'DBC.php';  // Ensure you have this file for database connection

$errors = [];

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
    $name = substr($_POST['name'], 0, 27);  // Limit name to 27 characters
    $type = substr($_POST['type'], 0, 27);  // Limit type to 27 characters
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (!empty($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "An error occurred while uploading the image. Error code: " . $_FILES['image']['error'];
        } else {
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                $errors[] = "File is not an image.";
            } else {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types)) {
                    $errors[] = "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
                } else {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . basename($_FILES["image"]["name"]);
                    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $errors[] = "Failed to move uploaded file.";
                    }
                }
            }
        }
    } else {
        $target_file = null;
    }

    if (empty($errors)) {
        $sql = "INSERT INTO Animal (name, type, image_path, owned, user_id, is_public) VALUES (?, ?, ?, 1, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssii", $name, $type, $target_file, $_SESSION['id'], $is_public);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Handle Edit Operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $animal_id = $_POST['animal_id'];
    $name = substr($_POST['edit_name'], 0, 27); // Limit to 27 characters
    $type = substr($_POST['edit_type'], 0, 27); // Limit to 27 characters
    $sql = "UPDATE Animal SET name = ?, type = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $name, $type, $animal_id, $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Search Operation
$search = $_GET['search'] ?? '';
$sql = "SELECT id, name, type, image_path, is_public FROM Animal WHERE user_id = ? AND owned = 1 AND (name LIKE ? OR type LIKE ?)";
$stmt = mysqli_prepare($conn, $sql);
$like_search = '%' . $search . '%';
mysqli_stmt_bind_param($stmt, "iss", $_SESSION['id'], $like_search, $like_search);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Owned Animals</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f6f5f7;
            font-family: 'Montserrat', sans-serif;
        }
        .container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 6px 10px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .btn {
            border-radius: 30px;
            background-color: #FF4B2B;
            color: #FFFFFF;
            padding: 12px 45px;
            text-transform: uppercase;
        }
        .btn:hover {
            background-color: #FF416C;
        }
        .list-group-item {
            border-radius: 15px;
            margin-top: 10px;
            transition: background-color 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .animal-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
            cursor: pointer;
        }
        .animal-details {
            flex-grow: 1;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .animal-name, .animal-type {
            max-width: 220px; /* Adjust width as per your layout needs */
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .image-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            border-radius: 8px;
            background: white;
            text-align: center;
            width: auto;
            max-width: 90%;
        }
        .image-modal img {
            max-width: 100%;
            max-height: 80vh;
        }
        .close-modal {
            position:absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5em;
            cursor: pointer;
        }
        .edit-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            padding: 20px;
            background-color: #FFF;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1050;
        }
        .edit-form-background, .image-modal-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
        @media (max-width: 576px) {
            .list-group-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .animal-details {
                margin-bottom: 10px;
            }
            .animal-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h1 class="text-center mb-3">Owned Animals</h1>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form class="d-flex mb-3" method="GET">
        <input class="form-control me-2" type="search" placeholder="Search by name or type" name="search" value="<?= htmlspecialchars($search); ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
    </form>

    <!-- Add Animal Form -->
    <form method="POST" enctype="multipart/form-data" class="mb-3">
        <input type="text" name="name" maxlength="24" placeholder="Animal Name" required class="form-control mb-2">
        <input type="text" name="type" maxlength="24" placeholder="Animal Type" required class="form-control mb-2">
        <input type="file" name="image" class="form-control mb-2">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_public" id="isPublic">
            <label class="form-check-label" for="isPublic">Make Public</label>
        </div>
        <button type="submit" name="add" class="btn">Add Animal</button>
    </form>

    <!-- Animal List -->
    <ul class="list-group">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <li class='list-group-item'>
                <div class="d-flex align-items-center">
                    <?php if (!empty($row['image_path'])): ?>
                        <img src="<?= htmlspecialchars($row['image_path']); ?>" alt="Animal Image" class="animal-image" onclick="showImageModal('<?= htmlspecialchars($row['image_path']); ?>')">
                    <?php endif; ?>
                    <div class="animal-details">
                        <strong>Name:</strong> <?= htmlspecialchars($row['name']); ?><br>
                        <strong>Type:</strong> <?= htmlspecialchars($row['type']); ?>
                    </div>
                </div>
                <div>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="animal_id" value="<?= $row['id']; ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                    <button type="button" onclick="editAnimal('<?= $row['id']; ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['type'], ENT_QUOTES); ?>')" class="btn btn-secondary btn-sm">Edit</button>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<!-- Image Modal (Hidden by Default) -->
<div class="image-modal-background" onclick="hideImageModal();">
    <div class="image-modal" onclick="event.stopPropagation();">
        <span class="close-modal" onclick="hideImageModal();">&times;</span>
        <img id="modalImage" src="" alt="Animal Image">
        <button class="btn btn-primary mt-3" onclick="downloadImage()">Download</button>
    </div>
</div>

<!-- Edit Form (Hidden by Default) -->
<div class="edit-form-background" onclick="hideEditForm();">
    <div class="edit-form" onclick="event.stopPropagation();">
        <form method="POST">
            <input type="hidden" name="animal_id" id="editAnimalId">
            <input type="text" name="edit_name" id="editName" placeholder="New Name" required class="form-control mb-2" maxlength="24">
            <input type="text" name="edit_type" id="editType" placeholder="New Type" required class="form-control mb-2" maxlength="24">
            <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function editAnimal(id, name, type) {
        document.getElementById('editAnimalId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editType').value = type;
        document.querySelector('.edit-form-background').style.display = 'block';
        document.querySelector('.edit-form').style.display = 'block';
    }

    function hideEditForm() {
        document.querySelector('.edit-form-background').style.display = 'none';
        document.querySelector('.edit-form').style.display = 'none';
    }

    function showImageModal(imagePath) {
        document.getElementById('modalImage').src = imagePath;
        document.querySelector('.image-modal-background').style.display = 'block';
        document.querySelector('.image-modal').style.display = 'block';
    }

    function hideImageModal() {
        document.querySelector('.image-modal-background').style.display = 'none';
        document.querySelector('.image-modal').style.display = 'none';
    }

    function downloadImage() {
        var imagePath = document.getElementById('modalImage').src;
        var link = document.createElement('a');
        link.href = imagePath;
        link.download = 'DownloadedImage.jpg';  // Provide the download filename
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>
