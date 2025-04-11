<?php
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM pengguna WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

$success_message = "";
$error_message = "";

function generateColorFromUsername($username) {
    $colors = [
        'google-blue' => '#4285F4',
        'google-red' => '#EA4335',
        'google-yellow' => '#FBBC05',
        'google-green' => '#34A853',
        'google-purple' => '#7B1FA2'
    ];
    $hash = md5($username);
    $colorIndex = hexdec(substr($hash, 0, 2)) % count($colors);
    $colorKeys = array_keys($colors);
    return $colorKeys[$colorIndex];
}

$avatarColor = generateColorFromUsername($user['username']);

if (isset($_POST['update_profile'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    
        if ($username != $user['username']) {
        $check_query = "SELECT * FROM pengguna WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Username sudah digunakan!";
        }
    }
    
    if (empty($error_message)) {
                $update_query = "UPDATE pengguna SET nama_lengkap = ?, username = ?";
        $params = [$nama_lengkap, $username];
        
                if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
            $params[] = $hashed_password;
        }
        
        $update_query .= " WHERE id = ?";
        $params[] = $user_id;
        
        $stmt = $conn->prepare($update_query);
        
                $types = str_repeat('s', count($params) - 1) . 'i';
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
                        $_SESSION['username'] = $username;
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            
            $success_message = "Profil berhasil diperbarui!";
            
                        $result = $conn->query($query);
            $user = $result->fetch_assoc();
            
                        $avatarColor = generateColorFromUsername($username);
        } else {
            $error_message = "Terjadi kesalahan saat memperbarui profil!";
        }
    }
}

$last_login = date("Y-m-d H:i:s"); ?>

<div class="max-w-5xl mx-auto">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profil Pengguna</h1>
            <div class="text-sm text-gray-500">
                <span>Dashboard</span>
                <span class="mx-2">›</span>
                <span>Profil</span>
            </div>
        </div>
        
        <div class="text-right">
            <div class="text-sm text-gray-500">Last Updated</div>
            <div class="text-sm font-medium text-gray-800"><?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
    </div>
    
    <?php if (!empty($success_message)) { ?>
        <div class="p-4 mb-6 rounded-lg flex items-center bg-google-green/10 text-google-green animate-fadeInDown">
            <span class="text-xl mr-3"><i class="fas fa-check-circle"></i></span>
            <span><?php echo $success_message; ?></span>
        </div>
    <?php } ?>
    
    <?php if (!empty($error_message)) { ?>
        <div class="p-4 mb-6 rounded-lg flex items-center bg-google-red/10 text-google-red animate-fadeInDown">
            <span class="text-xl mr-3"><i class="fas fa-exclamation-circle"></i></span>
            <span><?php echo $error_message; ?></span>
        </div>
    <?php } ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="col-span-1">
            <div class="bg-white rounded-lg google-shadow overflow-hidden">
                <div class="bg-<?php echo $avatarColor; ?> h-32 flex items-center justify-center">
                    <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center transform translate-y-10 border-4 border-white">
                        <span class="text-<?php echo $avatarColor; ?> text-4xl font-bold">
                            <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                        </span>
                    </div>
                </div>
                
                <div class="mt-12 p-6 text-center">
                    <h3 class="text-xl font-bold text-gray-800"><?php echo $user['nama_lengkap']; ?></h3>
                    
                    <?php if ($user['role'] == 'admin') { ?>
                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-purple-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Administrator
                        </span>
                    <?php } else { ?>
                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            User Biasa
                        </span>
                    <?php } ?>
                    
                    <div class="mt-6">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <p class="text-sm text-gray-500">Username</p>
                                <p class="text-lg font-medium text-gray-700"><?php echo $user['username']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="text-lg font-medium text-gray-700"><?php echo ucfirst($user['role']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-5 border-t">
                        <div class="text-sm text-gray-500 mb-2">Last Login</div>
                        <div class="text-sm font-medium text-gray-700"><?php echo $last_login; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="col-span-2">
            <div class="bg-white rounded-lg google-shadow p-6">
                <div class="flex items-center border-b pb-4 mb-6">
                    <div class="bg-google-blue/10 rounded-full p-2 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-google-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">Edit Profil</h3>
                </div>
                
                <form action="" method="post" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="nama_lengkap">
                                Nama Lengkap
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" 
                                    class="focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-3 border-gray-300 rounded-md" 
                                    value="<?php echo $user['nama_lengkap']; ?>" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="username">
                                Username
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-at"></i>
                                </div>
                                <input type="text" id="username" name="username" 
                                    class="focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-3 border-gray-300 rounded-md" 
                                    value="<?php echo $user['username']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="new_password">
                            Password Baru <span class="text-gray-400">(Kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="new_password" name="new_password" 
                                class="focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-3 border-gray-300 rounded-md">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Password minimal 6 karakter untuk keamanan yang lebih baik.
                        </p>
                    </div>
                    
                    <div class="border-t pt-6 flex justify-end">
                        <button type="submit" name="update_profile" class="inline-flex items-center px-6 py-3 border border-transparent rounded-full shadow-sm text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="mt-6 text-center text-gray-500 text-sm">
        Butuh bantuan? <a href="#" class="text-google-blue hover:underline">Kontak Administrator</a>
    </div>
</div>

<style>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeInDown {
    animation: fadeInDown 0.3s ease-out forwards;
}
</style>