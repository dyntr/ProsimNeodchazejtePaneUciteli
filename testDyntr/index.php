<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LandingPage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="stye.css"> <!-- Připojení externího CSS souboru -->
    <style>
        /* Vložené CSS ze style.css */
        @import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');

        body {
            background: #f6f5f7;
            font-family: 'Montserrat', sans-serif;
        }

        .container {
            margin-top: 50px; /* Posunutí obsahu dolů */
        }

        .header {
            font-weight: bold;
            margin: 0;
            color: #333; /* Barva textu */
        }

        .lead {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
            color: #333; /* Barva textu */
        }

        .card {
            margin-bottom: 20px; /* Odsazení mezi kartami */
        }

        .btn-dark {
            border-radius: 20px;
            border: 1px solid #FF4B2B;
            background-color: #FF4B2B;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
        }

        .btn-dark:hover {
            transform: scale(1.05); /* Zvětšení tlačítka při najetí myší */
        }

        .btn-dark:focus {
            outline: none; /* Odstranění orámování při zaměření */
        }
    </style>
</head>

<body>

<?php include 'navbar.php';?>

<div class="container">
    <h1 class="header animate__animated animate__fadeInDown">Vítejte u chovatelů zvířat</h1>
    <p class="lead animate__animated animate__fadeInUp">Jsme váš zdroj informací o chovatelích zvířat.</p>

    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card animate__animated animate__fadeInLeft">
                <img src="https://media.istockphoto.com/id/1307238003/photo/life-is-good-with-a-faithful-friend-by-your-side.jpg?s=612x612&w=0&k=20&c=8hIZN_g0-WGVfuybu2API5DjVAoNB6QkgcRsWYY3QVM=" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Chovatel 1</h5>
                    <p class="card-text">Informace o chovateli zvířat.</p>
                    <a href="#" class="btn btn-dark">Více informací</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card animate__animated animate__fadeInUp">
                <img src="https://media.istockphoto.com/id/1307238003/photo/life-is-good-with-a-faithful-friend-by-your-side.jpg?s=612x612&w=0&k=20&c=8hIZN_g0-WGVfuybu2API5DjVAoNB6QkgcRsWYY3QVM=" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Chovatel 2</h5>
                    <p class="card-text">Informace o chovateli zvířat.</p>
                    <a href="#" class="btn btn-dark">Více informací</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card animate__animated animate__fadeInRight">
                <img src="https://www.shutterstock.com/image-photo/young-woman-her-cute-jack-600nw-1674253951.jpg" class="card-img-top" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Chovatel 3</h5>
                    <p class="card-text">Informace o chovateli zvířat.</p>
                    <a href="#" class="btn btn-dark">Více informací</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

