<?php
require_once 'config/database.php';
require_once 'config/functions.php';

cek_login();

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($type) || empty($id)) {
    die("Parameter tidak valid.");
}

$data = null;
$judul = "";

// Ambil Data Berdasarkan Tipe
if ($type == 'simpanan') {
    $judul = "BUKTI TRANSAKSI SIMPANAN";
    $sql = "SELECT t.*, a.nama as nama_anggota, a.no_anggota, j.nama_simpanan, u.nama_lengkap as nama_petugas
            FROM transaksi_simpanan t
            JOIN anggota a ON t.anggota_id = a.id
            JOIN jenis_simpanan j ON t.jenis_simpanan_id = j.id
            JOIN users u ON t.petugas_id = u.id
            WHERE t.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch();

} elseif ($type == 'angsuran') {
    $judul = "BUKTI PEMBAYARAN ANGSURAN";
    $sql = "SELECT ang.*, p.kode_pinjaman, a.nama as nama_anggota, a.no_anggota, u.nama_lengkap as nama_petugas
            FROM angsuran ang
            JOIN pinjaman p ON ang.pinjaman_id = p.id
            JOIN anggota a ON p.anggota_id = a.id
            JOIN users u ON ang.petugas_id = u.id
            WHERE ang.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    
} elseif ($type == 'pinjaman') {
    $judul = "BUKTI PENCAIRAN PINJAMAN";
    $sql = "SELECT p.*, a.nama as nama_anggota, a.no_anggota, a.alamat, a.nik, u.nama_lengkap as nama_petugas
            FROM pinjaman p
            JOIN anggota a ON p.anggota_id = a.id
            JOIN users u ON p.petugas_id = u.id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch();
}

if (!$data) {
    die("Data tidak ditemukan.");
}

// Format Tanggal
$tanggal = isset($data['tanggal_transaksi']) ? $data['tanggal_transaksi'] : 
           (isset($data['tanggal_bayar']) ? $data['tanggal_bayar'] : 
           (isset($data['tanggal_pengajuan']) ? $data['tanggal_pengajuan'] : date('Y-m-d')));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Struk - <?php echo $judul; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Font struk */
            font-size: 14px;
            margin: 0;
            padding: 20px;
            background: #eee;
        }
        .struk-container {
            width: 80mm; /* Ukuran kertas struk thermal standar 80mm */
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h3 { margin: 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 12px; }
        
        .content {
            margin-bottom: 10px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .label { font-weight: bold; }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 2px dashed #333;
            padding-top: 10px;
            font-size: 12px;
        }
        
        .total-box {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 10px 0;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
        }
        
        @media print {
            body { background: #fff; padding: 0; }
            .struk-container {
                width: 100%;
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="struk-container">
        <div class="header">
            <h3>KOPERASI SIMPAN PINJAM</h3>
            <p>Jl. Merdeka No. 45, Jakarta</p>
            <p>Telp: (021) 123-4567</p>
        </div>

        <div class="content">
            <div style="text-align: center; margin-bottom: 15px; font-weight: bold;">
                <?php echo $judul; ?>
            </div>
            
            <div class="row">
                <span>Tanggal</span>
                <span><?php echo date('d/m/Y H:i', strtotime($tanggal)); ?></span>
            </div>
            <div class="row">
                <span>Petugas</span>
                <span><?php echo substr($data['nama_petugas'], 0, 15); ?></span>
            </div>
            <div class="row">
                <span>Anggota</span>
                <span><?php echo $data['no_anggota'] . ' - ' . substr($data['nama_anggota'], 0, 10); ?></span>
            </div>
            
            <hr style="border-top: 1px dashed #ccc;">

            <?php if ($type == 'simpanan'): ?>
                <div class="row">
                    <span>Jenis</span>
                    <span><?php echo $data['nama_simpanan']; ?></span>
                </div>
                <div class="row">
                    <span>Transaksi</span>
                    <span><?php echo strtoupper($data['jenis_transaksi']); ?></span>
                </div>
                <div class="row">
                    <span>Kode TRX</span>
                    <span><?php echo $data['kode_transaksi']; ?></span>
                </div>
                <div class="total-box">
                    Total: <?php echo format_rupiah($data['jumlah']); ?>
                </div>

            <?php elseif ($type == 'angsuran'): ?>
                <div class="row">
                    <span>No Pinjaman</span>
                    <span><?php echo $data['kode_pinjaman']; ?></span>
                </div>
                <div class="row">
                    <span>Angsuran Ke</span>
                    <span><?php echo $data['angsuran_ke']; ?></span>
                </div>
                <?php if ($data['denda'] > 0): ?>
                <div class="row">
                    <span>Denda</span>
                    <span><?php echo format_rupiah($data['denda']); ?></span>
                </div>
                <?php endif; ?>
                <div class="total-box">
                    Bayar: <?php echo format_rupiah($data['jumlah_bayar']); ?>
                </div>

            <?php elseif ($type == 'pinjaman'): ?>
                 <div class="row">
                    <span>No Pinjaman</span>
                    <span><?php echo $data['kode_pinjaman']; ?></span>
                </div>
                <div class="row">
                    <span>Tenor</span>
                    <span><?php echo $data['lama_angsuran']; ?> Bulan</span>
                </div>
                <div class="row">
                    <span>Bunga</span>
                    <span><?php echo $data['bunga_persen']; ?>% / Bulan</span>
                </div>
                <div class="total-box">
                    Cair: <?php echo format_rupiah($data['jumlah_pinjaman']); ?>
                </div>
                <div class="row">
                    <span>Kewajiban</span>
                    <span><?php echo format_rupiah($data['total_angsuran']); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($data['keterangan'])): ?>
                <div class="row" style="margin-top: 5px; font-style: italic;">
                    <small>Ket: <?php echo $data['keterangan']; ?></small>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>Terima Kasih atas kepercayaan Anda.</p>
            <p>Simpan struk ini sebagai bukti sah.</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Cetak Struk</button>
            <br><br>
            <button onclick="window.close()" style="cursor: pointer;">Tutup</button>
        </div>
    </div>

    <script>
        // Otomatis print saat load (opsional, bisa dimatikan jika mengganggu)
        // window.print();
    </script>
</body>
</html>
