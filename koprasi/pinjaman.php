<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();
cek_akses(['admin', 'petugas']);

// PROSES PENGAJUAN PINJAMAN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajukan_pinjaman'])) {
    $anggota_id = $_POST['anggota_id'];
    $jumlah_pinjaman = $_POST['jumlah_pinjaman'];
    $lama_angsuran = $_POST['lama_angsuran']; // bulan
    $bunga_persen = 2; // Flat 2% per bulan misal
    
    if ($jumlah_pinjaman > 0 && $lama_angsuran > 0) {
        $kode = 'PINJ-' . time();
        $jumlah_bunga = $jumlah_pinjaman * ($bunga_persen/100) * $lama_angsuran;
        $total_angsuran = $jumlah_pinjaman + $jumlah_bunga;
        
        try {
            $sql = "INSERT INTO pinjaman (anggota_id, petugas_id, kode_pinjaman, jumlah_pinjaman, bunga_persen, jumlah_bunga, total_angsuran, lama_angsuran, tanggal_pengajuan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'diajukan')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$anggota_id, $_SESSION['user_id'], $kode, $jumlah_pinjaman, $bunga_persen, $jumlah_bunga, $total_angsuran, $lama_angsuran]);
            
            set_flash_message('msg_pinjaman', 'Pinjaman berhasil diajukan! Menunggu persetujuan Admin.');
        } catch (PDOException $e) {
            set_flash_message('msg_pinjaman', 'Gagal: ' . $e->getMessage(), 'danger');
        }
    }
}

// PROSES BAYAR ANGSURAN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar_angsuran'])) {
    $pinjaman_id = $_POST['pinjaman_id'];
    $jumlah_bayar = $_POST['jumlah_bayar'];
    $angsuran_ke = $_POST['angsuran_ke'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO angsuran (pinjaman_id, petugas_id, angsuran_ke, jumlah_bayar, tanggal_bayar) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$pinjaman_id, $_SESSION['user_id'], $angsuran_ke, $jumlah_bayar]);
        
        // Cek apakah lunas
        $total_bayar = $pdo->query("SELECT SUM(jumlah_bayar) FROM angsuran WHERE pinjaman_id=$pinjaman_id")->fetchColumn();
        $total_hutang = $pdo->query("SELECT total_angsuran FROM pinjaman WHERE id=$pinjaman_id")->fetchColumn();
        
        if ($total_bayar >= $total_hutang) {
            $pdo->prepare("UPDATE pinjaman SET status='lunas' WHERE id=?")->execute([$pinjaman_id]);
            set_flash_message('msg_pinjaman', 'Angsuran diterima. Pinjaman LUNAS!', 'success');
        } else {
            set_flash_message('msg_pinjaman', 'Angsuran diterima.');
        }
    } catch (PDOException $e) {
        set_flash_message('msg_pinjaman', 'Gagal: ' . $e->getMessage(), 'danger');
    }
}

require_once 'layouts/header.php';
?>

<h3 class="fw-bold mb-4">Pinjaman & Angsuran</h3>
<?php echo get_flash_message('msg_pinjaman'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="data-tab" data-bs-toggle="tab" data-bs-target="#data" type="button" role="tab"><i class="bi bi-list-ul me-2"></i>Data Pinjaman</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button" role="tab"><i class="bi bi-plus-circle me-2"></i>Input Transaksi Baru</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="myTabContent">
            
            <!-- TAB DATA PINJAMAN (SEMUA) -->
            <div class="tab-pane fade show active" id="data" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover table-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Anggota</th>
                                <th>Pokok</th>
                                <th>Total Hutang</th>
                                <th>Lama</th>
                                <th>Status</th>
                                <th>Sisa Tagihan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, a.nama FROM pinjaman p JOIN anggota a ON p.anggota_id = a.id ORDER BY p.id DESC";
                            $stmt = $pdo->query($sql);
                            while ($row = $stmt->fetch()):
                                $dibayar = $pdo->query("SELECT SUM(jumlah_bayar) FROM angsuran WHERE pinjaman_id=".$row['id'])->fetchColumn();
                                $sisa = $row['total_angsuran'] - $dibayar;
                            ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border"><?php echo $row['kode_pinjaman']; ?></span></td>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo format_rupiah($row['jumlah_pinjaman']); ?></td>
                                <td><?php echo format_rupiah($row['total_angsuran']); ?></td>
                                <td><?php echo $row['lama_angsuran']; ?> Bln</td>
                                <td>
                                    <?php if($row['status']=='lunas'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                                    <?php elseif($row['status']=='disetujui'): ?>
                                        <span class="badge bg-primary"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                    <?php elseif($row['status']=='diajukan'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                    <?php elseif($row['status']=='ditolak'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-danger"><?php echo ($sisa <= 0) ? '-' : format_rupiah($sisa); ?></td>
                                <td>
                                    <a href="detail_pinjaman.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Detail</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB INPUT FORM -->
            <div class="tab-pane fade" id="form" role="tabpanel">
                <div class="row">
                    <!-- Form Pengajuan -->
                    <div class="col-md-6">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-header bg-warning text-dark fw-bold">
                                <i class="bi bi-file-earmark-plus me-2"></i>Pengajuan Pinjaman Baru
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <input type="hidden" name="ajukan_pinjaman" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Anggota</label>
                                        <select name="anggota_id" class="form-select" required>
                                            <option value="">-- Pilih Anggota --</option>
                                            <?php
                                            $stmt = $pdo->query("SELECT id, nama, no_anggota FROM anggota ORDER BY nama");
                                            while ($row = $stmt->fetch()) {
                                                echo "<option value='{$row['id']}'>{$row['no_anggota']} - {$row['nama']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Jumlah Pinjaman</label>
                                            <input type="number" name="jumlah_pinjaman" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tenor (Bulan)</label>
                                            <input type="number" name="lama_angsuran" class="form-control" value="12" required>
                                        </div>
                                    </div>
                                    <div class="alert alert-info py-2"><i class="bi bi-info-circle me-2"></i>Bunga flat 2% per bulan</div>
                                    <button type="submit" class="btn btn-warning w-100 fw-bold">Proses Pengajuan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Bayar Angsuran -->
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-header bg-success text-white fw-bold">
                                <i class="bi bi-cash-coin me-2"></i>Input Pembayaran Angsuran
                            </div>
                            <div class="card-body">
                                <form action="" method="POST">
                                    <input type="hidden" name="bayar_angsuran" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Pinjaman Aktif</label>
                                        <select name="pinjaman_id" class="form-select" id="select_pinjaman" onchange="updateAngsuranInfo()" required>
                                            <option value="">-- Pilih No Pinjaman --</option>
                                            <?php
                                            $sql = "SELECT p.*, a.nama FROM pinjaman p JOIN anggota a ON p.anggota_id = a.id WHERE p.status='disetujui'";
                                            $stmt = $pdo->query($sql);
                                            while ($row = $stmt->fetch()) {
                                                $cicilan = $row['total_angsuran'] / $row['lama_angsuran'];
                                                echo "<option value='{$row['id']}' data-cicilan='".ceil($cicilan)."'>{$row['kode_pinjaman']} - {$row['nama']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Angsuran Ke-</label>
                                            <input type="number" name="angsuran_ke" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Jumlah Bayar</label>
                                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 fw-bold">Simpan Pembayaran</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateAngsuranInfo() {
    var select = document.getElementById('select_pinjaman');
    var option = select.options[select.selectedIndex];
    var cicilan = option.getAttribute('data-cicilan');
    if (cicilan) {
        document.getElementById('jumlah_bayar').value = cicilan;
    } else {
        document.getElementById('jumlah_bayar').value = '';
    }
}
</script>

<?php require_once 'layouts/footer.php'; ?>
