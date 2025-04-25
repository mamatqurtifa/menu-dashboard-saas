<?php
session_start();
require_once 'db.php';

// Set default timezone to UTC to match database timezone
date_default_timezone_set('UTC');

$error = "";
$success = "";
$token_valid = false;
$token = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Verify token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_hash = hash('sha256', $token);
    
    // Improved query to check token expiration
    // This query compares server time (UTC) with the stored expiration time
    $query = "SELECT * FROM pengguna WHERE reset_token = ? AND reset_expires > UTC_TIMESTAMP()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_valid = true;
        $user = $result->fetch_assoc();
    } else {
        // Check if token exists but expired
        $check_token_query = "SELECT reset_expires FROM pengguna WHERE reset_token = ?";
        $check_stmt = $conn->prepare($check_token_query);
        $check_stmt->bind_param("s", $token_hash);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $token_data = $check_result->fetch_assoc();
            $error = "Token sudah kadaluarsa! Token berlaku hingga: " . $token_data['reset_expires'] . " UTC";
        } else {
            $error = "Token tidak valid!";
        }
    }
} else {
    header("Location: forgot_password.php");
    exit();
}

// Reset password
if (isset($_POST['update_password']) && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Update password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE pengguna SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $password_hash, $user['id']);
        
        if ($update_stmt->execute()) {
            $success = "Password berhasil diperbarui! Silakan <a href='index.php' class='text-blue-600 hover:underline'>login</a> dengan password baru Anda.";
        } else {
            $error = "Terjadi kesalahan saat memperbarui password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">Reset Password</h1>
        
        <?php if (!empty($error)) { ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php echo $error; ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($success)) { ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <?php echo $success; ?>
            </div>
        <?php } elseif ($token_valid) { ?>
            <p class="mb-4 text-gray-600">Silakan masukkan password baru Anda.</p>
            
            <form method="post" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password Baru
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="password" name="password" type="password" placeholder="Password Baru" required minlength="8">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                        Konfirmasi Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="confirm_password" name="confirm_password" type="password" placeholder="Konfirmasi Password" required minlength="8">
                </div>
                <div class="flex flex-col space-y-4">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                            type="submit" name="update_password">
                        Perbarui Password
                    </button>
                </div>
            </form>
        <?php } ?>
    </div>
</body>
</html>