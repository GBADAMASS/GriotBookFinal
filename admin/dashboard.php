<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../database/db.php';

// Get admin info
$admin_name = $_SESSION['user_name'] ?? 'Admin';
$admin_email = $_SESSION['user_email'] ?? '';

// Get WhatsApp settings from database
function getWhatsAppSettings($pdo) {
    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'whatsapp'");
        if ($stmt->rowCount() == 0) {
            return ['phone_number' => '', 'message' => ''];
        }
        
        $stmt = $pdo->query("SELECT phone_number, message FROM whatsapp WHERE is_active = TRUE LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: ['phone_number' => '', 'message' => ''];
    } catch (Exception $e) {
        return ['phone_number' => '', 'message' => ''];
    }
}

$whatsapp_settings = getWhatsAppSettings($pdo);
$site_name = 'GriotBook';

// Fetch submissions data only
try {
    $stmt = $pdo->query("SELECT * FROM submissions ORDER BY created_at DESC LIMIT 5");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all submissions for statistics
    $stmt_all_submissions = $pdo->query("SELECT COUNT(*) as total FROM submissions");
    $total_submissions_result = $stmt_all_submissions->fetch(PDO::FETCH_ASSOC);
    $total_submissions = $total_submissions_result['total'];
    
    // Get recent submissions (last 7 days) for statistics
    $stmt_recent_submissions = $pdo->query("SELECT COUNT(*) as total FROM submissions WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_submissions_result = $stmt_recent_submissions->fetch(PDO::FETCH_ASSOC);
    $recent_submissions_count = $recent_submissions_result['total'];
    
    // Get daily statistics for last 30 days (for chart)
    $daily_stats = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = (new DateTime())->modify("-$i days")->format('Y-m-d');
        $stmt_submissions = $pdo->prepare("SELECT COUNT(*) as count FROM submissions WHERE DATE(created_at) = ?");
        $stmt_submissions->execute([$date]);
        $submissions_count = $stmt_submissions->fetch(PDO::FETCH_ASSOC)['count'];
        
        $daily_stats[] = [
            'date' => $date,
            'submissions' => $submissions_count,
            'label' => (new DateTime($date))->format('d/m')
        ];
    }
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - GriotBook</title>
    <link rel='stylesheet' href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap" type='text/css' media='all'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --griot-gold: #b5901f;
            --griot-gold-dark: #8b6f1a;
            --griot-gold-light: #c9a42d;
            --dark-bg: #1a1a1a;
            --sidebar-bg: #2d2d2d;
            --card-bg: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #f5f5f5;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--dark-bg) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .sidebar-logo img {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            border-radius: 8px;
            object-fit: contain;
            background: white;
            padding: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 2px solid rgba(255,255,255,0.2);
        }

        .sidebar-logo h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--griot-gold);
        }

        .admin-profile {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .admin-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--griot-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 24px;
            color: white;
        }

        .admin-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .admin-email {
            font-size: 12px;
            opacity: 0.7;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: block;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: var(--griot-gold);
        }

        .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: var(--griot-gold);
            border-left-color: var(--griot-gold);
        }

        .menu-item i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
        }

        .top-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--text-primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        .content-area {
            padding: 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid var(--griot-gold);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--griot-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .stat-change {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .stat-change.positive {
            background: #e8f5e8;
            color: var(--success-color);
        }

        /* Tables */
        .content-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #e8f5e8;
            color: var(--success-color);
        }

        .badge-warning {
            background: #fff3e0;
            color: var(--warning-color);
        }

        .badge-primary {
            background: #e3f2fd;
            color: #1976d2;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
            margin-right: 5px;
        }

        .btn-view {
            background: var(--griot-gold);
            color: white;
        }

        .btn-view:hover {
            background: var(--griot-gold-dark);
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--text-secondary);
        }

        /* Settings Form Styles */
        .settings-container {
            max-width: 800px;
        }

        .settings-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--griot-gold);
        }

        .form-group small {
            margin-top: 5px;
            color: var(--text-secondary);
            font-size: 12px;
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
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #888;
            font-size: 16px;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: var(--griot-gold);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--griot-gold);
            color: white;
        }

        .btn-primary:hover {
            background: var(--griot-gold-dark);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }

        /* Mobile Responsive */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--griot-gold);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Tableau de bord</h1>
                <div class="header-actions">
                    <span style="color: var(--text-secondary);">
                        <i class="fas fa-clock"></i>
                        <?php echo date('d/m/Y H:i'); ?>
                    </span>
                    <a href="logout_user.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </header>

            <div class="content-area">
                <?php if(isset($_SESSION['error_msg'])): ?>
                    <div style="color: var(--danger-color); padding: 15px; background: #ffebee; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid var(--danger-color);">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php 
                        echo htmlspecialchars($_SESSION['error_msg']); 
                        unset($_SESSION['error_msg']);
                        ?>
                    </div>
                <?php elseif(isset($error)): ?>
                    <div style="color: var(--danger-color); padding: 15px; background: #ffebee; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid var(--danger-color);">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success_msg'])): ?>
                    <div style="color: var(--success-color); padding: 15px; background: #e8f5e8; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                        <?php 
                        echo htmlspecialchars($_SESSION['success_msg']); 
                        unset($_SESSION['success_msg']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Section -->
                <section id="dashboard-section" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Aperçu général</h2>
                    </div>
                    <div style="padding: 25px;">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stat-value"><?php echo $total_submissions; ?></div>
                                <div class="stat-label">Total Soumissions</div>
                                <span class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i> +<?php echo $recent_submissions_count; ?> cette semaine
                                </span>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-value"><?php echo date('d'); ?></div>
                                <div class="stat-label">Jour du mois</div>
                                <span class="stat-change positive">
                                    <i class="fas fa-clock"></i> <?php echo date('F Y'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Chart Section -->
                <section id="chart-section" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Évolution sur 30 jours</h2>
                    </div>
                    <div style="padding: 25px;">
                        <div style="position: relative; height: 400px;">
                            <canvas id="variationChart"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Submissions Section -->
                <section id="submissions-section" class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">5 Dernières histoires soumises</h2>
                        <a href="admin_all_submissions.php" class="badge badge-primary" style="text-decoration: none;">
                            Voir tout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="table-container">
                        <?php if (empty($submissions)): ?>
                            <div class="empty-state">
                                <img src="../logo/1.png" alt="GriotBook" style="width: 80px; height: 80px; margin-bottom: 15px; opacity: 0.3;">
                                <p>Aucune histoire n'a été soumise pour le moment.</p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo (new DateTime($submission['created_at']))->format('d/m/Y H:i'); ?></td>
                                            <td><strong><?php echo htmlspecialchars($submission['full_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($submission['email']); ?></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $submission['id']; ?>" class="action-btn btn-view">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings-section" class="content-section" style="display: none;">
                    <div class="section-header">
                        <h2 class="section-title">Paramètres</h2>
                        <p class="section-description">Sécurité, informations de compte et WhatsApp</p>
                    </div>
                    
                    <div class="settings-container">
                        <!-- Profil et Sécurité -->
                        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
                            <h3 style="margin-top: 0; font-size: 18px; color: var(--griot-gold); border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px;">
                                <i class="fas fa-user-shield"></i> Mon Profil d'Administrateur
                            </h3>
                            
                            <form method="POST" action="admin_update_profile.php">
                                <div class="form-row" style="grid-template-columns: 1fr;">
                                    <div class="form-group">
                                        <label for="admin_email">Adresse email</label>
                                        <input type="email" id="admin_email" name="admin_email" 
                                               value="<?= htmlspecialchars($admin_email) ?>"
                                               required>
                                        <small>L'email utilisé pour la connexion</small>
                                    </div>
                                </div>
                                
                                <h4 style="margin: 20px 0 15px; font-size: 15px; color: var(--text-primary);">Modifier le mot de passe</h4>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="new_password">Nouveau mot de passe</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="new_password" name="new_password">
                                            <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)" title="Afficher le mot de passe" aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                                        </div>
                                        <small>Laissez vide si vous ne souhaitez pas le changer</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="confirm_password" name="confirm_password">
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)" title="Afficher le mot de passe" aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row" style="grid-template-columns: 1fr; margin-top: 20px; background: #fff8e1; padding: 15px; border-radius: 8px; border-left: 4px solid var(--warning-color);">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="current_password">Mot de passe actuel <span style="color: red;">*</span></label>
                                        <div class="password-wrapper">
                                            <input type="password" id="current_password" name="current_password" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)" title="Afficher le mot de passe" aria-label="Afficher le mot de passe"><i class="fas fa-eye"></i></button>
                                        </div>
                                        <small style="color: #666;">Obligatoire pour autoriser toute modification de profil</small>
                                    </div>
                                </div>
                                
                                <div class="form-actions" style="margin-top: 20px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Mettre à jour mon profil
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Paramètres WhatsApp -->
                        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <h3 style="margin-top: 0; font-size: 18px; color: #25D366; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px;">
                                <i class="fab fa-whatsapp"></i> Paramètres WhatsApp
                            </h3>
                            <form method="POST" action="admin_save_settings.php">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="whatsapp_number">Numéro WhatsApp</label>
                                        <input type="text" id="whatsapp_number" name="whatsapp_number" 
                                               value="<?= htmlspecialchars($whatsapp_settings['phone_number'] ?? '') ?>"
                                               placeholder="+228070000000" required>
                                        <small>Format: +22800000000</small>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary" style="background: #25D366;">
                                        <i class="fas fa-save"></i> Sauvegarder WhatsApp
                                    </button>
                                    <a href="admin_all_submissions.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Retour aux soumissions
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                btn.title = 'Masquer le mot de passe';
                btn.setAttribute('aria-label', 'Masquer le mot de passe');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                btn.title = 'Afficher le mot de passe';
                btn.setAttribute('aria-label', 'Afficher le mot de passe');
            }
        }

        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(el => {
                el.style.display = 'none';
            });
            
            // Show selected section(s)
            if (section === 'dashboard') {
                document.getElementById('dashboard-section').style.display = 'block';
                document.getElementById('chart-section').style.display = 'block';
                document.getElementById('submissions-section').style.display = 'block';
            } else {
                document.getElementById(section + '-section').style.display = 'block';
            }
            
            // Update active menu
            document.querySelectorAll('.menu-item').forEach(el => {
                el.classList.remove('active');
            });
            
            // Find and activate the clicked menu item
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const onclick = item.getAttribute('onclick');
                if (onclick && onclick.includes("'" + section + "'")) {
                    item.classList.add('active');
                }
            });
        }

        // Handle URL hash on load
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash;
            if (hash) {
                const sectionId = hash.replace('#', '');
                // Basic validation string check to prevent executing garbage clicks
                if (['dashboard', 'submissions', 'settings', 'profile'].includes(sectionId)) {
                    showSection(sectionId);
                }
            }
        });

        // Chart initialization
        const ctx = document.getElementById('variationChart').getContext('2d');
        const dailyStats = <?php echo json_encode($daily_stats); ?>;
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyStats.map(stat => stat.label),
                datasets: [{
                    label: 'Soumissions',
                    data: dailyStats.map(stat => stat.submissions),
                    borderColor: '#b5901f',
                    backgroundColor: 'rgba(181, 144, 31, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
