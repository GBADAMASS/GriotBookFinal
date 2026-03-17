<?php
session_start();
include '../database/db.php';

// Already logged in → go to form
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: ../formulaire/form.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez renseigner votre email et votre mot de passe.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, password, role FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if password is hashed or plain text (for admin compatibility)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                
                // Set admin_logged_in for dashboard compatibility
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    header('Location: dashboard.php');
                } else {
                    $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '../formulaire/form.php';
                    header('Location: ' . $redirect);
                }
                exit;
            }
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

$redirect_param = $_GET['redirect'] ?? '../form.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – GriotBook</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600&display=swap">
    <style>
        :root { --gold: #b5901f; --gold-hover: #c9a42d; }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #faf9f6;
            font-family: 'Lato', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }
        .auth-logo { margin-bottom: 28px; }
        .auth-logo img { width: 140px; }
        .auth-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 30px rgba(0,0,0,0.07);
            border-top: 4px solid var(--gold);
            padding: 40px 44px;
            width: 100%;
            max-width: 420px;
        }
        .auth-box h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: #1d1d1d;
            margin: 0 0 6px;
            text-align: center;
        }
        .auth-box .subtitle {
            font-size: 14px;
            color: #888;
            text-align: center;
            margin: 0 0 30px;
        }
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 44px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #888;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: var(--gold);
        }
        .password-toggle svg {
            width: 20px;
            height: 20px;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Lato', sans-serif;
            font-size: 14px;
            color: #333;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(181,144,31,0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--gold);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-family: 'Lato', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.3s, transform 0.2s;
            letter-spacing: 0.4px;
        }
        .btn-primary:hover { background: var(--gold-hover); transform: translateY(-1px); }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: #999;
        }
        .auth-footer a { color: var(--gold); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #eee; margin: 20px 0; }
        .info-banner {
            background: #fdf9f0;
            border: 1px solid #ede8dc;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 13px;
            color: #6b5a1e;
            margin-bottom: 24px;
            text-align: center;
        }
        @media (max-width: 480px) {
            .auth-box { padding: 30px 22px; }
        }
    </style>
</head>
<body>
    <div class="auth-logo">
        <a href="../index.php"><img src="../logo/1.png" alt="GriotBook"></a>
    </div>

    <div class="auth-box">
        <h1>Se connecter</h1>
        <p class="subtitle">Accédez à votre espace GriotBook.</p>

        <?php if (!empty($_GET['redirect']) && $_GET['redirect'] !== '../form.php'): ?>
        <?php elseif (isset($_GET['redirect'])): ?>
        <div class="info-banner">
             Connectez-vous pour accéder au formulaire et partager votre histoire.
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect_param) ?>">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="votre@email.com" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)" title="Afficher le mot de passe" aria-label="Afficher le mot de passe">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>

        <div class="auth-footer" style="margin-top:10px;">
            <a href="../index.php">← Retour au site</a>
        </div>
    </div>
    <script>
        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.title = 'Masquer le mot de passe';
                btn.setAttribute('aria-label', 'Masquer le mot de passe');
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
                btn.classList.add('visible');
            } else {
                input.type = 'password';
                btn.title = 'Afficher le mot de passe';
                btn.setAttribute('aria-label', 'Afficher le mot de passe');
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                btn.classList.remove('visible');
            }
        }
    </script>
</body>
</html>
