<?php
session_start();
require_once 'db.php';

if ( !isset( $_SESSION[ 'user_id' ] ) ) {
    header( 'Location: index.php' );
    exit();
}

$page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : 'home';
$menu = isset( $_GET[ 'menu' ] ) ? $_GET[ 'menu' ] : '';

$is_admin = ( $_SESSION[ 'role' ] == 'admin' );
?>

<!DOCTYPE html>
<html lang = 'id'>
<head>
<meta charset = 'UTF-8'>
<meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
<title>Dashboard - Sistem Sekolah</title>
<script src = 'https://cdn.tailwindcss.com'></script>
<link rel = 'stylesheet' href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'google-blue': '#4285F4',
                'google-red': '#EA4335',
                'google-yellow': '#FBBC05',
                'google-green': '#34A853',
                'google-gray': '#F1F3F4',
            }
        }
    }
}
</script>
<style>
.google-shadow {
    box-shadow: 0 1px 6px rgba( 32, 33, 36, 0.28 );
}
.menu-item {
    transition: all 0.2s ease-in-out;
}
.menu-item:hover {
    transform: translateY( -2px );
}
.navbar-link {
    position: relative;
}
.navbar-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 3px;
    bottom: -6px;
    left: 0;
    background-color: currentColor;
    transition: width 0.2s ease-in-out;
}
.navbar-link:hover::after,
.navbar-link.active::after {
    width: 100%;
}
</style>
</head>
<body class = 'bg-google-gray min-h-screen flex flex-col'>
<header class = 'bg-white sticky top-0 z-50 google-shadow'>
<div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
<div class = 'flex justify-between items-center h-16'>
<div class = 'flex items-center'>
<div class = 'flex-shrink-0 flex items-center'>
<span class = 'text-3xl font-bold' style = "font-family: 'Product Sans', Arial, sans-serif">
<span class = 'text-google-blue'>Sistem</span><span class = 'text-google-red'> Infor</span><span class = 'text-google-yellow'>masi </span><span class = 'text-google-green'>Sekolah</span>
</span>
</div>
</div>

<div class = 'hidden md:block'>
<div class = 'flex space-x-6'>
<a href = 'dashboard.php' class = "navbar-link text-gray-600 hover:text-google-blue px-3 py-2 text-sm font-medium <?php echo $page === 'home' ? 'active text-google-blue' : ''; ?>">
Dashboard
</a>
<a href = 'dashboard.php?page=profil' class = "navbar-link text-gray-600 hover:text-google-blue px-3 py-2 text-sm font-medium <?php echo $page === 'profil' ? 'active text-google-blue' : ''; ?>">
Profil
</a>
<a href = 'dashboard.php?page=menu&menu=siswa' class = "navbar-link text-gray-600 hover:text-google-blue px-3 py-2 text-sm font-medium <?php echo ($page === 'menu' && $menu === 'siswa') ? 'active text-google-blue' : ''; ?>">
Data Siswa
</a>
<a href = 'dashboard.php?page=menu&menu=guru' class = "navbar-link text-gray-600 hover:text-google-blue px-3 py-2 text-sm font-medium <?php echo ($page === 'menu' && $menu === 'guru') ? 'active text-google-blue' : ''; ?>">
Data Guru
</a>
<?php if ( $is_admin ) {
    ?>
    <a href = 'dashboard.php?page=manage_users' class = "navbar-link text-gray-600 hover:text-google-blue px-3 py-2 text-sm font-medium <?php echo $page === 'manage_users' ? 'active text-google-blue' : ''; ?>">
    Manajemen User
    </a>
    <?php }
    ?>
    </div>
    </div>

    <div class = 'flex items-center'>
    <div class = 'flex items-center'>
    <div class = 'relative'>
    <button class = 'flex items-center text-sm focus:outline-none' id = 'user-menu-button'>
    <div class = 'h-8 w-8 rounded-full bg-google-blue flex items-center justify-center text-white'>
    <?php echo substr( $_SESSION[ 'nama_lengkap' ], 0, 1 );
    ?>
    </div>
    <div class = 'ml-2 hidden md:block'>
    <div class = 'text-sm font-medium text-gray-700'><?php echo $_SESSION[ 'nama_lengkap' ];
    ?></div>
    <div class = 'text-xs text-gray-500'><?php echo ucfirst( $_SESSION[ 'role' ] );
    ?></div>
    </div>
    </button>
    </div>
    <a href = 'logout.php' class = 'ml-4 px-4 py-2 text-sm text-white bg-google-red rounded-full hover:bg-opacity-90 focus:outline-none'>
    Logout
    </a>
    </div>
    </div>

    <div class = 'md:hidden flex items-center'>
    <button type = 'button' id = 'mobile-menu-button' class = 'text-gray-500 hover:text-gray-700 focus:outline-none'>
    <svg class = 'h-6 w-6' fill = 'none' viewBox = '0 0 24 24' stroke = 'currentColor'>
    <path stroke-linecap = 'round' stroke-linejoin = 'round' stroke-width = '2' d = 'M4 6h16M4 12h16M4 18h16' />
    </svg>
    </button>
    </div>
    </div>

    <div class = 'hidden md:hidden' id = 'mobile-menu'>
    <div class = 'px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t'>
    <a href = 'dashboard.php' class = "<?php echo $page === 'home' ? 'bg-google-blue text-white' : 'text-gray-600 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">
    <i class = 'fas fa-home mr-2'></i>Dashboard
    </a>
    <a href = 'dashboard.php?page=profil' class = "<?php echo $page === 'profil' ? 'bg-google-blue text-white' : 'text-gray-600 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">
    <i class = 'fas fa-user mr-2'></i>Profil
    </a>
    <a href = 'dashboard.php?page=menu&menu=siswa' class = "<?php echo ($page === 'menu' && $menu === 'siswa') ? 'bg-google-blue text-white' : 'text-gray-600 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">
    <i class = 'fas fa-graduation-cap mr-2'></i>Data Siswa
    </a>
    <a href = 'dashboard.php?page=menu&menu=guru' class = "<?php echo ($page === 'menu' && $menu === 'guru') ? 'bg-google-blue text-white' : 'text-gray-600 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">
    <i class = 'fas fa-chalkboard-teacher mr-2'></i>Data Guru
    </a>
    <?php if ( $is_admin ) {
        ?>
        <a href = 'dashboard.php?page=manage_users' class = "<?php echo $page === 'manage_users' ? 'bg-google-blue text-white' : 'text-gray-600 hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium">
        <i class = 'fas fa-users-cog mr-2'></i>Manajemen Pengguna
        </a>
        <?php }
        ?>
        </div>
        </div>
        </div>
        </header>

        <div class = 'flex-grow flex flex-col md:flex-row'>
        <aside class = 'hidden md:block w-64 bg-white google-shadow p-4'>
        <div class = 'text-center mb-8'>
        <div class = 'flex justify-center'>
        <span class = 'inline-flex rounded-full p-3 bg-google-blue/10 text-google-blue'>
        <svg xmlns = 'http://www.w3.org/2000/svg' class = 'h-6 w-6' fill = 'none' viewBox = '0 0 24 24' stroke = 'currentColor'>
        <path stroke-linecap = 'round' stroke-linejoin = 'round' stroke-width = '2' d = 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' />
        </svg>
        </span>
        </div>
        <h2 class = 'mt-3 text-xl font-bold text-gray-800'>Menu Utama</h2>
        <p class = 'mt-1 text-sm text-gray-500'>Sistem Informasi Sekolah</p>
        </div>

        <nav>
        <div class = 'space-y-2 py-4'>
        <a href = 'dashboard.php' class = "menu-item flex items-center p-3 rounded-lg <?php echo $page === 'home' ? 'bg-google-blue text-white' : 'text-gray-700 hover:bg-google-blue/10 hover:text-google-blue'; ?>">
        <span class = 'mr-3'><i class = 'fas fa-tachometer-alt'></i></span>
        <span>Dashboard</span>
        </a>
        <a href = 'dashboard.php?page=profil' class = "menu-item flex items-center p-3 rounded-lg <?php echo $page === 'profil' ? 'bg-google-blue text-white' : 'text-gray-700 hover:bg-google-blue/10 hover:text-google-blue'; ?>">
        <span class = 'mr-3'><i class = 'fas fa-user'></i></span>
        <span>Profil</span>
        </a>
        </div>

        <div class = 'pt-4 border-t'>
        <h3 class = 'px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider'>
        Data Master
        </h3>
        <div class = 'mt-2 space-y-2'>
        <a href = 'dashboard.php?page=menu&menu=siswa' class = "menu-item flex items-center p-3 rounded-lg <?php echo ($page === 'menu' && $menu === 'siswa') ? 'bg-google-blue text-white' : 'text-gray-700 hover:bg-google-blue/10 hover:text-google-blue'; ?>">
        <span class = 'mr-3'><i class = 'fas fa-graduation-cap'></i></span>
        <span>Data Siswa</span>
        </a>
        <a href = 'dashboard.php?page=menu&menu=guru' class = "menu-item flex items-center p-3 rounded-lg <?php echo ($page === 'menu' && $menu === 'guru') ? 'bg-google-blue text-white' : 'text-gray-700 hover:bg-google-blue/10 hover:text-google-blue'; ?>">
        <span class = 'mr-3'><i class = 'fas fa-chalkboard-teacher'></i></span>
        <span>Data Guru</span>
        </a>
        </div>
        </div>

        <?php if ( $is_admin ) {
            ?>
            <div class = 'pt-4 border-t'>
            <h3 class = 'px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider'>
            Administrator
            </h3>
            <div class = 'mt-2 space-y-2'>
            <a href = 'dashboard.php?page=manage_users' class = "menu-item flex items-center p-3 rounded-lg <?php echo $page === 'manage_users' ? 'bg-google-blue text-white' : 'text-gray-700 hover:bg-google-blue/10 hover:text-google-blue'; ?>">
            <span class = 'mr-3'><i class = 'fas fa-users-cog'></i></span>
            <span>Manajemen Pengguna</span>
            </a>
            </div>
            </div>
            <?php }
            ?>

            <div class = 'mt-8 px-3 py-4 bg-google-gray rounded-lg'>
            <div class = 'text-sm text-gray-500 mb-1'>Tanggal & Waktu</div>
            <div class = 'text-gray-700 font-medium' id = 'current-time'></div>
            <script>

            function updateTime() {
                const now = new Date();
                document.getElementById( 'current-time' ).textContent = now.toLocaleString( 'id-ID' );
                setTimeout( updateTime, 1000 );
            }
            updateTime();
            </script>
            </div>
            </nav>
            </aside>

            <main class = 'flex-grow p-4 md:p-8 overflow-y-auto'>
            <div class = 'container mx-auto'>
            <?php
            switch ( $page ) {
                case 'home':
                include( 'pages/home.php' );
                break;
                case 'profil':
                include( 'pages/profil.php' );
                break;
                case 'menu':
                if ( $menu == 'siswa' ) {
                    include( 'pages/siswa.php' );
                } elseif ( $menu == 'guru' ) {
                    include( 'pages/guru.php' );
                } else {
                    include( 'pages/404.php' );
                }
                break;
                case 'manage_users':
                if ( $is_admin ) {
                    include( 'pages/manage_users.php' );
                } else {
                    include( 'pages/403.php' );
                }
                break;
                default:
                include( 'pages/404.php' );
            }
            ?>
            </div>
            </main>
            </div>

            <footer class = 'bg-white google-shadow py-4'>
            <div class = 'container mx-auto px-4 sm:px-6 lg:px-8'>
            <div class = 'flex flex-wrap justify-between items-center'>
            <div class = 'w-full md:w-auto mb-4 md:mb-0 text-center md:text-left'>
            <p class = 'text-sm text-gray-600'>
            &copy;
            <?php echo date( 'Y' );
            ?> Sistem Informasi Sekolah. All rights reserved.
            </p>
            </div>
            <div class = 'w-full md:w-auto flex justify-center md:justify-end'>
            <div class = 'flex space-x-4'>
            <a href = '#' class = 'text-google-blue hover:text-google-blue/80'>
            <i class = 'fab fa-facebook'></i>
            </a>
            <a href = '#' class = 'text-google-red hover:text-google-red/80'>
            <i class = 'fab fa-youtube'></i>
            </a>
            <a href = '#' class = 'text-google-blue hover:text-google-blue/80'>
            <i class = 'fab fa-twitter'></i>
            </a>
            <a href = '#' class = 'text-google-yellow hover:text-google-yellow/80'>
            <i class = 'fas fa-envelope'></i>
            </a>
            </div>
            </div>
            </div>
            </div>
            </footer>

            <script>
            document.getElementById( 'mobile-menu-button' ).addEventListener( 'click', function() {
                const mobileMenu = document.getElementById( 'mobile-menu' );
                mobileMenu.classList.toggle( 'hidden' );
            }
        );

        document.addEventListener( 'click', function( event ) {
            const mobileMenu = document.getElementById( 'mobile-menu' );
            const mobileMenuButton = document.getElementById( 'mobile-menu-button' );

            if ( !mobileMenuButton.contains( event.target ) && !mobileMenu.contains( event.target ) ) {
                mobileMenu.classList.add( 'hidden' );
            }
        }
    );
    </script>
    </body>
    </html>