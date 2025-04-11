<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-semibold mb-6">Dashboard</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-blue-100 p-4 rounded-lg shadow">
            <?php
            $result = $conn->query("SELECT COUNT(*) as total FROM siswa");
            $jumlah_siswa = $result->fetch_assoc()['total'];
            ?>
            <h3 class="font-bold text-xl mb-2">Jumlah Siswa</h3>
            <div class="text-4xl font-bold text-blue-600"><?php echo $jumlah_siswa; ?></div>
            <a href="dashboard.php?page=menu&menu=siswa" class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                <i class="fas fa-arrow-right"></i> Lihat Data Siswa
            </a>
        </div>
        
        
        <div class="bg-green-100 p-4 rounded-lg shadow">
            <?php
            $result = $conn->query("SELECT COUNT(*) as total FROM guru");
            $jumlah_guru = $result->fetch_assoc()['total'];
            ?>
            <h3 class="font-bold text-xl mb-2">Jumlah Guru</h3>
            <div class="text-4xl font-bold text-green-600"><?php echo $jumlah_guru; ?></div>
            <a href="dashboard.php?page=menu&menu=guru" class="text-green-600 hover:text-green-800 mt-2 inline-block">
                <i class="fas fa-arrow-right"></i> Lihat Data Guru
            </a>
        </div>
        
        
        <div class="bg-purple-100 p-4 rounded-lg shadow">
            <?php
            $result = $conn->query("SELECT COUNT(*) as total FROM pengguna");
            $jumlah_pengguna = $result->fetch_assoc()['total'];
            ?>
            <h3 class="font-bold text-xl mb-2">Jumlah Pengguna</h3>
            <div class="text-4xl font-bold text-purple-600"><?php echo $jumlah_pengguna; ?></div>
            <a href="dashboard.php?page=profil" class="text-purple-600 hover:text-purple-800 mt-2 inline-block">
                <i class="fas fa-arrow-right"></i> Lihat Profil Anda
            </a>
        </div>
    </div>
    
    <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Selamat Datang di Sistem Informasi Sekolah</h3>
        <p class="text-gray-600">
            Sistem ini memungkinkan Anda untuk mengelola data siswa dan guru dengan mudah.
            Silakan gunakan menu di sebelah kiri untuk mengakses berbagai fitur yang tersedia.
        </p>
        <div class="mt-4">
            <h4 class="font-semibold">Fitur yang tersedia:</h4>
            <ul class="list-disc ml-5 mt-2 text-gray-600">
                <li>Kelola data siswa (tambah, edit, hapus)</li>
                <li>Kelola data guru (tambah, edit, hapus)</li>
                <li>Pencarian data siswa dan guru</li>
                <li>Akses profil pengguna</li>
            </ul>
        </div>
    </div>
</div>