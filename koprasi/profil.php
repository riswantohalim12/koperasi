<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();

// Pastikan user adalah anggota
if ($_SESSION['role'] != 'anggota' || !isset($_SESSION['anggota_id'])) {
    header("Location: index.php");
    exit();
}

$anggota_id = $_SESSION['anggota_id'];

// Ambil data anggota
$stmt = $pdo->prepare("SELECT * FROM anggota WHERE id = ?");
$stmt->execute([$anggota_id]);
$anggota = $stmt->fetch();

// Hitung Statistik
$setor = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='setor'")->fetchColumn();
$tarik = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='tarik'")->fetchColumn();
$saldo = $setor - $tarik;

require_once 'layouts/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle display-1 text-primary"></i>
                </div>
                <h4><?php echo $anggota['nama']; ?></h4>
                <p class="text-muted"><?php echo $anggota['no_anggota']; ?></p>
                <hr>
                <div class="text-start">
                    <p><strong>NIK:</strong> <?php echo $anggota['nik']; ?></p>
                    <p><strong>Alamat:</strong> <?php echo $anggota['alamat']; ?></p>
                    <p><strong>Pekerjaan:</strong> <?php echo $anggota['pekerjaan']; ?></p>
                    <p><strong>Bergabung:</strong> <?php echo date('d M Y', strtotime($anggota['tanggal_gabung'])); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Saldo Simpanan</h5>
                        <h2 class="fw-bold mb-0"><?php echo format_rupiah($saldo ?? 0); ?></h2>
                    </div>
                    <i class="bi bi-wallet2 display-4"></i>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Riwayat Simpanan Terakhir
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Transaksi</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT t.*, j.nama_simpanan 
                                    FROM transaksi_simpanan t
                                    JOIN jenis_simpanan j ON t.jenis_simpanan_id = j.id
                                    WHERE t.anggota_id = ?
                                    ORDER BY t.tanggal_transaksi DESC LIMIT 10";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$anggota_id]);
                            
                            while ($row = $stmt->fetch()):
                                $color = $row['jenis_transaksi'] == 'setor' ? 'text-success' : 'text-danger';
                                $sign = $row['jenis_transaksi'] == 'setor' ? '+' : '-';
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td><?php echo $row['nama_simpanan']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['jenis_transaksi']=='setor' ? 'bg-success':'bg-danger'; ?>">
                                        <?php echo ucfirst($row['jenis_transaksi']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?php echo $color; ?>">
                                    <?php echo $sign . ' ' . format_rupiah($row['jumlah']); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="riwayat_simpanan.php" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
