<?php
$search = isset( $_GET[ 'search' ] ) ? $_GET[ 'search' ] : '';
$search_condition = '';
if ( !empty( $search ) ) {
    $search_condition = " WHERE nama LIKE '%$search%' OR nis LIKE '%$search%' OR kelas LIKE '%$search%'";
}

$filter_kelas = isset( $_GET[ 'filter_kelas' ] ) ? $_GET[ 'filter_kelas' ] : '';
if ( !empty( $filter_kelas ) ) {
    $filter_condition = empty( $search_condition ) ? " WHERE kelas = '$filter_kelas'" : " AND kelas = '$filter_kelas'";
    $search_condition .= $filter_condition;
}

$items_per_page = 10;
$page_number = isset( $_GET[ 'page_number' ] ) ? ( int )$_GET[ 'page_number' ] : 1;
$offset = ( $page_number - 1 ) * $items_per_page;

$count_query = 'SELECT COUNT(*) as total FROM siswa' . $search_condition;
$count_result = $conn->query( $count_query );
$total_items = $count_result->fetch_assoc()[ 'total' ];
$total_pages = ceil( $total_items / $items_per_page );

$query = 'SELECT * FROM siswa' . $search_condition . " ORDER BY id DESC LIMIT $offset, $items_per_page";
$result = $conn->query( $query );

$kelas_query = 'SELECT DISTINCT kelas FROM siswa ORDER BY kelas';
$kelas_result = $conn->query( $kelas_query );
$kelas_list = [];
while ( $row = $kelas_result->fetch_assoc() ) {
    $kelas_list[] = $row[ 'kelas' ];
}

$is_admin = ( $_SESSION[ 'role' ] == 'admin' );

$success_message = '';
$error_message = '';
$siswa = [
    'id' => '',
    'nis' => '',
    'nama' => '',
    'kelas' => '',
    'jenis_kelamin' => '',
    'alamat' => ''
];

if ( !$is_admin && isset( $_GET[ 'action' ] ) ) {
    $error_message = 'Anda tidak memiliki hak akses untuk melakukan operasi ini!';
} else {
    if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'edit' && isset( $_GET[ 'id' ] ) ) {
        $id = $_GET[ 'id' ];
        $edit_query = "SELECT * FROM siswa WHERE id = $id";
        $edit_result = $conn->query( $edit_query );
        if ( $edit_result->num_rows > 0 ) {
            $siswa = $edit_result->fetch_assoc();
        }
    }

    if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'delete' && isset( $_GET[ 'id' ] ) ) {
        $id = $_GET[ 'id' ];
        $delete_query = "DELETE FROM siswa WHERE id = $id";
        if ( $conn->query( $delete_query ) ) {
            $success_message = 'Data siswa berhasil dihapus!';
            echo "<script>window.location.href = 'dashboard.php?page=menu&menu=siswa';</script>";
        } else {
            $error_message = 'Terjadi kesalahan saat menghapus data: ' . $conn->error;
        }
    }

    if ( isset( $_POST[ 'submit' ] ) ) {
        $nis = $_POST[ 'nis' ];
        $nama = $_POST[ 'nama' ];
        $kelas = $_POST[ 'kelas' ];
        $jenis_kelamin = $_POST[ 'jenis_kelamin' ];
        $alamat = $_POST[ 'alamat' ];

        $is_valid = true;

        if ( empty( $_POST[ 'id' ] ) || ( $_POST[ 'id' ] && $nis != $siswa[ 'nis' ] ) ) {
            $check_query = 'SELECT * FROM siswa WHERE nis = ?';
            $check_stmt = $conn->prepare( $check_query );
            $check_stmt->bind_param( 's', $nis );
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ( $check_result->num_rows > 0 ) {
                $is_valid = false;
                $error_message = 'NIS sudah terdaftar!';
            }
        }

        if ( $is_valid ) {
            if ( !empty( $_POST[ 'id' ] ) ) {
                $id = $_POST[ 'id' ];
                $update_query = 'UPDATE siswa SET nis = ?, nama = ?, kelas = ?, jenis_kelamin = ?, alamat = ? WHERE id = ?';
                $update_stmt = $conn->prepare( $update_query );
                $update_stmt->bind_param( 'sssssi', $nis, $nama, $kelas, $jenis_kelamin, $alamat, $id );

                if ( $update_stmt->execute() ) {
                    $success_message = 'Data siswa berhasil diperbarui!';
                    $siswa = [
                        'id' => '',
                        'nis' => '',
                        'nama' => '',
                        'kelas' => '',
                        'jenis_kelamin' => '',
                        'alamat' => ''
                    ];
                } else {
                    $error_message = 'Terjadi kesalahan saat memperbarui data: ' . $conn->error;
                }
            } else {
                $insert_query = 'INSERT INTO siswa (nis, nama, kelas, jenis_kelamin, alamat) VALUES (?, ?, ?, ?, ?)';
                $insert_stmt = $conn->prepare( $insert_query );
                $insert_stmt->bind_param( 'sssss', $nis, $nama, $kelas, $jenis_kelamin, $alamat );

                if ( $insert_stmt->execute() ) {
                    $success_message = 'Data siswa berhasil ditambahkan!';
                    $siswa = [
                        'id' => '',
                        'nis' => '',
                        'nama' => '',
                        'kelas' => '',
                        'jenis_kelamin' => '',
                        'alamat' => ''
                    ];
                } else {
                    $error_message = 'Terjadi kesalahan saat menambahkan data: ' . $conn->error;
                }
            }

            $result = $conn->query( $query );
        }
    }
}

function getClassColor( $class ) {
    if ( strpos( $class, 'X' ) !== false ) {
        return 'bg-google-blue/10 text-google-blue';
    } elseif ( strpos( $class, 'XI' ) !== false ) {
        return 'bg-google-green/10 text-google-green';
    } elseif ( strpos( $class, 'XII' ) !== false ) {
        return 'bg-google-yellow/10 text-google-yellow';
    } else {
        return 'bg-gray-100 text-gray-800';
    }
}

function getEnrollmentYear( $nis ) {
    $year = substr( $nis, 0, 4 );
    if ( is_numeric( $year ) && $year >= 2010 && $year <= date( 'Y' ) ) {
        return $year;
    }
    return 'N/A';
}
?>

<div class = 'max-w-7xl mx-auto'>

<div class = 'flex justify-between items-center mb-6'>
<div>
<h1 class = 'text-2xl font-bold text-gray-800'>Data Siswa</h1>
<div class = 'text-sm text-gray-500'>
<span>Dashboard</span>
<span class = 'mx-2'>›</span>
<span>Data Siswa</span>
</div>
</div>

<?php if ( $is_admin ) {
    ?>
    <div>
    <button type = 'button' id = 'toggleFormBtn' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-green hover:bg-google-green/90 focus:outline-none'>
    <svg class = 'h-5 w-5 mr-2 addIcon' xmlns = 'http://www.w3.org/2000/svg' viewBox = '0 0 20 20' fill = 'currentColor'>
    <path fill-rule = 'evenodd' d = 'M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z' clip-rule = 'evenodd' />
    </svg>
    <svg class = 'h-5 w-5 mr-2 hidden closeIcon' xmlns = 'http://www.w3.org/2000/svg' viewBox = '0 0 20 20' fill = 'currentColor'>
    <path fill-rule = 'evenodd' d = 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule = 'evenodd' />
    </svg>
    <span class = 'addText'>Tambah Siswa</span>
    <span class = 'hidden closeText'>Tutup Form</span>
    </button>
    </div>
    <?php }
    ?>
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

            <?php if ( $is_admin ) {
                ?>
                <div id = 'formContainer' class = "mb-6 bg-white rounded-lg google-shadow p-6 <?php echo empty($siswa['id']) ? 'hidden' : ''; ?>">
                <div class = 'flex items-center border-b pb-4 mb-6'>
                <div class = 'bg-google-green/10 rounded-full p-2 mr-3'>
                <svg xmlns = 'http://www.w3.org/2000/svg' class = 'h-6 w-6 text-google-green' fill = 'none' viewBox = '0 0 24 24' stroke = 'currentColor'>
                <path stroke-linecap = 'round' stroke-linejoin = 'round' stroke-width = '2' d = 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' />
                </svg>
                </div>
                <h3 class = 'text-lg font-semibold'><?php echo !empty( $siswa[ 'id' ] ) ? 'Edit Data Siswa' : 'Tambah Data Siswa';
                ?></h3>
                </div>

                <form action = '' method = 'post'>
                <input type = 'hidden' name = 'id' value = "<?php echo $siswa['id']; ?>">

                <div class = 'grid grid-cols-1 md:grid-cols-2 gap-6'>
                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'nis'>
                NIS ( Nomor Induk Siswa )
                </label>
                <div class = 'relative rounded-md shadow-sm'>
                <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                <i class = 'fas fa-id-card'></i>
                </div>
                <input type = 'text' id = 'nis' name = 'nis'

                class = 'focus:ring-google-green focus:border-google-green block w-full pl-10 py-3 border-gray-300 rounded-md'
                value = "<?php echo $siswa['nis']; ?>" required>
                </div>
                </div>

                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'nama'>
                Nama Lengkap
                </label>
                <div class = 'relative rounded-md shadow-sm'>
                <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                <i class = 'fas fa-user'></i>
                </div>
                <input type = 'text' id = 'nama' name = 'nama'

                class = 'focus:ring-google-green focus:border-google-green block w-full pl-10 py-3 border-gray-300 rounded-md'
                value = "<?php echo $siswa['nama']; ?>" required>
                </div>
                </div>
                </div>

                <div class = 'grid grid-cols-1 md:grid-cols-2 gap-6 mt-6'>
                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'kelas'>
                Kelas
                </label>
                <div class = 'relative rounded-md shadow-sm'>
                <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                <i class = 'fas fa-graduation-cap'></i>
                </div>
                <input type = 'text' id = 'kelas' name = 'kelas'

                class = 'focus:ring-google-green focus:border-google-green block w-full pl-10 py-3 border-gray-300 rounded-md'
                value = "<?php echo $siswa['kelas']; ?>" required>
                </div>
                </div>

                <div>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'jenis_kelamin'>
                Jenis Kelamin
                </label>
                <div class = 'relative rounded-md shadow-sm'>
                <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                <i class = 'fas fa-venus-mars'></i>
                </div>
                <select id = 'jenis_kelamin' name = 'jenis_kelamin'

                class = 'focus:ring-google-green focus:border-google-green block w-full pl-10 py-3 border-gray-300 rounded-md'
                required>
                <option value = ''>Pilih Jenis Kelamin</option>
                <option value = 'Laki-laki' <?php echo ( $siswa[ 'jenis_kelamin' ] == 'Laki-laki' ) ? 'selected' : '';
                ?>>Laki-laki</option>
                <option value = 'Perempuan' <?php echo ( $siswa[ 'jenis_kelamin' ] == 'Perempuan' ) ? 'selected' : '';
                ?>>Perempuan</option>
                </select>
                </div>
                </div>
                </div>

                <div class = 'mt-6'>
                <label class = 'block text-sm font-medium text-gray-700 mb-2' for = 'alamat'>
                Alamat
                </label>
                <div class = 'relative rounded-md shadow-sm'>
                <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                <i class = 'fas fa-map-marker-alt'></i>
                </div>
                <textarea id = 'alamat' name = 'alamat' rows = '3'

                class = 'focus:ring-google-green focus:border-google-green block w-full pl-10 py-3 border-gray-300 rounded-md'><?php echo $siswa[ 'alamat' ];
                ?></textarea>
                </div>
                </div>

                <div class = 'mt-6 flex space-x-3 justify-end'>
                <?php if ( !empty( $siswa[ 'id' ] ) ) {
                    ?>
                    <a href = 'dashboard.php?page=menu&menu=siswa'

                    class = 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-full shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none'>
                    <i class = 'fas fa-times mr-2'></i> Batal
                    </a>
                    <?php }
                    ?>
                    <button type = 'submit' name = 'submit'

                    class = 'inline-flex items-center px-6 py-2 border border-transparent rounded-full shadow-sm text-white bg-google-green hover:bg-google-green/90 focus:outline-none'>
                    <i class = 'fas fa-save mr-2'></i>
                    <?php echo !empty( $siswa[ 'id' ] ) ? 'Update' : 'Simpan';
                    ?>
                    </button>
                    </div>
                    </form>
                    </div>
                    <?php }
                    ?>

                    <div class = 'bg-white rounded-lg google-shadow mb-6 transition-all'>
                    <div class = 'p-6'>
                    <form action = '' method = 'get' class = 'space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4'>
                    <input type = 'hidden' name = 'page' value = 'menu'>
                    <input type = 'hidden' name = 'menu' value = 'siswa'>

                    <div class = 'flex-grow'>
                    <label for = 'search' class = 'block text-sm font-medium text-gray-700 mb-1'>Cari Siswa</label>
                    <div class = 'relative rounded-md'>
                    <div class = 'absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400'>
                    <i class = 'fas fa-search'></i>
                    </div>
                    <input type = 'text' id = 'search' name = 'search' placeholder = 'Cari berdasarkan nama, NIS, atau kelas'

                    class = 'block w-full pl-10 py-2 border-gray-300 rounded-md focus:ring-google-green focus:border-google-green'
                    value = "<?php echo $search; ?>">
                    </div>
                    </div>

                    <div class = 'md:w-64'>
                    <label for = 'filter_kelas' class = 'block text-sm font-medium text-gray-700 mb-1'>Filter Kelas</label>
                    <select id = 'filter_kelas' name = 'filter_kelas'

                    class = 'block w-full py-2 border-gray-300 rounded-md focus:ring-google-green focus:border-google-green'>
                    <option value = ''>Semua Kelas</option>
                    <?php foreach ( $kelas_list as $kelas ) {
                        ?>
                        <option value = "<?php echo $kelas; ?>" <?php echo ( $filter_kelas == $kelas ) ? 'selected' : '';
                        ?>>
                        <?php echo $kelas;
                        ?>
                        </option>
                        <?php }
                        ?>
                        </select>
                        </div>

                        <div class = 'flex space-x-2'>
                        <button type = 'submit' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-google-green hover:bg-google-green/90 focus:outline-none'>
                        <i class = 'fas fa-search mr-2'></i> Cari
                        </button>
                        <?php if ( !empty( $search ) || !empty( $filter_kelas ) ) {
                            ?>
                            <a href = 'dashboard.php?page=menu&menu=siswa' class = 'inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none'>
                            <i class = 'fas fa-times mr-2'></i> Reset
                            </a>
                            <?php }
                            ?>
                            </div>
                            </form>
                            </div>
                            </div>

                            <div class = 'grid grid-cols-1 md:grid-cols-4 gap-4 mb-6'>
                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-green/10 text-google-green mr-4'>
                            <i class = 'fas fa-users text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Total Siswa</div>
                            <div class = 'text-2xl font-bold'><?php echo $total_items;
                            ?></div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-blue/10 text-google-blue mr-4'>
                            <i class = 'fas fa-male text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Laki-laki</div>
                            <?php
                            $male_query = "SELECT COUNT(*) as total FROM siswa WHERE jenis_kelamin = 'Laki-laki'";
                            $male_result = $conn->query( $male_query );
                            $male_count = $male_result->fetch_assoc()[ 'total' ];
                            ?>
                            <div class = 'text-2xl font-bold'><?php echo $male_count;
                            ?></div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-red/10 text-google-red mr-4'>
                            <i class = 'fas fa-female text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Perempuan</div>
                            <?php
                            $female_query = "SELECT COUNT(*) as total FROM siswa WHERE jenis_kelamin = 'Perempuan'";
                            $female_result = $conn->query( $female_query );
                            $female_count = $female_result->fetch_assoc()[ 'total' ];
                            ?>
                            <div class = 'text-2xl font-bold'><?php echo $female_count;
                            ?></div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow p-4'>
                            <div class = 'flex items-center'>
                            <div class = 'p-3 rounded-full bg-google-yellow/10 text-google-yellow mr-4'>
                            <i class = 'fas fa-graduation-cap text-xl'></i>
                            </div>
                            <div>
                            <div class = 'text-gray-500 text-sm'>Jumlah Kelas</div>
                            <?php
                            $class_query = 'SELECT COUNT(DISTINCT kelas) as total FROM siswa';
                            $class_result = $conn->query( $class_query );
                            $class_count = $class_result->fetch_assoc()[ 'total' ];
                            ?>
                            <div class = 'text-2xl font-bold'><?php echo $class_count;
                            ?></div>
                            </div>
                            </div>
                            </div>
                            </div>

                            <div class = 'bg-white rounded-lg google-shadow mb-6 p-6'>
                            <div class = 'flex justify-between items-center mb-4'>
                            <h3 class = 'text-lg font-medium text-gray-800'>Distribusi Siswa per Kelas</h3>
                            </div>

                            <div class = 'flex flex-wrap'>
                            <?php
                            $class_distribution_query = 'SELECT kelas, COUNT(*) as total FROM siswa GROUP BY kelas ORDER BY kelas';
                            $class_distribution_result = $conn->query( $class_distribution_query );
                            $max_students = 0;
                            $class_data = [];

                            while ( $row = $class_distribution_result->fetch_assoc() ) {
                                $class_data[] = $row;
                                if ( $row[ 'total' ] > $max_students ) {
                                    $max_students = $row[ 'total' ];
                                }
                            }

                            foreach ( $class_data as $data ) {
                                $percentage = ( $max_students > 0 ) ? ( $data[ 'total' ] / $max_students ) * 100 : 0;
                                $color_class = getClassColor( $data[ 'kelas' ] );
                                ?>
                                <div class = 'w-full md:w-1/4 p-2'>
                                <div class = 'bg-gray-50 rounded-lg p-3'>
                                <div class = 'flex justify-between items-center'>
                                <span class = "inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $color_class; ?>">
                                <?php echo $data[ 'kelas' ];
                                ?>
                                </span>
                                <span class = 'text-gray-500 text-sm'><?php echo $data[ 'total' ];
                                ?> siswa</span>
                                </div>
                                <div class = 'mt-2 relative h-3 bg-gray-200 rounded-full overflow-hidden'>
                                <div class = "absolute top-0 left-0 h-full <?php echo str_replace('/10', '', $color_class); ?>" style = "width: <?php echo $percentage; ?>%"></div>
                                </div>
                                </div>
                                </div>
                                <?php }
                                ?>
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
                                    NIS
                                    </th>
                                    <th scope = 'col' class = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                    Nama
                                    </th>
                                    <th scope = 'col' class = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                    Kelas
                                    </th>
                                    <th scope = 'col' class = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                    Jenis Kelamin
                                    </th>
                                    <?php if ( $is_admin ) {
                                        ?>
                                        <th scope = 'col' class = 'px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider'>
                                        Aksi
                                        </th>
                                        <?php }
                                        ?>
                                        </tr>
                                        </thead>
                                        <tbody class = 'bg-white divide-y divide-gray-200'>
                                        <?php while ( $row = $result->fetch_assoc() ) {
                                            ?>
                                            <tr class = 'hover:bg-gray-50 transition-colors'>
                                            <td class = 'px-6 py-4 whitespace-nowrap'>
                                            <div class = 'text-sm font-medium text-gray-900'><?php echo $row[ 'nis' ];
                                            ?></div>
                                            <div class = 'text-xs text-gray-500'>Tahun Masuk: <?php echo getEnrollmentYear( $row[ 'nis' ] );
                                            ?></div>
                                            </td>
                                            <td class = 'px-6 py-4'>
                                            <div class = 'flex items-center'>
                                            <div class = 'flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center'>
                                            <span class = 'text-gray-600 font-medium'><?php echo substr( $row[ 'nama' ], 0, 1 );
                                            ?></span>
                                            </div>
                                            <div class = 'ml-4'>
                                            <div class = 'text-sm font-medium text-gray-900'><?php echo $row[ 'nama' ];
                                            ?></div>
                                            <div class = 'text-sm text-gray-500'><?php echo $row[ 'alamat' ];
                                            ?></div>
                                            </div>
                                            </div>
                                            </td>
                                            <td class = 'px-6 py-4'>
                                            <span class = "inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo getClassColor($row['kelas']); ?>">
                                            <?php echo $row[ 'kelas' ];
                                            ?>
                                            </span>
                                            </td>
                                            <td class = 'px-6 py-4 whitespace-nowrap'>
                                            <?php if ( $row[ 'jenis_kelamin' ] == 'Laki-laki' ) {
                                                ?>
                                                <span class = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>
                                                <i class = 'fas fa-male mr-1'></i> Laki-laki
                                                </span>
                                                <?php } else {
                                                    ?>
                                                    <span class = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800'>
                                                    <i class = 'fas fa-female mr-1'></i> Perempuan
                                                    </span>
                                                    <?php }
                                                    ?>
                                                    </td>
                                                    <?php if ( $is_admin ) {
                                                        ?>
                                                        <td class = 'px-6 py-4 whitespace-nowrap text-center text-sm'>
                                                        <a href = "dashboard.php?page=menu&menu=siswa&action=edit&id=<?php echo $row['id']; ?>"

                                                        class = 'text-google-green hover:text-google-green/70 hover:underline mr-4'>
                                                        <i class = 'fas fa-edit'></i> Edit
                                                        </a>
                                                        <a href = 'javascript:void(0);'
                                                        onclick = "confirmDelete(<?php echo $row['id']; ?>, '<?php echo $row['nama']; ?>')"

                                                        class = 'text-google-red hover:text-google-red/70 hover:underline'>
                                                        <i class = 'fas fa-trash'></i> Hapus
                                                        </a>
                                                        </td>
                                                        <?php }
                                                        ?>
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
                                                                <a href = "dashboard.php?page=menu&menu=siswa&page_number=<?php echo ($page_number - 1); ?>&search=<?php echo $search; ?>&filter_kelas=<?php echo $filter_kelas; ?>"

                                                                class = 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50'>
                                                                Previous
                                                                </a>
                                                                <?php }
                                                                ?>
                                                                <?php if ( $page_number < $total_pages ) {
                                                                    ?>
                                                                    <a href = "dashboard.php?page=menu&menu=siswa&page_number=<?php echo ($page_number + 1); ?>&search=<?php echo $search; ?>&filter_kelas=<?php echo $filter_kelas; ?>"

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
                                                                        <a href = "dashboard.php?page=menu&menu=siswa&page_number=<?php echo ($page_number - 1); ?>&search=<?php echo $search; ?>&filter_kelas=<?php echo $filter_kelas; ?>"

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
                                                                            echo '<a href="dashboard.php?page=menu&menu=siswa&page_number=1&search=' . $search . '&filter_kelas=' . $filter_kelas . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                                                            if ( $start_page > 2 ) {
                                                                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                                                            }
                                                                        }

                                                                        for ( $i = $start_page; $i <= $end_page; $i++ ) {
                                                                            $active_class = ( $i == $page_number ) ? 'bg-google-green text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
                                                                            echo '<a href="dashboard.php?page=menu&menu=siswa&page_number=' . $i . '&search=' . $search . '&filter_kelas=' . $filter_kelas . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 ' . $active_class . ' text-sm font-medium">' . $i . '</a>';
                                                                        }

                                                                        if ( $end_page < $total_pages ) {
                                                                            if ( $end_page < $total_pages - 1 ) {
                                                                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                                                            }
                                                                            echo '<a href="dashboard.php?page=menu&menu=siswa&page_number=' . $total_pages . '&search=' . $search . '&filter_kelas=' . $filter_kelas . '" 
                                             class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $total_pages . '</a>';
                                                                        }
                                                                        ?>

                                                                        <?php if ( $page_number < $total_pages ) {
                                                                            ?>
                                                                            <a href = "dashboard.php?page=menu&menu=siswa&page_number=<?php echo ($page_number + 1); ?>&search=<?php echo $search; ?>&filter_kelas=<?php echo $filter_kelas; ?>"

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
                                                                                <div class = 'inline-flex rounded-full p-6 bg-google-green/10 mb-4'>
                                                                                <i class = 'fas fa-search text-4xl text-google-green'></i>
                                                                                </div>
                                                                                <h3 class = 'text-lg font-medium text-gray-900'>Tidak ada data siswa</h3>
                                                                                <p class = 'text-gray-500 mt-2'>
                                                                                <?php echo empty( $search ) && empty( $filter_kelas ) ?
                                                                                'Belum ada data siswa yang tersedia.' :
                                                                                'Tidak ditemukan data siswa yang sesuai dengan pencarian.';
                                                                                ?>
                                                                                </p>
                                                                                <?php if ( !empty( $search ) || !empty( $filter_kelas ) ) {
                                                                                    ?>
                                                                                    <div class = 'mt-4'>
                                                                                    <a href = 'dashboard.php?page=menu&menu=siswa' class = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-google-green hover:bg-google-green/90 focus:outline-none'>
                                                                                    Reset Pencarian
                                                                                    </a>
                                                                                    </div>
                                                                                    <?php }
                                                                                    ?>
                                                                                    </div>
                                                                                    <?php }
                                                                                    ?>
                                                                                    </div>

                                                                                    <?php if ( !$is_admin ) {
                                                                                        ?>
                                                                                        <div class = 'mt-6 bg-blue-50 border-l-4 border-google-blue p-4 rounded-md'>
                                                                                        <div class = 'flex'>
                                                                                        <div class = 'flex-shrink-0'>
                                                                                        <i class = 'fas fa-info-circle text-google-blue'></i>
                                                                                        </div>
                                                                                        <div class = 'ml-3'>
                                                                                        <p class = 'text-sm text-blue-700'>
                                                                                        Hanya administrator yang dapat menambah, mengubah, atau menghapus data siswa.
                                                                                        </p>
                                                                                        </div>
                                                                                        </div>
                                                                                        </div>
                                                                                        <?php }
                                                                                        ?>
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
                                                                                        Hapus data siswa
                                                                                        </h3>
                                                                                        <div class = 'mt-2'>
                                                                                        <p class = 'text-sm text-gray-500' id = 'modal-description'>
                                                                                        Apakah Anda yakin ingin menghapus data siswa ini? Data yang dihapus tidak dapat dikembalikan.
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
                                                                                            const formContainer = document.getElementById( 'formContainer' );

                                                                                            if ( toggleFormBtn && formContainer ) {
                                                                                                toggleFormBtn.addEventListener( 'click', function() {
                                                                                                    formContainer.classList.toggle( 'hidden' );

                                                                                                    const addText = document.querySelector( '.addText' );
                                                                                                    const closeText = document.querySelector( '.closeText' );
                                                                                                    const addIcon = document.querySelector( '.addIcon' );
                                                                                                    const closeIcon = document.querySelector( '.closeIcon' );

                                                                                                    addText.classList.toggle( 'hidden' );
                                                                                                    closeText.classList.toggle( 'hidden' );
                                                                                                    addIcon.classList.toggle( 'hidden' );
                                                                                                    closeIcon.classList.toggle( 'hidden' );
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

                                                                                    if ( deleteModal && confirmDelete ) {
                                                                                        modalDescription.textContent = `Apakah Anda yakin ingin menghapus data siswa "${name}"? Data yang dihapus tidak dapat dikembalikan.`;
                                                                                        confirmDelete.href = `dashboard.php?page = menu&menu = siswa&action = delete&id = $ {
                                                                                            id}
                                                                                            `;
                                                                                            deleteModal.classList.remove( 'hidden' );
                                                                                        }
                                                                                    }
                                                                                    </script>