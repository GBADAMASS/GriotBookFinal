<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_SESSION['user_id'];
    
    $new_email = trim(strtolower($_POST['admin_email'] ?? ''));
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($new_email) || empty($current_password)) {
        $_SESSION['error_msg'] = "L'email et le mot de passe actuel sont obligatoires.";
        header("Location: dashboard.php#settings");
        exit;
    }

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_msg'] = "L'adresse email fournie n'est pas valide.";
        header("Location: dashboard.php#settings");
        exit;
    }

    try {
        // Verify current user exists and get password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error_msg'] = "Utilisateur introuvable.";
            header("Location: dashboard.php#settings");
            exit;
        }

        // Verify current password
        $is_valid_password = false;
        if (password_verify($current_password, $user['password']) || $current_password === $user['password']) {
            $is_valid_password = true;
        }

        if (!$is_valid_password) {
            $_SESSION['error_msg'] = "Le mot de passe actuel est incorrect.";
            header("Location: dashboard.php#settings");
            exit;
        }

        // Logic for updates
        $updates = [];
        $params = [];

        // Update email if it changed
        if ($new_email !== $_SESSION['user_email']) {
            // Check if email already exists
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check->execute([$new_email, $admin_id]);
            if ($stmt_check->fetch()) {
                $_SESSION['error_msg'] = "Cet email est déjà utilisé par un autre compte.";
                header("Location: dashboard.php#settings");
                exit;
            }

            $updates[] = "email = ?";
            $params[] = $new_email;
        }

        // Update password if requested
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error_msg'] = "Les nouveaux mots de passe ne correspondent pas.";
                header("Location: dashboard.php#settings");
                exit;
            }

            if (strlen($new_password) < 6) {
                $_SESSION['error_msg'] = "Le mot de passe doit contenir au moins 6 caractères.";
                header("Location: dashboard.php#settings");
                exit;
            }

            $updates[] = "password = ?";
            // Store hashed password
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Apply updates if there is anything to update
        if (!empty($updates)) {
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $params[] = $admin_id;
            
            $stmt_update = $pdo->prepare($sql);
            if ($stmt_update->execute($params)) {
                // Update session variables if email changed
                if ($new_email !== $_SESSION['user_email']) {
                    $_SESSION['user_email'] = $new_email;
                }
                $_SESSION['success_msg'] = "Votre profil a été mis à jour avec succès.";
            } else {
                $_SESSION['error_msg'] = "Erreur lors de la mise à jour du profil.";
            }
        } else {
            $_SESSION['success_msg'] = "Aucune modification n'a été apportée.";
        }

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Erreur de base de données : " . $e->getMessage();
    }
}

// Redirect back with profile section anchored
header("Location: dashboard.php#settings");
exit;
