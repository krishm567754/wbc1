<?php
require 'config.php';
if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else { $error = "Invalid Password"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Castrol SmartHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm border border-slate-200 text-center">
        <i class="fa-solid fa-oil-can text-red-600 text-4xl mb-3"></i>
        <h2 class="text-2xl font-bold text-slate-800 mb-6">Admin Login</h2>
        <?php if (isset($error)) echo "<p class='text-red-500 text-sm mb-4 font-bold'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" class="w-full border p-3 rounded-lg mb-4 outline-none focus:ring-2 focus:ring-red-600" placeholder="Enter Password" required>
            <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-lg font-bold hover:bg-slate-800">Login</button>
        </form>
        <a href="index.php" class="block mt-4 text-sm text-slate-500 hover:text-red-600">← Back to Dashboard</a>
    </div>
</body>
</html>