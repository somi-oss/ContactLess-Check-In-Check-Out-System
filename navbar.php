<?php
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">HB Website</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="facilities.php">Facilities</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <b><?php echo $_SESSION["user_name"]; ?></b></span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="btn btn-primary">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
