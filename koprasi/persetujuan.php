<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();
cek_akses(['admin']); // Hanya Admin yang boleh akses

// Handle Aksi (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $aksi = $_POST['aksi']; // setuju / tolak
    
    if ($id > 0 && in_array($aksi, ['setuju', 'tolak'])) {
        try {
            $status_baru = ($aksi == 'setuju') ? 'disetujui' : 'ditolak';
            
            $stmt = $pdo->prepare("UPDATE pinjaman SET status = ? WHERE id = ?");
            $stmt->execute([$status_baru, $id]);
            
            $msg = ($aksi == 'setuju') ? "Pinjaman berhasil DISETUJUI dan Aktif." : "Pinjaman telah DITOLAK.";
            $type = ($aksi == 'setuju') ? "success" : "warning";
            
            set_flash_message('msg_approval', $msg, $type);
        } catch (PDOException $e) {
            set_flash_message('msg_approval', "Gagal memproses: " . $e->getMessage(), 'danger');
        }
    }
    header("Location: persetujuan.php");
    exit();
}

require_once 'layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-md-12">
        <h3 class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Persetujuan Pinjaman</h3>
        <p class="text-muted">Daftar pengajuan pinjaman yang menunggu konfirmasi Admin.</p>
    </div>
</div>

<?php echo get_flash_message('msg_approval'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-datatable">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Pengajuan</th>
                        <th>Kode</th>
                        <th>Anggota</th>
                        <th>Jumlah</th>
                        <th>Tenor</th>
                        <th>Bunga/Bln</th>
                        <th>Total Kewajiban</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.*, a.nama, a.no_anggota, a.nik, a.pekerjaan 
                            FROM pinjaman p 
                            JOIN anggota a ON p.anggota_id = a.id 
                            WHERE p.status = 'diajukan' 
                            ORDER BY p.tanggal_pengajuan ASC";
                    $stmt = $pdo->query($sql);
                    
                    if ($stmt->rowCount() > 0):
                        while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo $row['kode_pinjaman']; ?></span></td>
                        <td>
                            <div class="fw-bold"><?php echo $row['nama']; ?></div>
                            <small class="text-muted"><?php echo $row['no_anggota']; ?></small>
                        </td>
                        <td class="fw-bold"><?php echo format_rupiah($row['jumlah_pinjaman']); ?></td>
                        <td><?php echo $row['lama_angsuran']; ?> Bln</td>
                        <td><?php echo $row['bunga_persen']; ?>%</td>
                        <td><?php echo format_rupiah($row['total_angsuran']); ?></td>
                        <td class="text-center">
                            <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses pinjaman ini?');">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <div class="btn-group" role="group">
                                    <button type="submit" name="aksi" value="setuju" class="btn btn-sm btn-success" title="Setujui Pinjaman">
                                        <i class="bi bi-check-lg me-1"></i>Setuju
                                    </button>
                                    <button type="submit" name="aksi" value="tolak" class="btn btn-sm btn-danger" title="Tolak Pinjaman">
                                        <i class="bi bi-x-lg me-1"></i>Tolak
                                    </button>
                                </div>
                            </form>
                            <div class="mt-1">
                                <a href="detail_pinjaman.php?id=<?php echo $row['id']; ?>" class="text-decoration-none" style="font-size: 12px;">Lihat Detail</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                        <!-- Jika tidak ada data, DataTables biasanya akan handle, tapi bisa juga kita kasih info manual -->
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($stmt->rowCount() == 0): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="mt-3 text-muted">Tidak ada pengajuan pinjaman yang pending saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'layouts/footer.php'; ?>
