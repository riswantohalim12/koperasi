<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();
cek_akses(['admin', 'petugas']);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    header("Location: pinjaman.php");
    exit();
}

// Ambil Data Pinjaman
$sql = "SELECT p.*, a.nama, a.no_anggota, a.nik, a.alamat, u.nama_lengkap as nama_petugas 
        FROM pinjaman p 
        JOIN anggota a ON p.anggota_id = a.id
        JOIN users u ON p.petugas_id = u.id
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$pinjaman = $stmt->fetch();

if (!$pinjaman) {
    echo "Data tidak ditemukan.";
    exit();
}

// Hitung Statistik Pinjaman Ini
$dibayar = $pdo->query("SELECT SUM(jumlah_bayar) FROM angsuran WHERE pinjaman_id=$id")->fetchColumn();
$sisa = $pinjaman['total_angsuran'] - $dibayar;
$persen = ($dibayar / $pinjaman['total_angsuran']) * 100;

require_once 'layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h3 class="fw-bold"><i class="bi bi-file-text me-2"></i>Detail Pinjaman</h3>
    </div>
    <div class="col-md-6 text-end">
        <a href="cetak.php?type=pinjaman&id=<?php echo $id; ?>" target="_blank" class="btn btn-primary shadow-sm"><i class="bi bi-printer me-2"></i>Cetak Bukti Pencairan</a>
        <a href="pinjaman.php" class="btn btn-secondary shadow-sm ms-2"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
    </div>
</div>

<div class="row">
    <!-- Info Peminjam & Status -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                Informasi Peminjam
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="display-1 text-secondary"><i class="bi bi-person-circle"></i></div>
                    <h5 class="fw-bold mt-2"><?php echo $pinjaman['nama']; ?></h5>
                    <span class="badge bg-light text-dark border"><?php echo $pinjaman['no_anggota']; ?></span>
                </div>
                <hr>
                <p class="mb-1"><small class="text-muted">NIK</small><br><strong><?php echo $pinjaman['nik']; ?></strong></p>
                <p class="mb-1"><small class="text-muted">Alamat</small><br><?php echo $pinjaman['alamat']; ?></p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                Ringkasan Pinjaman
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Kode Pinjaman</span>
                    <span class="fw-bold"><?php echo $pinjaman['kode_pinjaman']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tanggal Cair</span>
                    <span class="fw-bold"><?php echo date('d M Y', strtotime($pinjaman['tanggal_pengajuan'])); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tenor</span>
                    <span class="fw-bold"><?php echo $pinjaman['lama_angsuran']; ?> Bulan</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Bunga</span>
                    <span class="fw-bold"><?php echo $pinjaman['bunga_persen']; ?>% / bln</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Pokok Pinjaman</span>
                    <span class="fw-bold"><?php echo format_rupiah($pinjaman['jumlah_pinjaman']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Bunga</span>
                    <span class="fw-bold"><?php echo format_rupiah($pinjaman['jumlah_bunga']); ?></span>
                </div>
                <div class="alert alert-primary p-2 mt-3 text-center mb-0">
                    <small>Total Kewajiban</small><br>
                    <h5 class="fw-bold mb-0"><?php echo format_rupiah($pinjaman['total_angsuran']); ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik & Riwayat Angsuran -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold">Status Pinjaman</small>
                        <h2 class="mb-0">
                            <?php if($pinjaman['status']=='lunas'): ?>
                                <span class="text-success fw-bold">LUNAS</span>
                            <?php else: ?>
                                <span class="text-warning fw-bold">AKTIF</span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">Sisa Tagihan</small>
                        <h3 class="fw-bold text-danger mb-0"><?php echo format_rupiah($sisa); ?></h3>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Progress Pembayaran</small>
                        <small class="fw-bold"><?php echo round($persen, 1); ?>%</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $persen; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Riwayat Pembayaran Angsuran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal Bayar</th>
                                <th>Petugas</th>
                                <th>Jumlah Bayar</th>
                                <th>Denda</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_ang = "SELECT a.*, u.nama_lengkap as nama_petugas 
                                        FROM angsuran a 
                                        JOIN users u ON a.petugas_id = u.id 
                                        WHERE pinjaman_id = ? 
                                        ORDER BY angsuran_ke ASC";
                            $stmt_ang = $pdo->prepare($sql_ang);
                            $stmt_ang->execute([$id]);
                            
                            if ($stmt_ang->rowCount() > 0):
                                while ($a = $stmt_ang->fetch()):
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary rounded-circle"><?php echo $a['angsuran_ke']; ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($a['tanggal_bayar'])); ?></td>
                                <td><small><?php echo substr($a['nama_petugas'], 0, 10); ?></small></td>
                                <td class="fw-bold text-success"><?php echo format_rupiah($a['jumlah_bayar']); ?></td>
                                <td><?php echo format_rupiah($a['denda']); ?></td>
                                <td>
                                    <a href="cetak.php?type=angsuran&id=<?php echo $a['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Cetak Struk">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Belum ada data angsuran.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
