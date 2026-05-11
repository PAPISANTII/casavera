<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username === '' || $password === '') {
        $error = 'Usuario y contraseña son obligatorios';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // La contraseña por defecto es: admin123
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Casa Vera Gestión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #c0614a 0%, #9a4b35 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 48px 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            color: #1a1a1a;
        }
        .login-logo p {
            font-size: 0.875rem;
            color: #6b6b6b;
            margin-top: 4px;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            border: 1.5px solid #f2e8d9;
            border-radius: 6px;
            outline: none;
            transition: border-color 0.3s;
        }
        input:focus { border-color: #c0614a; }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #c0614a;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover { background: #a54d37; }
        .error-msg {
            background: #fdecea;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-bottom: 20px;
            border-left: 3px solid #c62828;
        }
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.75rem;
            color: #6b6b6b;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <h1>Casa Vera</h1>
            <p>Panel de Gestión</p>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>
        <p class="login-footer">Sistema privado — Solo personal autorizado</p>
    </div>
</body>
</html>