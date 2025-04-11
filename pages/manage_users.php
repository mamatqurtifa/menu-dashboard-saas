<?php
if ( $_SESSION[ 'role' ] != 'admin' ) {
    header( 'Location: dashboard.php' );
    exit();
}

$search = isset( $_GET[ 'search' ] ) ? $_GET[ 'search' ] : '';
$search_condition = '';
if ( !empty( $search ) ) {
    $search_condition = " WHERE username LIKE '%$search%' OR nama_lengkap LIKE '%$search%'";
}

$filter_role = isset( $_GET[ 'filter_role' ] ) ? $_GET[ 'filter_role' ] : '';
if ( !empty( $filter_role ) ) {
    $filter_condition = empty( $search_condition ) ? " WHERE role = '$filter_role'" : " AND role = '$filter_role'";
    $search_condition .= $filter_condition;
}

$items_per_page = 10;
$page_number = isset( $_GET[ 'page_number' ] ) ? ( int )$_GET[ 'page_number' ] : 1;
$offset = ( $page_number - 1 ) * $items_per_page;

$count_query = 'SELECT COUNT(*) as total FROM pengguna' . $search_condition;
$count_result = $conn->query( $count_query );
$total_items = $count_result->fetch_assoc()[ 'total' ];
$total_pages = ceil( $total_items / $items_per_page );

$query = 'SELECT * FROM pengguna' . $search_condition . " ORDER BY id DESC LIMIT $offset, $items_per_page";
$result = $conn->query( $query );

$success_message = '';
$error_message = '';
$user = [
    'id' => '',
    'username' => '',
    'nama_lengkap' => '',
    'role' => ''
];

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' && isset( $_GET[ 'id' ] ) ) {
    $id = $_GET[ 'id' ];
    $edit_query = 'SELECT id, username, nama_lengkap, role FROM pengguna WHERE id = ?';
    $stmt = $conn->prepare( $edit_query );
    $stmt->bind_param( 'i', $id );
    $stmt->execute();
    $edit_result = $stmt->get_result();

    if ( $edit_result->num_rows > 0 ) {
        $user = $edit_result->fetch_assoc();
    }
}

if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' && isset( $_GET[ 'id' ] ) ) {
    $id = $_GET[ 'id' ];

    if ( $id == $_SESSION[ 'user_id' ] ) {
        $error_message = 'Anda tidak dapat menghapus akun yang sedang aktif!';
    } else {
        $delete_query = 'DELETE FROM pengguna WHERE id = ?';
        $stmt = $conn->prepare( $delete_query );
        $stmt->bind_param( 'i', $id );

        if ( $stmt->execute() ) {
            $success_message = 'Data pengguna berhasil dihapus!';
            echo "<script>window.location.href = 'dashboard.php?page=manage_users';</script>";
        } else {
            $error_message = 'Terjadi kesalahan saat menghapus data: ' . $conn->error;
        }
    }
}

if ( isset( $_POST[ 'change_role' ] ) ) {
    $user_id = $_POST[ 'user_id' ];
    $new_role = $_POST[ 'role' ];

    if ( $user_id == $_SESSION[ 'user_id' ] ) {
        $error_message = 'Anda tidak dapat mengubah role akun Anda sendiri!';
    } else {
        $update_query = 'UPDATE pengguna SET role = ? WHERE id = ?';
        $stmt = $conn->prepare( $update_query );
        $stmt->bind_param( 'si', $new_role, $user_id );

        if ( $stmt->execute() ) {
            $success_message = 'Role pengguna berhasil diubah!';
            $user = [
                'id' => '',
                'username' => '',
                'nama_lengkap' => '',
                'role' => ''
            ];
        } else {
            $error_message = 'Terjadi kesalahan saat mengubah role: ' . $conn->error;
        }
    }
}

if ( isset( $_POST[ 'add_user' ] ) ) {
    $username = trim( $_POST[ 'username' ] );
    $password = $_POST[ 'password' ];
    $nama_lengkap = trim( $_POST[ 'nama_lengkap' ] );
    $role = $_POST[ 'role' ];

    $is_valid = true;

    if ( empty( $username ) || empty( $password ) || empty( $nama_lengkap ) ) {
        $is_valid = false;
        $error_message = 'Semua field harus diisi!';
    } elseif ( strlen( $username ) < 4 ) {
        $is_valid = false;
        $error_message = 'Username minimal 4 karakter!';
    } elseif ( strlen( $password ) < 6 ) {
        $is_valid = false;
        $error_message = 'Password minimal 6 karakter!';
    } else {
        $check_query = 'SELECT * FROM pengguna WHERE username = ?';
        $check_stmt = $conn->prepare( $check_query );
        $check_stmt->bind_param( 's', $username );
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ( $check_result->num_rows > 0 ) {
            $is_valid = false;
            $error_message = 'Username sudah digunakan!';
        }
    }

    if ( $is_valid ) {
        $hashed_password = password_hash( $password, PASSWORD_DEFAULT );

        $insert_query = 'INSERT INTO pengguna (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)';
        $insert_stmt = $conn->prepare( $insert_query );
        $insert_stmt->bind_param( 'ssss', $username, $hashed_password, $nama_lengkap, $role );

        if ( $insert_stmt->execute() ) {
            $success_message = 'Pengguna baru berhasil ditambahkan!';
            $result = $conn->query( $query );
        } else {
            $error_message = 'Terjadi kesalahan saat menambahkan pengguna: ' . $conn->error;
        }
    }
}

if ( isset( $_POST[ 'reset_password' ] ) ) {
    $user_id = $_POST[ 'user_id' ];
    $new_password = $_POST[ 'new_password' ];

    if ( empty( $new_password ) || strlen( $new_password ) < 6 ) {
        $error_message = 'Password baru minimal 6 karakter!';
    } else {
        $hashed_password = password_hash( $new_password, PASSWORD_DEFAULT );

        $reset_query = 'UPDATE pengguna SET password = ? WHERE id = ?';
        $reset_stmt = $conn->prepare( $reset_query );
        $reset_stmt->bind_param( 'si', $hashed_password, $user_id );

        if ( $reset_stmt->execute() ) {
            $success_message = 'Password pengguna berhasil diubah!';
        } else {
            $error_message = 'Terjadi kesalahan saat mengubah password: ' . $conn->error;
        }
    }
}

function getRoleColor( $role ) {
    if ( $role == 'admin' ) {
        return 'bg-google-red/10 text-google-red';
    } else {
        return 'bg-google-blue/10 text-google-blue';
    }
}

$admin_query = "SELECT COUNT(*) as total FROM pengguna WHERE role = 'admin'";
$admin_result = $conn->query( $admin_query );
$admin_count = $admin_result->fetch_assoc()[ 'total' ];

$user_query = "SELECT COUNT(*) as total FROM pengguna WHERE role = 'users'";
$user_result = $conn->query( $user_query );
$user_count = $user_result->fetch_assoc()[ 'total' ];

$last_query = 'SELECT MAX(id) as last_id FROM pengguna';
$last_result = $conn->query( $last_query );
$last_id = $last_result->fetch_assoc()[ 'last_id' ];

$creation_date = date( 'Y-m-d' );
?>

<div class = 'max-w-7xl mx-auto'>

<div class = 'flex justify-between items-center mb-6'>
<div>
<h1 class = 'text-2xl font-bold text-gray-800'>Manajemen Pengguna</h1>
<div class = 'text-sm text-gray-500'>
<span>Dashboard</span>
<span class = 'mx-2'>›</span>
<span>Manajemen Pengguna</span>
</div>
</div>

<div>
<button type = 'button' id = 'toggleFormBtn' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none'>
<svg class = 'h-5 w-5 mr-2 addIcon' xmlns = 'http://www.w3.org/2000/svg' viewBox = '0 0 20 20' fill = 'currentColor'>
<path fill-rule = 'evenodd' d = 'M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z' clip-rule = 'evenodd' />
</svg>
<svg class = 'h-5 w-5 mr-2 hidden closeIcon' xmlns = 'http://www.w3.org/2000/svg' viewBox = '0 0 20 20' fill = 'currentColor'>
<path fill-rule = 'evenodd' d = 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule = 'evenodd' />
</svg>
<span class = 'addText'>Tambah Pengguna</span>
<span class = 'hidden closeText'>Tutup Form</span>
</button>
</div>
</div>

<?php if ( !empty( $success_message ) ) {
    ?>
    <div class = 'p-4 mb-6 rounded-lg flex items-center bg-google-green/10 text-google-green animate-fadeInDown'>
    <span class = 'text-xl mr-3'><i class = 'fas fa-check-circle'></i></span>
    <span><?php echo $success_message;
    ?></span>
    </div>
    <?php }
    ?>

    <?php if ( !empty( $error_message ) ) {
        ?>
        <div class = 'p-4 mb-6 rounded-lg flex items-center bg-google-red/10 text-google-red animate-fadeInDown'>
        <span class = 'text-xl mr-3'><i class = 'fas fa-exclamation-circle'></i></span>
        <span><?php echo $error_message;
        ?></span>
        </div>
        <?php }
        ?>

        <div id = 'formContainer' class = "mb-6 bg-white rounded-lg google-shadow p-6 <?php echo empty($_GET['action']) ? 'hidden' : ''; ?>">
        <div class = 'flex items-center border-b pb-4 mb-6'>
        <div class = 'bg-google-blue/10 rounded-full p-2 mr-3'>
        <svg xmlns = 'http://www.w3.org/2000/svg' class = 'h-6 w-6 text-google-blue' fill = 'none' viewBox = '0 0 24 24' stroke = 'currentColor'>
        <path stroke-linecap = 'round' stroke-linejoin = 'round' stroke-width = '2' d = 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' />
        </svg>
        </div>
        <h3 class = 'text-lg font-semibold'>
        <?php echo !empty( $user[ 'id' ] ) ? 'Edit Pengguna' : 'Tambah Pengguna Baru';
        ?>
        </h3>
        </div>

        <?php if ( !empty( $user[ 'id' ] ) ) {
            ?>

            <div class = 'grid grid-cols-1 md:grid-cols-2 gap-6'>

            <div>
            <h4 class = 'font-medium text-gray-700 mb-4'>Ubah Role Pengguna</h4>
            <form action = '' method = 'post' class = 'space-y-4'>
            <input type = 'hidden' name = 'user_id' value = "<?php echo $user['id']; ?>">

            <div>
            <label class = 'block text-sm font-medium text-gray-700 mb-2'>
            Username
            </label>
            <div class = 'bg-gray-50 rounded-md py-2 px-3 text-gray-700 font-medium'>
            <?php echo $user[ 'username' ];
            ?>
            <?php if ( $user[ 'id' ] == $_SESSION[ 'user_id' ] ) {
                ?>
                <span class = 'inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs font-medium bg-google-green/10 text-google-green'>
                Anda
                </span>
                <?php }
                ?>
                </div>
                </div>

                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2'>
                Nama Lengkap
                </label>
                <div class = 'bg-gray-50 rounded-md py-2 px-3 text-gray-700 font-medium'>
                <?php echo $user[ 'nama_lengkap' ];
                ?>
                </div>
                </div>

                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'role'>
                Role
                </label>
                <div class = 'relative'>
                <select id = 'role' name = 'role' class = 'mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md focus:outline-none focus:ring-google-blue focus:border-google-blue'
                <?php echo ( $user[ 'id' ] == $_SESSION[ 'user_id' ] ) ? 'disabled' : '';
                ?>>
                <option value = 'admin' <?php echo ( $user[ 'role' ] == 'admin' ) ? 'selected' : '';
                ?>>Administrator</option>
                <option value = 'users' <?php echo ( $user[ 'role' ] == 'users' ) ? 'selected' : '';
                ?>>User Biasa</option>
                </select>
                </div>

                <?php if ( $user[ 'id' ] == $_SESSION[ 'user_id' ] ) {
                    ?>
                    <p class = 'mt-2 text-sm text-google-red'>
                    <i class = 'fas fa-info-circle mr-1'></i> Anda tidak dapat mengubah role akun Anda sendiri
                    </p>
                    <?php }
                    ?>
                    </div>

                    <div class = 'pt-4'>
                    <button type = 'submit' name = 'change_role'

                    class = 'inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none'
                    <?php echo ( $user[ 'id' ] == $_SESSION[ 'user_id' ] ) ? 'disabled' : '';
                    ?>>
                    <i class = 'fas fa-user-tag mr-2'></i> Ubah Role
                    </button>
                    <a href = 'dashboard.php?page=manage_users' class = 'ml-3 inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-full shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none'>
                    Batal
                    </a>
                    </div>
                    </form>
                    </div>

                    <div class = 'border-t md:border-t-0 md:border-l border-gray-200 pt-4 md:pt-0 md:pl-6'>
                    <h4 class = 'font-medium text-gray-700 mb-4'>Reset Password</h4>
                    <form action = '' method = 'post' class = 'space-y-4'>
                    <input type = 'hidden' name = 'user_id' value = "<?php echo $user['id']; ?>">

                    <div>
                    <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'new_password'>
                    Password Baru
                    </label>
                    <div class = 'relative rounded-md shadow-sm'>
                    <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                    <i class = 'fas fa-key'></i>
                    </div>
                    <input type = 'password' id = 'new_password' name = 'new_password'

                    class = 'focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-2 border-gray-300 rounded-md'
                    required>
                    </div>
                    <p class = 'mt-2 text-xs text-gray-500'>
                    Password minimal 6 karakter, gunakan kombinasi huruf, angka, dan karakter khusus untuk keamanan lebih baik.
                    </p>
                    </div>

                    <div class = 'pt-4'>
                    <button type = 'submit' name = 'reset_password' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-yellow hover:bg-google-yellow/90 focus:outline-none'>
                    <i class = 'fas fa-key mr-2'></i> Reset Password
                    </button>
                    </div>
                    </form>
                    </div>
                    </div>
                    <?php } else {
                        ?>

                        <form action = '' method = 'post'>
                        <div class = 'grid grid-cols-1 md:grid-cols-2 gap-6'>
                        <div>
                        <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'username'>
                        Username
                        </label>
                        <div class = 'relative rounded-md shadow-sm'>
                        <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                        <i class = 'fas fa-user'></i>
                        </div>
                        <input type = 'text' id = 'username' name = 'username'

                        class = 'focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-2 border-gray-300 rounded-md'
                        required>
                        </div>
                        <p class = 'mt-2 text-xs text-gray-500'>Minimal 4 karakter, tanpa spasi.</p>
                        </div>

                        <div>
                        <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'nama_lengkap'>
                        Nama Lengkap
                        </label>
                        <div class = 'relative rounded-md shadow-sm'>
                        <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                        <i class = 'fas fa-address-card'></i>
                        </div>
                        <input type = 'text' id = 'nama_lengkap' name = 'nama_lengkap'

                        class = 'focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-2 border-gray-300 rounded-md'
                        required>
                        </div>
                        </div>
                        </div>

                        <div class = 'grid grid-cols-1 md:grid-cols-2 gap-6 mt-6'>
                        <div>
                        <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'password'>
                        Password
                        </label>
                        <div class = 'relative rounded-md shadow-sm'>
                        <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                        <i class = 'fas fa-lock'></i>
                        </div>
                        <input type = 'password' id = 'password' name = 'password'

                        class = 'focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-2 border-gray-300 rounded-md'
                        required>
                        </div>
                        <p class = 'mt-2 text-xs text-gray-500'>Minimal 6 karakter.</p>
                        </div>

                        <div>
                        <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'role'>
                        Role
                        </label>
                        <div class = 'relative rounded-md shadow-sm'>
                        <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                        <i class = 'fas fa-user-shield'></i>
                        </div>
                        <select id = 'role' name = 'role'

                        class = 'focus:ring-google-blue focus:border-google-blue block w-full pl-10 py-2 border-gray-300 rounded-md'
                        required>
                        <option value = 'users'>User Biasa</option>
                        <option value = 'admin'>Administrator</option>
                        </select>
                        </div>
                        </div>
                        </div>

                        <div class = 'mt-6 flex justify-end space-x-3'>
                        <button type = 'button' id = 'cancelAddBtn' class = 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-full shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none'>
                        <i class = 'fas fa-times mr-2'></i> Batal
                        </button>
                        <button type = 'submit' name = 'add_user' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none'>
                        <i class = 'fas fa-user-plus mr-2'></i> Tambah Pengguna
                        </button>
                        </div>
                        </form>
                        <?php }
                        ?>
                        </div>

                        <div class = 'bg-white rounded-lg google-shadow mb-6'>
                        <div class = 'p-6'>
                        <form action = '' method = 'get' class = 'space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4'>
                        <input type = 'hidden' name = 'page' value = 'manage_users'>

                        <div class = 'flex-grow'>
                        <label for = 'search' class = 'block text-sm font-medium text-gray-700 mb-1'>Cari Pengguna</label>
                        <div class = 'relative rounded-md'>
                        <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                        <i class = 'fas fa-search'></i>
                        </div>
                        <input type = 'text' id = 'search' name = 'search' placeholder = 'Cari berdasarkan username atau nama'

                        class = 'block w-full pl-10 py-2 border-gray-300 rounded-md focus:ring-google-blue focus:border-google-blue'
                        value = "<?php echo $search; ?>">
                        </div>
                        </div>

                        <div class = 'md:w-64'>
                        <label for = 'filter_role' class = 'block text-sm font-medium text-gray-700 mb-1'>Filter Role</label>
                        <select id = 'filter_role' name = 'filter_role'

                        class = 'block w-full py-2 border-gray-300 rounded-md focus:ring-google-blue focus:border-google-blue'>
                        <option value = ''>Semua Role</option>
                        <option value = 'admin' <?php echo ( $filter_role == 'admin' ) ? 'selected' : '';
                        ?>>Administrator</option>
                        <option value = 'users' <?php echo ( $filter_role == 'users' ) ? 'selected' : '';
                        ?>>User Biasa</option>
                        </select>
                        </div>

                        <div class = 'flex space-x-2'>
                        <button type = 'submit' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none'>
                        <i class = 'fas fa-search mr-2'></i> Cari
                        </button>
                        <?php if ( !empty( $search ) || !empty( $filter_role ) ) {
                            ?>
                            <a href = 'dashboard.php?page=manage_users' class = 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none'>
                            <i class = 'fas fa-times mr-2'></i> Reset
                            </a>
                            <?php }
                            ?>
                            </div>
                            </form>
                            </div>
                            </div>

                            <div class = 'grid grid-cols-1 md:grid-cols-3 gap-4 mb-6'>
                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-blue/10 text-google-blue mr-4'>
                            <i class = 'fas fa-users text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Total Pengguna</div>
                            <div class = 'text-2xl font-bold'><?php echo $total_items;
                            ?></div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-red/10 text-google-red mr-4'>
                            <i class = 'fas fa-user-shield text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Administrator</div>
                            <div class = 'text-2xl font-bold'><?php echo $admin_count;
                            ?></div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-green/10 text-google-green mr-4'>
                            <i class = 'fas fa-user text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>User Biasa</div>
                            <div class = 'text-2xl font-bold'><?php echo $user_count;
                            ?></div>
                            </div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow overflow-hidden'>
                            <?php if ( $result->num_rows > 0 ) {
                                ?>
                                <div class = 'overflow-x-auto'>
                                <table class = 'min-w-full divide-y divide-gray-200'>
                                <thead class = 'bg-gray-50'>
                                <tr>
                                <th scope = 'col' class = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                Pengguna
                                </th>
                                <th scope = 'col' class = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                Role
                                </th>
                                <th scope = 'col' class = 'px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                Aksi
                                </th>
                                </tr>
                                </thead>
                                <tbody class = 'bg-white divide-y divide-gray-200'>
                                <?php while ( $row = $result->fetch_assoc() ) {

                                    $is_current_user = ( $row[ 'id' ] == $_SESSION[ 'user_id' ] );
                                    $role_class = getRoleColor( $row[ 'role' ] );

                                    $initial = strtoupper( substr( $row[ 'nama_lengkap' ], 0, 1 ) );
                                    ?>
                                    <tr class = 'hover:bg-gray-50 transition-colors'>
                                    <td class = 'px-6 py-4'>
                                    <div class = 'flex items-center'>
                                    <div class = "flex-shrink-0 h-10 w-10 rounded-full <?php echo str_replace('text-', 'bg-', str_replace('/10', '', $role_class)); ?> text-white flex items-center justify-center">
                                    <?php echo $initial;
                                    ?>
                                    </div>
                                    <div class = 'ml-4'>
                                    <div class = 'flex items-center'>
                                    <div class = 'text-sm font-medium text-gray-900'><?php echo $row[ 'nama_lengkap' ];
                                    ?></div>
                                    <?php if ( $is_current_user ) {
                                        ?>
                                        <span class = 'ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-google-green/10 text-google-green'>
                                        Anda
                                        </span>
                                        <?php }
                                        ?>
                                        </div>
                                        <div class = 'text-sm text-gray-500'>@<?php echo $row[ 'username' ];
                                        ?></div>
                                        </div>
                                        </div>
                                        </td>
                                        <td class = 'px-6 py-4 whitespace-nowrap'>
                                        <span class = "inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $role_class; ?>">
                                        <?php echo ( $row[ 'role' ] == 'admin' ) ? 'Administrator' : 'User Biasa';
                                        ?>
                                        </span>
                                        </td>
                                        <td class = 'px-6 py-4 whitespace-nowrap text-center text-sm'>
                                        <a href = "dashboard.php?page=manage_users&action=edit&id=<?php echo $row['id']; ?>"

                                        class = 'inline-flex items-center px-3 py-1 border border-transparent rounded-full text-xs font-medium bg-google-blue/10 text-google-blue hover:bg-google-blue/20 mr-2'>
                                        <i class = 'fas fa-edit mr-1'></i> Edit
                                        </a>

                                        <?php if ( !$is_current_user ) {
                                            ?>
                                            <a href = 'javascript:void(0);'
                                            onclick = "confirmDelete(<?php echo $row['id']; ?>, '<?php echo $row['nama_lengkap']; ?>')"

                                            class = 'inline-flex items-center px-3 py-1 border border-transparent rounded-full text-xs font-medium bg-google-red/10 text-google-red hover:bg-google-red/20'>
                                            <i class = 'fas fa-trash mr-1'></i> Hapus
                                            </a>
                                            <?php } else {
                                                ?>
                                                <span class = 'inline-flex items-center px-3 py-1 border border-gray-200 rounded-full text-xs font-medium text-gray-400 cursor-not-allowed'>
                                                <i class = 'fas fa-trash mr-1'></i> Hapus
                                                </span>
                                                <?php }
                                                ?>
                                                </td>
                                                </tr>
                                                <?php }
                                                ?>
                                                </tbody>
                                                </table>
                                                </div>

                                                <?php if ( $total_pages > 1 ) {
                                                    ?>
                                                    <div class = 'bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200'>
                                                    <div class = 'flex-1 flex justify-between sm:hidden'>
                                                    <?php if ( $page_number > 1 ) {
                                                        ?>
                                                        <a href = "dashboard.php?page=manage_users&page_number=<?php echo ($page_number - 1); ?>&search=<?php echo $search; ?>&filter_role=<?php echo $filter_role; ?>"

                                                        class = 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50'>
                                                        Previous
                                                        </a>
                                                        <?php }
                                                        ?>
                                                        <?php if ( $page_number < $total_pages ) {
                                                            ?>
                                                            <a href = "dashboard.php?page=manage_users&page_number=<?php echo ($page_number + 1); ?>&search=<?php echo $search; ?>&filter_role=<?php echo $filter_role; ?>"

                                                            class = 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50'>
                                                            Next
                                                            </a>
                                                            <?php }
                                                            ?>
                                                            </div>
                                                            <div class = 'hidden sm:flex-1 sm:flex sm:items-center sm:justify-between'>
                                                            <div>
                                                            <p class = 'text-sm text-gray-700'>
                                                            Showing
                                                            <span class = 'font-medium'><?php echo ( $offset + 1 );
                                                            ?></span>
                                                            to
                                                            <span class = 'font-medium'><?php echo min( $offset + $items_per_page, $total_items );
                                                            ?></span>
                                                            of
                                                            <span class = 'font-medium'><?php echo $total_items;
                                                            ?></span>
                                                            results
                                                            </p>
                                                            </div>
                                                            <div>
                                                            <nav class = 'inline-flex rounded-md shadow-sm -space-x-px' aria-label = 'Pagination'>
                                                            <?php if ( $page_number > 1 ) {
                                                                ?>
                                                                <a href = "dashboard.php?page=manage_users&page_number=<?php echo ($page_number - 1); ?>&search=<?php echo $search; ?>&filter_role=<?php echo $filter_role; ?>"

                                                                class = 'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50'>
                                                                <span class = 'sr-only'>Previous</span>
                                                                <i class = 'fas fa-chevron-left'></i>
                                                                </a>
                                                                <?php }
                                                                ?>

                                                                <?php
                                                                $start_page = max( 1, $page_number - 2 );
                                                                $end_page = min( $total_pages, $page_number + 2 );

                                                                if ( $start_page > 1 ) {
                                                                    echo '<a href="dashboard.php?page=manage_users&page_number=1&search=' . $search . '&filter_role=' . $filter_role . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                                                    if ( $start_page > 2 ) {
                                                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                                                    }
                                                                }

                                                                for ( $i = $start_page; $i <= $end_page; $i++ ) {
                                                                    $active_class = ( $i == $page_number ) ? 'bg-google-blue text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                                                                    echo '<a href="dashboard.php?page=manage_users&page_number=' . $i . '&search=' . $search . '&filter_role=' . $filter_role . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 ' . $active_class . ' text-sm font-medium">' . $i . '</a>';
                                                                }

                                                                if ( $end_page < $total_pages ) {
                                                                    if ( $end_page < $total_pages - 1 ) {
                                                                        echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                                                    }
                                                                    echo '<a href="dashboard.php?page=manage_users&page_number=' . $total_pages . '&search=' . $search . '&filter_role=' . $filter_role . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $total_pages . '</a>';
                                                                }
                                                                ?>

                                                                <?php if ( $page_number < $total_pages ) {
                                                                    ?>
                                                                    <a href = "dashboard.php?page=manage_users&page_number=<?php echo ($page_number + 1); ?>&search=<?php echo $search; ?>&filter_role=<?php echo $filter_role; ?>"

                                                                    class = 'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50'>
                                                                    <span class = 'sr-only'>Next</span>
                                                                    <i class = 'fas fa-chevron-right'></i>
                                                                    </a>
                                                                    <?php }
                                                                    ?>
                                                                    </nav>
                                                                    </div>
                                                                    </div>
                                                                    </div>
                                                                    <?php }
                                                                    ?>
                                                                    <?php } else {
                                                                        ?>
                                                                        <div class = 'p-6 text-center'>
                                                                        <div class = 'inline-flex rounded-full p-6 bg-google-blue/10 mb-4'>
                                                                        <i class = 'fas fa-users-slash text-4xl text-google-blue'></i>
                                                                        </div>
                                                                        <h3 class = 'text-lg font-medium text-gray-900'>Tidak ada data pengguna</h3>
                                                                        <p class = 'text-gray-500 mt-2'>
                                                                        <?php echo empty( $search ) && empty( $filter_role ) ?
                                                                        'Belum ada data pengguna yang tersedia.' :
                                                                        'Tidak ditemukan data pengguna yang sesuai dengan pencarian.';
                                                                        ?>
                                                                        </p>
                                                                        <?php if ( !empty( $search ) || !empty( $filter_role ) ) {
                                                                            ?>
                                                                            <div class = 'mt-4'>
                                                                            <a href = 'dashboard.php?page=manage_users' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-google-blue hover:bg-google-blue/90 focus:outline-none'>
                                                                            Reset Pencarian
                                                                            </a>
                                                                            </div>
                                                                            <?php }
                                                                            ?>
                                                                            </div>
                                                                            <?php }
                                                                            ?>
                                                                            </div>

                                                                            <div class = 'mt-6 bg-white rounded-lg google-shadow p-6'>
                                                                            <div class = 'flex items-center border-b pb-4 mb-4'>
                                                                            <div class = 'bg-google-yellow/10 rounded-full p-2 mr-3'>
                                                                            <svg xmlns = 'http://www.w3.org/2000/svg' class = 'h-6 w-6 text-google-yellow' fill = 'none' viewBox = '0 0 24 24' stroke = 'currentColor'>
                                                                            <path stroke-linecap = 'round' stroke-linejoin = 'round' stroke-width = '2' d = 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' />
                                                                            </svg>
                                                                            </div>
                                                                            <h3 class = 'text-lg font-semibold'>Tips Keamanan Akun</h3>
                                                                            </div>

                                                                            <div class = 'grid grid-cols-1 md:grid-cols-3 gap-6'>
                                                                            <div class = 'border-l-4 border-google-blue pl-4'>
                                                                            <h4 class = 'font-medium text-gray-800 mb-2'>Kata Sandi yang Kuat</h4>
                                                                            <p class = 'text-gray-600 text-sm'>
                                                                            Gunakan kata sandi yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan simbol. Hindari menggunakan kata sandi yang sama untuk beberapa akun.
                                                                            </p>
                                                                            </div>

                                                                            <div class = 'border-l-4 border-google-red pl-4'>
                                                                            <h4 class = 'font-medium text-gray-800 mb-2'>Pemberian Hak Akses</h4>
                                                                            <p class = 'text-gray-600 text-sm'>
                                                                            Berikan hak akses administrator hanya kepada pengguna yang benar-benar membutuhkan. Terlalu banyak administrator meningkatkan risiko keamanan.
                                                                            </p>
                                                                            </div>

                                                                            <div class = 'border-l-4 border-google-green pl-4'>
                                                                            <h4 class = 'font-medium text-gray-800 mb-2'>Audit Rutin</h4>
                                                                            <p class = 'text-gray-600 text-sm'>
                                                                            Lakukan audit rutin terhadap daftar pengguna dan peran mereka. Hapus akun yang sudah tidak digunakan atau tidak aktif.
                                                                            </p>
                                                                            </div>
                                                                            </div>
                                                                            </div>
                                                                            </div>

                                                                            <div id = 'deleteModal' class = 'fixed z-10 inset-0 overflow-y-auto hidden'>
                                                                            <div class = 'flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0'>

                                                                            <div class = 'fixed inset-0 transition-opacity' aria-hidden = 'true'>
                                                                            <div id = 'modalOverlay' class = 'absolute inset-0 bg-gray-500 opacity-75'></div>
                                                                            </div>

                                                                            <div class = 'inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full'>
                                                                            <div class = 'bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4'>
                                                                            <div class = 'sm:flex sm:items-start'>
                                                                            <div class = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10'>
                                                                            <i class = 'fas fa-exclamation-triangle text-red-600'></i>
                                                                            </div>
                                                                            <div class = 'mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left'>
                                                                            <h3 class = 'text-lg leading-6 font-medium text-gray-900' id = 'modal-title'>
                                                                            Hapus Pengguna
                                                                            </h3>
                                                                            <div class = 'mt-2'>
                                                                            <p class = 'text-sm text-gray-500' id = 'modal-description'>
                                                                            Apakah Anda yakin ingin menghapus pengguna ini? Data yang dihapus tidak dapat dikembalikan.
                                                                            </p>
                                                                            </div>
                                                                            </div>
                                                                            </div>
                                                                            </div>
                                                                            <div class = 'bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse'>
                                                                            <a id = 'confirmDelete' href = '#' class = 'w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-google-red text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm'>
                                                                            Hapus
                                                                            </a>
                                                                            <button type = 'button' id = 'cancelDelete' class = 'mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm'>
                                                                            Batal
                                                                            </button>
                                                                            </div>
                                                                            </div>
                                                                            </div>
                                                                            </div>

                                                                            <style>
                                                                            @keyframes fadeInDown {
                                                                                from {
                                                                                    opacity: 0;
                                                                                    transform: translateY( -10px );
                                                                                }
                                                                                to {
                                                                                    opacity: 1;
                                                                                    transform: translateY( 0 );
                                                                                }
                                                                            }
                                                                            .animate-fadeInDown {
                                                                                animation: fadeInDown 0.3s ease-out forwards;
                                                                            }
                                                                            </style>

                                                                            <script>
                                                                            document.addEventListener( 'DOMContentLoaded', function() {
                                                                                const toggleFormBtn = document.getElementById( 'toggleFormBtn' );
                                                                                const cancelAddBtn = document.getElementById( 'cancelAddBtn' );
                                                                                const formContainer = document.getElementById( 'formContainer' );

                                                                                if ( toggleFormBtn && formContainer ) {
                                                                                    toggleFormBtn.addEventListener( 'click', function() {
                                                                                        formContainer.classList.toggle( 'hidden' );

                                                                                        const addText = document.querySelector( '.addText' );
                                                                                        const closeText = document.querySelector( '.closeText' );
                                                                                        const addIcon = document.querySelector( '.addIcon' );
                                                                                        const closeIcon = document.querySelector( '.closeIcon' );

                                                                                        if ( addText && closeText && addIcon && closeIcon ) {
                                                                                            addText.classList.toggle( 'hidden' );
                                                                                            closeText.classList.toggle( 'hidden' );
                                                                                            addIcon.classList.toggle( 'hidden' );
                                                                                            closeIcon.classList.toggle( 'hidden' );
                                                                                        }
                                                                                    }
                                                                                );
                                                                            }

                                                                            if ( cancelAddBtn && formContainer ) {
                                                                                cancelAddBtn.addEventListener( 'click', function() {
                                                                                    formContainer.classList.add( 'hidden' );

                                                                                    const addText = document.querySelector( '.addText' );
                                                                                    const closeText = document.querySelector( '.closeText' );
                                                                                    const addIcon = document.querySelector( '.addIcon' );
                                                                                    const closeIcon = document.querySelector( '.closeIcon' );

                                                                                    if ( addText && closeText && addIcon && closeIcon ) {
                                                                                        addText.classList.remove( 'hidden' );
                                                                                        closeText.classList.add( 'hidden' );
                                                                                        addIcon.classList.remove( 'hidden' );
                                                                                        closeIcon.classList.add( 'hidden' );
                                                                                    }
                                                                                }
                                                                            );
                                                                        }

                                                                        const deleteModal = document.getElementById( 'deleteModal' );
                                                                        const modalOverlay = document.getElementById( 'modalOverlay' );
                                                                        const cancelDelete = document.getElementById( 'cancelDelete' );

                                                                        if ( modalOverlay && cancelDelete ) {
                                                                            modalOverlay.addEventListener( 'click', closeModal );
                                                                            cancelDelete.addEventListener( 'click', closeModal );
                                                                        }
                                                                    }
                                                                );

                                                                function closeModal() {
                                                                    const deleteModal = document.getElementById( 'deleteModal' );
                                                                    if ( deleteModal ) {
                                                                        deleteModal.classList.add( 'hidden' );
                                                                    }
                                                                }

                                                                function confirmDelete( id, name ) {
                                                                    const deleteModal = document.getElementById( 'deleteModal' );
                                                                    const confirmDelete = document.getElementById( 'confirmDelete' );
                                                                    const modalDescription = document.getElementById( 'modal-description' );

                                                                    if ( deleteModal && confirmDelete && modalDescription ) {
                                                                        modalDescription.textContent = `Apakah Anda yakin ingin menghapus pengguna "${name}"? Data yang dihapus tidak dapat dikembalikan.`;
                                                                        confirmDelete.href = `dashboard.php?page = manage_users&action = delete&id = $ {
                                                                            id}
                                                                            `;
                                                                            deleteModal.classList.remove( 'hidden' );
                                                                        }
                                                                    }
                                                                    </script>