<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();

if ($_SESSION['role'] != 'anggota' || !isset($_SESSION['anggota_id'])) {
    header("Location: index.php");
    exit();
}

$anggota_id = $_SESSION['anggota_id'];

require_once 'layouts/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Riwayat Pinjaman Saya</h3>
            <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
        
        <?php
        $sql = "SELECT * FROM pinjaman WHERE anggota_id = ? ORDER BY tanggal_pengajuan DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$anggota_id]);
        $pinjaman = $stmt->fetchAll();
        
        if (count($pinjaman) > 0):
            foreach ($pinjaman as $p):
                $dibayar = $pdo->query("SELECT SUM(jumlah_bayar) FROM angsuran WHERE pinjaman_id=".$p['id'])->fetchColumn();
                $sisa = $p['total_angsuran'] - $dibayar;
                $persen = ($dibayar / $p['total_angsuran']) * 100;
        ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <span class="fw-bold">Pinjaman #<?php echo $p['kode_pinjaman']; ?> (<?php echo date('d M Y', strtotime($p['tanggal_pengajuan'])); ?>)</span>
                    <?php if($p['status']=='lunas'): ?>
                        <span class="badge bg-success">LUNAS</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">AKTIF</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Jumlah Pinjaman</small>
                            <h5><?php echo format_rupiah($p['jumlah_pinjaman']); ?></h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Kewajiban</small>
                            <h5><?php echo format_rupiah($p['total_angsuran']); ?></h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Sudah Dibayar</small>
                            <h5 class="text-success"><?php echo format_rupiah($dibayar ?? 0); ?></h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Sisa Tagihan</small>
                            <h5 class="text-danger"><?php echo ($sisa <= 0) ? '-' : format_rupiah($sisa); ?></h5>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small>Progress Pembayaran</small>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $persen; ?>%;" aria-valuenow="<?php echo $persen; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($persen); ?>%</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Riwayat Angsuran</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Angsuran Ke</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Jumlah Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_ang = "SELECT * FROM angsuran WHERE pinjaman_id = ? ORDER BY angsuran_ke ASC";
                                $stmt_ang = $pdo->prepare($sql_ang);
                                $stmt_ang->execute([$p['id']]);
                                while ($a = $stmt_ang->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo $a['angsuran_ke']; ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($a['tanggal_bayar'])); ?></td>
                                    <td><?php echo format_rupiah($a['jumlah_bayar']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <div class="alert alert-info">Belum ada riwayat pinjaman.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
