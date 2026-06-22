<?php
session_start();
include 'db_config.php';

// Already logged in? Send the user straight to their dashboard.
if (!empty($_SESSION['admin_logged_in'])) {
    if (($_SESSION['role'] ?? '') === 'super_admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: branch_admin/branch_dashboard.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role, branch_id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['branch_id'] = $admin['branch_id'];

            if ($admin['role'] === 'super_admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: branch_admin/branch_dashboard.php");
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 16px;
        }

        .login-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            padding: 36px 30px;
        }

        .login-card .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
        }

        .login-card h1 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 6px;
            color: var(--primary);
        }

        .login-card p.subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .form-control {
            padding: 10px 14px;
        }

        .btn-login {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 11px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
        }

        .btn-login:hover {
            background: var(--primary-light);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon-circle"><i class="bi bi-shield-lock"></i></div>
        <h1>Admin Login</h1>
        <p class="subtitle">Sign in to manage your branch</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="you@example.com" autocomplete="username" required autofocus />
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter your password" autocomplete="current-password" required />
            </div>
            <button type="submit" class="btn-login mt-2">Login</button>
        </form>
    </div>
</body>
</html>
