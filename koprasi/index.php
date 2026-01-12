<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();

// Include Header
require_once 'layouts/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold">Dashboard</h2>
        <p class="text-muted">Selamat datang kembali, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>!</p>
    </div>
</div>

<?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'petugas'): ?>
    <?php
    // Query Statistik untuk Admin/Petugas
    try {
        $total_anggota = $pdo->query("SELECT COUNT(*) FROM anggota")->fetchColumn();
        $total_simpanan = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE jenis_transaksi='setor'")->fetchColumn() - 
                          $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE jenis_transaksi='tarik'")->fetchColumn();
        $total_pinjaman_cair = $pdo->query("SELECT SUM(jumlah_pinjaman) FROM pinjaman WHERE status='disetujui' OR status='lunas'")->fetchColumn();
        $total_pinjaman_pending = $pdo->query("SELECT COUNT(*) FROM pinjaman WHERE status='diajukan'")->fetchColumn();
        
        // Data Grafik (Simpanan Setor vs Tarik)
        $chart_setor = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE jenis_transaksi='setor'")->fetchColumn();
        $chart_tarik = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE jenis_transaksi='tarik'")->fetchColumn();
        
    } catch (PDOException $e) {
        $total_anggota = 0; $total_simpanan = 0; $total_pinjaman_cair = 0; $total_pinjaman_pending = 0;
        $chart_setor = 0; $chart_tarik = 0;
    }
    ?>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(45deg, #4e73df, #224abe); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Total Anggota</h6>
                            <h2 class="fw-bold mb-0"><?php echo number_format($total_anggota); ?></h2>
                        </div>
                        <i class="bi bi-people-fill display-6" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(45deg, #1cc88a, #13855c); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Total Dana Simpanan</h6>
                            <h2 class="fw-bold mb-0" style="font-size: 1.5rem;"><?php echo format_rupiah($total_simpanan ?? 0); ?></h2>
                        </div>
                        <i class="bi bi-wallet2 display-6" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(45deg, #f6c23e, #dda20a); color: white;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Total Pinjaman Cair</h6>
                            <h2 class="fw-bold mb-0" style="font-size: 1.5rem;"><?php echo format_rupiah($total_pinjaman_cair ?? 0); ?></h2>
                        </div>
                        <i class="bi bi-cash-coin display-6" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="persetujuan.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(45deg, #e74a3b, #be2617); color: white; cursor: pointer; transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-size: 12px; opacity: 0.8;">Pinjaman Pending</h6>
                                <h2 class="fw-bold mb-0"><?php echo number_format($total_pinjaman_pending); ?></h2>
                                <small style="font-size: 11px; opacity: 0.8;">Klik untuk verifikasi</small>
                            </div>
                            <i class="bi bi-clock-history display-6" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Grafik -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                    <h6 class="m-0 fw-bold text-primary">Statistik Arus Kas Simpanan</h6>
                </div>
                <div class="card-body">
                    <canvas id="myChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Menu Cepat -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-secondary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="simpanan.php" class="btn btn-success btn-lg shadow-sm text-start">
                            <i class="bi bi-arrow-down-circle me-2"></i> Input Setoran
                        </a>
                        <a href="simpanan.php" class="btn btn-danger btn-lg shadow-sm text-start">
                            <i class="bi bi-arrow-up-circle me-2"></i> Input Penarikan
                        </a>
                        <a href="pinjaman.php" class="btn btn-warning btn-lg shadow-sm text-start text-dark">
                            <i class="bi bi-plus-circle me-2"></i> Ajukan Pinjaman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Chart -->
    <?php 
    $extra_js = "
    <script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Total Setoran Masuk', 'Total Penarikan Keluar'],
            datasets: [{
                data: [{$chart_setor}, {$chart_tarik}],
                backgroundColor: [
                    '#1cc88a',
                    '#e74a3b'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    </script>
    ";
    ?>

<?php elseif ($_SESSION['role'] == 'anggota'): ?>
    <?php
    // Query Statistik untuk Anggota
    try {
        $anggota_id = $_SESSION['anggota_id'] ?? 0;
        
        // Hitung Saldo Simpanan
        $setor = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='setor'")->fetchColumn();
        $tarik = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='tarik'")->fetchColumn();
        $saldo_simpanan = $setor - $tarik;

        // Cek Pinjaman Aktif
        $pinjaman_aktif = $pdo->query("SELECT * FROM pinjaman WHERE anggota_id = $anggota_id AND (status='disetujui') ORDER BY id DESC LIMIT 1")->fetch();
        
        $sisa_pinjaman = 0;
        if ($pinjaman_aktif) {
            $total_bayar = $pdo->query("SELECT SUM(jumlah_bayar) FROM angsuran WHERE pinjaman_id = " . $pinjaman_aktif['id'])->fetchColumn();
            $sisa_pinjaman = $pinjaman_aktif['total_angsuran'] - $total_bayar;
        }

    } catch (PDOException $e) {
        $saldo_simpanan = 0; $sisa_pinjaman = 0;
    }
    ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card text-white h-100 border-0 shadow" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                <div class="card-body p-4">
                    <h5 class="card-title text-white-50"><i class="bi bi-wallet2 me-2"></i>Saldo Simpanan Saya</h5>
                    <h1 class="fw-bold mt-3 display-5"><?php echo format_rupiah($saldo_simpanan ?? 0); ?></h1>
                    <a href="riwayat_simpanan.php" class="btn btn-light rounded-pill mt-3 text-primary fw-bold px-4">Lihat Riwayat <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-dark h-100 border-0 shadow" style="background: linear-gradient(135deg, #ffc107, #ffca2c);">
                <div class="card-body p-4">
                    <h5 class="card-title text-black-50"><i class="bi bi-exclamation-circle me-2"></i>Sisa Pinjaman Saya</h5>
                    <h1 class="fw-bold mt-3 display-5"><?php echo format_rupiah($sisa_pinjaman ?? 0); ?></h1>
                    <a href="riwayat_pinjaman.php" class="btn btn-light rounded-pill mt-3 text-warning fw-bold px-4">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Include Footer
require_once 'layouts/footer.php';
?>
