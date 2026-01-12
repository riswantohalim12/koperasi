<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password']; // Password raw

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login sukses
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];

                // Ambil data anggota jika role anggota
                if ($user['role'] == 'anggota') {
                    $stmt_anggota = $pdo->prepare("SELECT id FROM anggota WHERE user_id = ?");
                    $stmt_anggota->execute([$user['id']]);
                    $anggota = $stmt_anggota->fetch();
                    if ($anggota) {
                        $_SESSION['anggota_id'] = $anggota['id'];
                    }
                }

                header("Location: index.php");
                exit();
            } else {
                $error = "Username atau Password salah!";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .split-screen {
            display: flex;
            height: 100%;
        }
        .left-pane {
            flex: 1;
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }
        .left-pane::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 50%;
            height: 50%;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .left-pane::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 60%;
            height: 60%;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .right-pane {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background-color: #f8fafc;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #4361ee;
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        .btn-login {
            background-color: #4361ee;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #3f37c9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        @media (max-width: 768px) {
            .split-screen {
                flex-direction: column;
            }
            .left-pane {
                flex: 0 0 30%;
                padding: 20px;
            }
            .left-pane h1 { font-size: 1.5rem; }
            .left-pane p { display: none; }
        }
    </style>
</head>
<body>

<div class="split-screen">
    <div class="left-pane">
        <div class="text-center z-1 position-relative">
            <i class="bi bi-bank display-1 mb-4"></i>
            <h1 class="fw-bold mb-3">Koperasi Digital</h1>
            <p class="fs-5 opacity-75">Solusi keuangan modern untuk kesejahteraan bersama. Aman, Mudah, dan Terpercaya.</p>
        </div>
    </div>
    
    <div class="right-pane">
        <div class="login-box">
            <div class="text-start mb-4">
                <h3 class="fw-bold text-dark">Selamat Datang!</h3>
                <p class="text-muted">Silakan login akun Anda.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <small><?php echo $error; ?></small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login text-white">
                        MASUK SEKARANG <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">© <?php echo date('Y'); ?> Sistem Informasi Koperasi</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
