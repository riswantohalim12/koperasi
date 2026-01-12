<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Koperasi Simpan Pinjam</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --sidebar-width: 260px;
            --card-radius: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden;
            color: #495057;
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }
        
        .sidebar-header {
            padding: 25px;
            background: transparent;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .sidebar-menu {
            padding: 15px 10px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #94a3b8;
            text-decoration: none;
            transition: 0.3s;
            border-radius: 8px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: #fff;
            background: rgba(67, 97, 238, 0.15); /* Soft primary tint */
        }
        
        .sidebar-menu a.active {
            color: #60a5fa;
            font-weight: 600;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s;
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Navbar Top */
        .top-navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border-radius: var(--card-radius);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #f1f5f9;
        }

        /* Modern Card Styling */
        .card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            padding: 20px 25px;
            font-size: 1rem;
            color: #334155;
        }
        .card-body {
            padding: 25px;
        }
        
        /* Modern Buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border: none;
        }
        .btn-primary {
            background-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
        }

        /* Table Styling */
        .table thead th {
            border-top: none;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
            background-color: #f8fafc;
        }
        
        .table td {
            vertical-align: middle;
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            padding: 12px 20px;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            background: none;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #eef2f7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<?php
// Cek Login & Role untuk Sidebar
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    $current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4 class="fw-bold mb-0"><i class="bi bi-bank me-2"></i>KOPERASI</h4>
        <small class="text-muted">Simpan Pinjam</small>
    </div>
    <div class="sidebar-menu">
        <small class="text-muted px-3 text-uppercase fw-bold" style="font-size: 11px;">Menu Utama</small>
        
        <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <?php if ($role == 'admin' || $role == 'petugas'): ?>
            <a href="anggota.php" class="<?php echo ($current_page == 'anggota.php') ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i> Data Anggota
            </a>
            
            <a href="simpanan.php" class="<?php echo ($current_page == 'simpanan.php') ? 'active' : ''; ?>">
                <i class="bi bi-wallet2"></i> Transaksi Simpanan
            </a>
            
            <a href="pinjaman.php" class="<?php echo ($current_page == 'pinjaman.php') ? 'active' : ''; ?>">
                <i class="bi bi-cash-stack"></i> Pinjaman & Angsuran
            </a>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <div class="mt-3 mb-1 px-3 border-top pt-3 border-secondary"></div>
            <small class="text-muted px-3 text-uppercase fw-bold" style="font-size: 11px;">Administrator</small>
            
            <a href="persetujuan.php" class="<?php echo ($current_page == 'persetujuan.php') ? 'active' : ''; ?>">
                <i class="bi bi-check2-square"></i> Persetujuan Pinjaman
                <?php
                // Hitung pending
                try {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM pinjaman WHERE status='diajukan'")->fetchColumn();
                    if ($cnt > 0) echo "<span class='badge bg-warning text-dark float-end'>$cnt</span>";
                } catch(Exception $e){}
                ?>
            </a>

            <a href="users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-shield-lock-fill"></i> Manajemen User
            </a>
        <?php endif; ?>

        <?php if ($role == 'anggota'): ?>
            <a href="profil.php" class="<?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a>
            <a href="riwayat_simpanan.php" class="<?php echo ($current_page == 'riwayat_simpanan.php') ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i> Riwayat Simpanan
            </a>
            <a href="riwayat_pinjaman.php" class="<?php echo ($current_page == 'riwayat_pinjaman.php') ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i> Riwayat Pinjaman
            </a>
        <?php endif; ?>
        
        <div class="mt-4 px-3">
            <a href="logout.php" class="btn btn-danger w-100 text-white" style="border-radius: 50px;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<!-- Main Content Wrapper -->
<div class="main-content">
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="d-flex align-items-center">
            <button type="button" id="sidebarCollapse" class="btn btn-light shadow-sm me-3 d-md-none">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 fw-bold text-secondary">
                <?php 
                    if($current_page == 'index.php') echo 'Dashboard Overview';
                    elseif($current_page == 'anggota.php') echo 'Data Anggota';
                    elseif($current_page == 'simpanan.php') echo 'Transaksi Simpanan';
                    elseif($current_page == 'pinjaman.php') echo 'Pinjaman & Angsuran';
                    elseif($current_page == 'persetujuan.php') echo 'Persetujuan Pinjaman';
                    elseif($current_page == 'users.php') echo 'Manajemen Pengguna';
                    elseif($current_page == 'detail_pinjaman.php') echo 'Detail Pinjaman';
                    else echo 'Koperasi Simpan Pinjam';
                ?>
            </h5>
        </div>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="d-none d-sm-inline fw-medium"><?php echo $_SESSION['nama_lengkap']; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser1">
                <li><span class="dropdown-header text-uppercase"><?php echo $_SESSION['role']; ?></span></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-power text-danger me-2"></i>Logout</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Content Container -->
    <div class="container-fluid">

<?php } else { ?>
<!-- Jika belum login, container biasa (untuk login page) -->
<div class="container">
<?php } ?>
