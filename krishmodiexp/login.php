<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pass']) && $_POST['pass'] === "admin786") {
        $_SESSION['admin'] = true;
        // Redirecting to admin.php as requested
        header("Location: admin.php");
        exit(); // CRITICAL: Stops the rest of the page from loading after redirect
    } else {
        $error = "Incorrect password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #00824D; display: flex; align-items: center; height: 100vh; font-family: sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 card p-4 shadow-lg border-0">
                <h4 class="text-center text-success mb-4">Shri Laxmi Admin</h4>
                
                <?php if($error): ?>
                    <div class="alert alert-danger text-center p-2 mb-3" style="font-size: 0.85rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="password" name="pass" class="form-control mb-3" placeholder="Enter Password" required>
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>