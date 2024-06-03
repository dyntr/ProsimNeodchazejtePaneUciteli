<?php
session_start();
require_once 'DBC.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$type = $_GET['type'] ?? 'all';  // could be 'owned', 'wish', or 'all'
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';

$ownedCondition = ($type === 'owned') ? 1 : (($type === 'wish') ? 0 : '%');

// Prepare SQL query
$sql = "SELECT User.username, 
        GROUP_CONCAT(DISTINCT Animal.name ORDER BY Animal.name ASC) AS animal_names, 
        GROUP_CONCAT(DISTINCT Animal.image_path ORDER BY Animal.name ASC) AS image_paths,
        Animal.type
        FROM Animal
        JOIN User ON Animal.user_id = User.id
        WHERE Animal.is_public = 1
        AND (Animal.owned LIKE ?)
        AND (Animal.name LIKE ? OR Animal.type LIKE ?)
        GROUP BY User.username, Animal.type";

// Add sorting logic
$sql .= ($sort === 'name') ? " ORDER BY animal_names" : " ORDER BY Animal.type";

$stmt = mysqli_prepare($conn, $sql);
$like_search = '%' . $search . '%';
mysqli_stmt_bind_param($stmt, "sss", $ownedCondition, $like_search, $like_search);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Public Animal Lists</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            position: relative;
            z-index: 100;
        }

        h1 {
            color: #333;
            font-weight: 800;
            text-align: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-control, .btn {
            border-radius: 15px;
        }

        .btn {
            background-color: #FF4B2B;
            color: #FFFFFF;
            font-weight: bold;
            text-transform: uppercase;
            padding: 12px 45px;
        }

        .btn:hover {
            background-color: #FF416C;
        }

        .list-group-item {
            border-radius: 15px;
            margin-top: 10px;
            padding: 10px 20px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        .animal-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            margin-right: 15px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h1>Public Animal Lists</h1>

    <form class="search-bar" action="" method="GET">
        <input type="text" class="form-control" name="search" placeholder="Search by name or type" value="<?= htmlspecialchars($search); ?>">
        <select class="form-control" name="type">
            <option value="all" <?= $type === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="owned" <?= $type === 'owned' ? 'selected' : ''; ?>>Owned</option>
            <option value="wish" <?= $type === 'wish' ? 'selected' : ''; ?>>Wish</option>
        </select>
        <select class="form-control" name="sort">
            <option value="name" <?= $sort === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
            <option value="type" <?= $sort === 'type' ? 'selected' : ''; ?>>Sort by Type</option>
        </select>
        <button class="btn" type="submit">Search</button>
    </form>

    <ul class="list-group">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <li class='list-group-item'>
                <?php
                if (!empty($row['image_paths'])) {
                    $images = explode(',', $row['image_paths']);
                    foreach ($images as $image) {
                        echo "<img src='".htmlspecialchars($image)."' alt='Animal Image' class='animal-image' onclick='openModal(\"".htmlspecialchars($image)."\")'>";
                    }
                }
                ?>
                <div>
                    <strong>User:</strong> <?= htmlspecialchars($row['username']); ?><br>
                    <strong>Type:</strong> <?= htmlspecialchars($row['type']); ?><br>
                    <strong>Animals:</strong> <?= htmlspecialchars($row['animal_names']); ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img src="" id="modalImage" style="width:100%; height:auto;">
        <a href="" download="animal_image" class="btn btn-primary" style="width:100%; margin-top:10px;">Download</a>
    </div>
</div>

<script>
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("modalImage");

    function openModal(src) {
        modal.style.display = "block";
        modalImg.src = src;
        document.querySelector('.modal-content a').href = src;
    }

    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
