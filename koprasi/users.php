<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();

// HANYA ADMIN YANG BOLEH AKSES
cek_akses(['admin']);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// HANDLE ADD / EDIT / DELETE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $nama = clean_input($_POST['nama']);
    $role = $_POST['role'];
    
    // Validasi sederhana
    if (empty($username) || empty($nama)) {
        set_flash_message('msg_user', 'Username dan Nama wajib diisi!', 'danger');
    } else {
        try {
            if ($action == 'add') {
                $password = $_POST['password'];
                if (empty($password)) {
                    throw new Exception("Password wajib diisi untuk user baru!");
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $nama, $role]);
                
                set_flash_message('msg_user', 'User berhasil ditambahkan!');
            } elseif ($action == 'edit' && $id > 0) {
                // Cek apakah ganti password
                $password = $_POST['password'];
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, nama_lengkap=?, role=? WHERE id=?");
                    $stmt->execute([$username, $hashed_password, $nama, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username=?, nama_lengkap=?, role=? WHERE id=?");
                    $stmt->execute([$username, $nama, $role, $id]);
                }
                set_flash_message('msg_user', 'Data user berhasil diperbarui!');
            }
            header("Location: users.php");
            exit();
        } catch (Exception $e) {
            set_flash_message('msg_user', 'Gagal: ' . $e->getMessage(), 'danger');
        }
    }
}

if ($action == 'delete' && $id > 0) {
    // Cegah hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        set_flash_message('msg_user', 'Tidak dapat menghapus akun sendiri!', 'warning');
    } else {
        try {
            // Cek apakah user terikat dengan data anggota
            $cek = $pdo->query("SELECT COUNT(*) FROM anggota WHERE user_id=$id")->fetchColumn();
            if ($cek > 0) {
                set_flash_message('msg_user', 'User ini adalah Anggota. Hapus dari menu Data Anggota!', 'warning');
            } else {
                $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
                set_flash_message('msg_user', 'User berhasil dihapus!');
            }
        } catch (PDOException $e) {
            set_flash_message('msg_user', 'Gagal hapus: ' . $e->getMessage(), 'danger');
        }
    }
    header("Location: users.php");
    exit();
}

require_once 'layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h3 class="fw-bold">Manajemen User</h3>
        <p class="text-muted">Kelola akun Admin dan Petugas</p>
    </div>
    <div class="col-md-6 text-end">
        <?php if ($action == 'list'): ?>
            <a href="users.php?action=add" class="btn btn-primary shadow-sm"><i class="bi bi-person-plus-fill"></i> Tambah User</a>
        <?php else: ?>
            <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        <?php endif; ?>
    </div>
</div>

<?php echo get_flash_message('msg_user'); ?>

<?php if ($action == 'list'): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Tgl Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tampilkan semua user kecuali anggota (anggota diurus di menu Anggota)
                        // Atau tampilkan semua juga boleh, tapi biasanya admin sistem terpisah
                        $stmt = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'petugas') ORDER BY id DESC");
                        $no = 1;
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="fw-bold text-primary"><?php echo $row['username']; ?></span></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td>
                                <?php if($row['role']=='admin'): ?>
                                    <span class="badge bg-dark">ADMIN</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">PETUGAS</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="users.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')"><i class="bi bi-trash"></i></a>
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
    }
?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo ($action == 'add') ? 'Form Tambah User' : 'Form Edit User'; ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $data['username'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?php echo $data['nama_lengkap'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="petugas" <?php echo (isset($data['role']) && $data['role']=='petugas') ? 'selected' : ''; ?>>Petugas</option>
                                <option value="admin" <?php echo (isset($data['role']) && $data['role']=='admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password <?php echo ($action=='edit') ? '(Kosongkan jika tidak diubah)' : ''; ?></label>
                            <input type="password" name="password" class="form-control" <?php echo ($action=='add') ? 'required' : ''; ?>>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'layouts/footer.php'; ?>
