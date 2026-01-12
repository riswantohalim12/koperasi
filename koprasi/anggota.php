<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();
cek_akses(['admin', 'petugas']);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// HANDLE TAMBAH / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_anggota = clean_input($_POST['no_anggota']);
    $nik = clean_input($_POST['nik']);
    $nama = clean_input($_POST['nama']);
    $alamat = clean_input($_POST['alamat']);
    $no_hp = clean_input($_POST['no_hp']);
    $pekerjaan = clean_input($_POST['pekerjaan']);
    
    try {
        if ($action == 'add') {
            // 1. Buat User Login untuk Anggota secara otomatis
            // Username = No Anggota, Password Default = NIK
            $username = $no_anggota;
            $password = password_hash($nik, PASSWORD_DEFAULT);
            
            $stmt_user = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'anggota')");
            $stmt_user->execute([$username, $password, $nama]);
            $user_id = $pdo->lastInsertId();
            
            // 2. Insert Data Anggota
            $stmt = $pdo->prepare("INSERT INTO anggota (user_id, no_anggota, nik, nama, alamat, no_hp, pekerjaan, tanggal_gabung) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->execute([$user_id, $no_anggota, $nik, $nama, $alamat, $no_hp, $pekerjaan]);
            
            set_flash_message('msg_anggota', 'Anggota berhasil ditambahkan! Username: ' . $username . ', Password: ' . $nik);
        } elseif ($action == 'edit' && $id > 0) {
            $stmt = $pdo->prepare("UPDATE anggota SET nik=?, nama=?, alamat=?, no_hp=?, pekerjaan=? WHERE id=?");
            $stmt->execute([$nik, $nama, $alamat, $no_hp, $pekerjaan, $id]);
            
            // Update nama di table users juga
            $anggota = $pdo->query("SELECT user_id FROM anggota WHERE id=$id")->fetch();
            if ($anggota && $anggota['user_id']) {
                $pdo->prepare("UPDATE users SET nama_lengkap=? WHERE id=?")->execute([$nama, $anggota['user_id']]);
            }
            
            set_flash_message('msg_anggota', 'Data anggota berhasil diperbarui!');
        }
        header("Location: anggota.php");
        exit();
    } catch (PDOException $e) {
        set_flash_message('msg_anggota', 'Gagal: ' . $e->getMessage(), 'danger');
    }
}

// HANDLE HAPUS (Khusus Admin) - PERBAIKAN DI SINI
if ($action == 'delete' && $id > 0) {
    if ($_SESSION['role'] != 'admin') {
        set_flash_message('msg_anggota', 'Anda tidak memiliki hak akses untuk menghapus!', 'danger');
        header("Location: anggota.php");
        exit();
    }
    
    // Cek apakah ada transaksi di Simpanan ATAU Pinjaman
    $cek_simpanan = $pdo->query("SELECT COUNT(*) FROM transaksi_simpanan WHERE anggota_id=$id")->fetchColumn();
    $cek_pinjaman = $pdo->query("SELECT COUNT(*) FROM pinjaman WHERE anggota_id=$id")->fetchColumn();
    
    if ($cek_simpanan > 0 || $cek_pinjaman > 0) {
        set_flash_message('msg_anggota', 'Tidak bisa dihapus karena anggota masih memiliki riwayat Simpanan atau Pinjaman!', 'warning');
    } else {
        try {
            // Hapus User terkait
            $anggota = $pdo->query("SELECT user_id FROM anggota WHERE id=$id")->fetch();
            $pdo->prepare("DELETE FROM anggota WHERE id=?")->execute([$id]);
            if ($anggota && $anggota['user_id']) {
                $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$anggota['user_id']]);
            }
            set_flash_message('msg_anggota', 'Anggota berhasil dihapus!');
        } catch (PDOException $e) {
            set_flash_message('msg_anggota', 'Gagal hapus: ' . $e->getMessage(), 'danger');
        }
    }
    header("Location: anggota.php");
    exit();
}

require_once 'layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h3 class="fw-bold">Kelola Data Anggota</h3>
    </div>
    <div class="col-md-6 text-end">
        <?php if ($action == 'list'): ?>
            <a href="anggota.php?action=add" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Tambah Anggota</a>
        <?php else: ?>
            <a href="anggota.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <?php endif; ?>
    </div>
</div>

<?php echo get_flash_message('msg_anggota'); ?>

<?php if ($action == 'list'): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>No HP</th>
                            <th>Tgl Gabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM anggota ORDER BY id DESC");
                        $no = 1;
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo $row['no_anggota']; ?></span></td>
                            <td><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['nik']; ?></td>
                            <td><?php echo $row['no_hp']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tanggal_gabung'])); ?></td>
                            <td>
                                <a href="anggota.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <?php if($_SESSION['role'] == 'admin'): ?>
                                    <a href="anggota.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus anggota ini?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($action == 'add' || $action == 'edit'): 
    $data = null;
    if ($action == 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM anggota WHERE id=?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
    }
?>
    <div class="card col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">
            <?php echo ($action == 'add') ? 'Form Tambah Anggota' : 'Form Edit Anggota'; ?>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">No Anggota</label>
                    <input type="text" name="no_anggota" class="form-control" value="<?php echo $data['no_anggota'] ?? 'A-'.rand(1000,9999); ?>" <?php echo ($action=='edit') ? 'readonly' : ''; ?> required>
                </div>
                <div class="mb-3">
                    <label class="form-label">NIK (Digunakan sebagai password awal)</label>
                    <input type="number" name="nik" class="form-control" value="<?php echo $data['nik'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo $data['nama'] ?? ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?php echo $data['alamat'] ?? ''; ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo $data['no_hp'] ?? ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-control" value="<?php echo $data['pekerjaan'] ?? ''; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Data</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'layouts/footer.php'; ?>