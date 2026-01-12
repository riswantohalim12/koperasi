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

require_once 'layouts/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Riwayat Transaksi Simpanan</h3>
            <a href="profil.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali ke Profil</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kode Transaksi</th>
                                <th>Jenis Simpanan</th>
                                <th>Jenis Transaksi</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT t.*, j.nama_simpanan 
                                    FROM transaksi_simpanan t
                                    JOIN jenis_simpanan j ON t.jenis_simpanan_id = j.id
                                    WHERE t.anggota_id = ?
                                    ORDER BY t.tanggal_transaksi DESC";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$anggota_id]);
                            $no = 1;
                            while ($row = $stmt->fetch()):
                                $color = $row['jenis_transaksi'] == 'setor' ? 'text-success' : 'text-danger';
                                $sign = $row['jenis_transaksi'] == 'setor' ? '+' : '-';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td><?php echo $row['kode_transaksi']; ?></td>
                                <td><?php echo $row['nama_simpanan']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['jenis_transaksi']=='setor' ? 'bg-success':'bg-danger'; ?>">
                                        <?php echo ucfirst($row['jenis_transaksi']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?php echo $color; ?>">
                                    <?php echo $sign . ' ' . format_rupiah($row['jumlah']); ?>
                                </td>
                                <td><?php echo $row['keterangan']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
