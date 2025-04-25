<?php
session_start();
require_once 'db.php';

// Set default timezone to UTC to match database timezone
date_default_timezone_set('UTC');

$error = "";
$success = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['reset'])) {
    $username = $_POST['username'];
    
    // Check if username exists
    $query = "SELECT * FROM pengguna WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate random token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token);
        
        // Expiration time: 24 hours from now (UTC)
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Store token in DB
        $update_query = "UPDATE pengguna SET reset_token = ?, reset_expires = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $token_hash, $expires, $user['id']);
        
        if ($update_stmt->execute()) {
            // In a real app, you'd send an email here
            // For demo purposes, we'll show the reset link directly
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            $user_friendly_expires = date('Y-m-d H:i:s', strtotime($expires) + 7*3600); // Convert to GMT+7 for display
            $success = "Link reset password: <a href='$reset_link' class='text-blue-600 hover:underline'>Klik di sini</a><br>
                       <small class='text-gray-500'>Link berlaku hingga: " . $user_friendly_expires . " (waktu lokal GMT+7)</small>";
            
            // In production, you'd email the link instead of displaying it:
            // $to = $user['email'];
            // $subject = "Reset Password";
            // $message = "Klik link berikut untuk reset password Anda: $reset_link\nLink berlaku hingga: $user_friendly_expires (waktu lokal GMT+7)";
            // mail($to, $subject, $message);
            // $success = "Instruksi reset password telah dikirim ke email Anda.";
        } else {
            $error = "Terjadi kesalahan saat memproses permintaan reset password.";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-6">Lupa Password</h1>
        
        <?php if (!empty($error)) { ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php echo $error; ?>
            </div>
        <?php } ?>
        
        <?php if (!empty($success)) { ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <?php echo $success; ?>
            </div>
        <?php } else { ?>
            <p class="mb-4 text-gray-600">Masukkan username Anda untuk menerima link reset password.</p>
            
            <form method="post" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="username" name="username" type="text" placeholder="Username" required>
                </div>
                <div class="flex flex-col space-y-4">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                            type="submit" name="reset">
                        Kirim Link Reset
                    </button>
                    <p class="text-center">
                        <a href="login.php" class="text-blue-600 hover:underline">Kembali ke Login</a>
                    </p>
                </div>
            </form>
        <?php } ?>
    </div>
</body>
</html>