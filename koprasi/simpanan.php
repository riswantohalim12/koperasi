<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();
cek_akses(['admin', 'petugas']);

// Ambil jenis simpanan
$jenis_simpanan = $pdo->query("SELECT * FROM jenis_simpanan")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $anggota_id = $_POST['anggota_id'];
    $jenis_simpanan_id = $_POST['jenis_simpanan_id'];
    $jenis_transaksi = $_POST['jenis_transaksi'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    
    if (empty($anggota_id) || empty($jumlah) || $jumlah <= 0) {
        set_flash_message('msg_simpanan', 'Data tidak lengkap atau jumlah tidak valid!', 'danger');
    } else {
        // Generate kode transaksi
        $kode = 'TRX-' . time() . '-' . rand(100,999);
        
        try {
            // Jika tarik, cek saldo dulu
            if ($jenis_transaksi == 'tarik') {
                $setor = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='setor'")->fetchColumn();
                $tarik = $pdo->query("SELECT SUM(jumlah) FROM transaksi_simpanan WHERE anggota_id = $anggota_id AND jenis_transaksi='tarik'")->fetchColumn();
                $saldo = $setor - $tarik;
                
                if ($jumlah > $saldo) {
                    throw new Exception("Saldo tidak mencukupi! Saldo saat ini: " . format_rupiah($saldo));
                }
            }
            
            $sql = "INSERT INTO transaksi_simpanan (anggota_id, jenis_simpanan_id, petugas_id, kode_transaksi, jenis_transaksi, jumlah, keterangan) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$anggota_id, $jenis_simpanan_id, $_SESSION['user_id'], $kode, $jenis_transaksi, $jumlah, $keterangan]);
            
            set_flash_message('msg_simpanan', 'Transaksi Berhasil Disimpan!');
        } catch (Exception $e) {
            set_flash_message('msg_simpanan', 'Gagal: ' . $e->getMessage(), 'danger');
        }
    }
}

require_once 'layouts/header.php';
?>

<h3 class="fw-bold mb-4">Transaksi Simpanan</h3>
<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Input Transaksi Simpanan</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pilih Anggota</label>
                        <select name="anggota_id" class="form-select" required>
                            <option value="">-- Cari Anggota --</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, nama, no_anggota FROM anggota ORDER BY nama ASC");
                            while ($a = $stmt->fetch()) {
                                echo "<option value='{$a['id']}'>{$a['no_anggota']} - {$a['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Simpanan</label>
                        <select name="jenis_simpanan_id" class="form-select" required>
                            <?php foreach ($jenis_simpanan as $js): ?>
                                <option value="<?php echo $js['id']; ?>"><?php echo $js['nama_simpanan']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Transaksi</label>
                        <select name="jenis_transaksi" class="form-select" required>
                            <option value="setor">Setor Simpanan</option>
                            <option value="tarik">Tarik Simpanan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="jumlah" class="form-control" min="1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Simpan Transaksi</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php echo get_flash_message('msg_simpanan'); ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Riwayat Transaksi Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl</th>
                                <th>Kode</th>
                                <th>Anggota</th>
                                <th>Jenis</th>
                                <th>Debet/Kredit</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Limit dihilangkan agar DataTable bisa menghandle pagination
                            $sql = "SELECT t.*, a.nama as nama_anggota, j.nama_simpanan 
                                    FROM transaksi_simpanan t
                                    JOIN anggota a ON t.anggota_id = a.id
                                    JOIN jenis_simpanan j ON t.jenis_simpanan_id = j.id
                                    ORDER BY t.tanggal_transaksi DESC";
                            $stmt = $pdo->query($sql);
                            while ($row = $stmt->fetch()):
                                $color = $row['jenis_transaksi'] == 'setor' ? 'text-success' : 'text-danger';
                                $sign = $row['jenis_transaksi'] == 'setor' ? '+' : '-';
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td><small><?php echo $row['kode_transaksi']; ?></small></td>
                                <td><?php echo $row['nama_anggota']; ?></td>
                                <td><?php echo $row['nama_simpanan']; ?></td>
                                <td>
                                    <span class="badge <?php echo $row['jenis_transaksi']=='setor' ? 'bg-success':'bg-danger'; ?>">
                                        <?php echo ucfirst($row['jenis_transaksi']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?php echo $color; ?>">
                                    <?php echo $sign . ' ' . format_rupiah($row['jumlah']); ?>
                                </td>
                                <td>
                                    <a href="cetak.php?type=simpanan&id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Cetak Struk">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
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
