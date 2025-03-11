<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - My PHP Website</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Welcome to My PHP Website</h1>
    </header>

    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>

    <section>
        <h2>Today’s Date & Time</h2>
        <p>
            <?php
                echo "Current server time: " . date("Y-m-d H:i:s");
            ?>
        </p>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> My PHP Website. All Rights Reserved.</p>
    </footer>
</body>
</html>