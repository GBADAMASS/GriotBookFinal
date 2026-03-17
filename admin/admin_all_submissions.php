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

// Advanced Filtering
$page = $_GET['page'] ?? 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$filter_name = $_GET['filter_name'] ?? '';
$filter_email = $_GET['filter_email'] ?? '';
$filter_country = $_GET['filter_country'] ?? '';
$filter_birth_place = $_GET['filter_birth_place'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];

if (!empty($filter_name)) {
    $where_conditions[] = "full_name LIKE :filter_name";
    $params[':filter_name'] = '%' . $filter_name . '%';
}

if (!empty($filter_email)) {
    $where_conditions[] = "email LIKE :filter_email";
    $params[':filter_email'] = '%' . $filter_email . '%';
}

if (!empty($filter_country)) {
    $where_conditions[] = "country LIKE :filter_country";
    $params[':filter_country'] = '%' . $filter_country . '%';
}

if (!empty($filter_birth_place)) {
    $where_conditions[] = "birth_place LIKE :filter_birth_place";
    $params[':filter_birth_place'] = '%' . $filter_birth_place . '%';
}

if (!empty($filter_date)) {
    $where_conditions[] = "DATE(created_at) = :filter_date";
    $params[':filter_date'] = $filter_date;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total submissions count with filters
try {
    $count_sql = "SELECT COUNT(*) as total FROM submissions $where_clause";
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute($params);
    $total_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_submissions = $total_result['total'];
    $total_pages = ceil($total_submissions / $per_page);
    
    // Get submissions with pagination and filters
    $sql = "SELECT * FROM submissions $where_clause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    // Bind filter parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', (int)$per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}

// Function to build URL with filters
function buildUrlWithFilters($page = null) {
    $params = [];
    
    if ($page !== null) {
        $params['page'] = $page;
    }
    
    if (!empty($filter_name)) $params['filter_name'] = $filter_name;
    if (!empty($filter_email)) $params['filter_email'] = $filter_email;
    if (!empty($filter_country)) $params['filter_country'] = $filter_country;
    if (!empty($filter_birth_place)) $params['filter_birth_place'] = $filter_birth_place;
    if (!empty($filter_date)) $params['filter_date'] = $filter_date;
    
    $query = http_build_query($params);
    return 'admin_all_submissions.php' . ($query ? '?' . $query : '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel='stylesheet' href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap" type='text/css' media='all'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --griot-gold: #b5901f;
            --griot-gold-dark: #8b6f1a;
            --card-bg: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2d2d2d 0%, #1a1a1a 100%);
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
            height: 40px;
            margin-right: 10px;
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

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            background: var(--griot-gold);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--griot-gold-dark);
        }

        .submissions-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
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

        .search-bar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 14px;
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

        .badge-primary {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-success {
            background: #e8f5e8;
            color: var(--success-color);
        }

        .badge-info {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .badge-audio {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
            border: none;
        }

        /* Advanced Filter Styles */
        .filter-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--griot-gold) 0%, #d4af37 100%);
        }

        .filter-section:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .filter-section h3 {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--griot-gold);
            position: relative;
        }

        .filter-section h3 i {
            color: var(--griot-gold);
            font-size: 1.2rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            align-items: start;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .filter-group label i {
            color: var(--griot-gold);
            font-size: 0.85rem;
            width: 16px;
        }

        .filter-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }

        .filter-group input:focus {
            outline: none;
            border-color: var(--griot-gold);
            box-shadow: 0 0 0 3px rgba(181, 144, 31, 0.1);
            transform: translateY(-1px);
        }

        .filter-group input::placeholder {
            color: #adb5bd;
            font-style: italic;
        }

        .filter-actions {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            justify-self: start;
        }

        .filter-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            min-width: 120px;
            justify-content: center;
        }

        .filter-btn-primary {
            background: linear-gradient(135deg, var(--griot-gold) 0%, #d4af37 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(181, 144, 31, 0.3);
        }

        .filter-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(181, 144, 31, 0.4);
        }

        .filter-btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(108, 117, 125, 0.3);
        }

        .filter-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(108, 117, 125, 0.4);
        }

        .active-filters {
            margin-top: 25px;
            padding: 18px 22px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 8px;
            border-left: 4px solid #2196f3;
            box-shadow: 0 2px 4px rgba(33, 150, 243, 0.1);
        }

        .active-filters strong {
            color: #1565c0;
            font-weight: 700;
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .active-filters em {
            color: #0d47a1;
            font-weight: 600;
            display: block;
            margin-top: 8px;
            font-size: 0.95rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .filter-actions {
                justify-self: stretch;
                justify-content: center;
            }
            
            .filter-btn {
                flex: 1;
                min-width: auto;
            }
            
            .filter-section {
                padding: 20px;
            }
            
            .filter-section h3 {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .filter-section {
                padding: 15px;
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
            }
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

        .btn-download {
            background: var(--success-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        /* Groupe de boutons d'action */
        .action-buttons-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        .action-buttons-group .action-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .action-buttons-group .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .action-buttons-group .action-btn[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
        }

        .pagination {
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-primary);
        }

        .pagination a:hover {
            background: var(--griot-gold);
            color: white;
            border-color: var(--griot-gold);
        }

        .pagination .current {
            background: var(--griot-gold);
            color: white;
            border-color: var(--griot-gold);
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--text-secondary);
        }

        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--griot-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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
                <h1 class="page-title">Toutes les soumissions</h1>
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

            <div style="padding: 30px;">
                <!-- Advanced Filter Form -->
                <div class="filter-section" style="background: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 20px; color: var(--text-primary); border-bottom: 2px solid var(--griot-gold); padding-bottom: 10px;">
                        <i class="fas fa-search"></i> Recherche et Filtrage
                    </h3>
                    <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-secondary);">Nom complet</label>
                            <input type="text" name="filter_name" value="<?php echo htmlspecialchars($filter_name); ?>" 
                                   placeholder="Rechercher par nom" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-secondary);">Email</label>
                            <input type="email" name="filter_email" value="<?php echo htmlspecialchars($filter_email); ?>" 
                                   placeholder="Rechercher par email" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-secondary);">Pays</label>
                            <input type="text" name="filter_country" value="<?php echo htmlspecialchars($filter_country); ?>" 
                                   placeholder="Rechercher par pays" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-secondary);">Lieu de naissance</label>
                            <input type="text" name="filter_birth_place" value="<?php echo htmlspecialchars($filter_birth_place); ?>" 
                                   placeholder="Rechercher par lieu de naissance" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-secondary);">Date de soumission</label>
                            <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filter_date ?? ''); ?>" 
                                   style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <button type="submit" style="background: var(--griot-gold); color: white; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px; min-width: 80px;">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <a href="admin_all_submissions.php" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 16px; border-radius: 4px; display: inline-block; font-weight: 600; font-size: 14px; min-width: 80px;">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                            <a href="export_preview.php?<?php echo http_build_query(array_filter([
                                'filter_name' => $filter_name,
                                'filter_email' => $filter_email,
                                'filter_country' => $filter_country,
                                'filter_birth_place' => $filter_birth_place,
                                'filter_date' => $filter_date
                            ])); ?>" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; text-decoration: none; padding: 10px 16px; border-radius: 4px; display: inline-block; font-weight: 600; font-size: 14px; min-width: 80px; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);">
                                <i class="fas fa-download"></i> Téléchargement
                            </a>
                        </div>
                    </form>
                    
                    <?php if (!empty($filter_name) || !empty($filter_email) || !empty($filter_country) || !empty($filter_birth_place) || !empty($filter_date)): ?>
                        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 4px; border-left: 4px solid #2196f3;">
                            <strong>Filtres actifs :</strong>
                            <?php if (!empty($filter_name)): ?> Nom: "<?php echo htmlspecialchars($filter_name); ?>" <?php endif; ?>
                            <?php if (!empty($filter_email)): ?> Email: "<?php echo htmlspecialchars($filter_email); ?>" <?php endif; ?>
                            <?php if (!empty($filter_country)): ?> Pays: "<?php echo htmlspecialchars($filter_country); ?>" <?php endif; ?>
                            <?php if (!empty($filter_birth_place)): ?> Lieu: "<?php echo htmlspecialchars($filter_birth_place); ?>" <?php endif; ?>
                            <?php if (!empty($filter_date)): ?> Date: <?php echo htmlspecialchars($filter_date); ?> <?php endif; ?>
                            <br><em><?php echo $total_submissions; ?> résultat(s) trouvé(s)</em>
                        </div>
                    <?php endif; ?>
                </div>

        <?php if(isset($error)): ?>
            <div style="color: var(--danger-color); padding: 15px; background: #ffebee; border-radius: 5px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php else: ?>

        <div class="submissions-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-book"></i> Liste complète des soumissions
                </h3>
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Rechercher..." id="searchInput">
                </div>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <strong><?php echo $total_submissions; ?></strong>
                        <div style="font-size: 12px; color: var(--text-secondary);">Total soumissions</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-file"></i>
                    </div>
                    <div>
                        <strong><?php echo $page; ?>/<?php echo $total_pages; ?></strong>
                        <div style="font-size: 12px; color: var(--text-secondary);">Page</div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <?php if (empty($submissions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-book" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>Aucune soumission trouvée.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Pays</th>
                                <th>Lieu de naissance</th>
                                <th>Fichiers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <?php 
                                    $photos = json_decode($sub['photos_paths'] ?? '[]', true);
                                    $has_photos = is_array($photos) && count($photos) > 0;
                                    $has_audio = !empty($sub['audio_path']);
                                    $has_multiple_audios = !empty($sub['audio_paths']);
                                    $multiple_audios = json_decode($sub['audio_paths'] ?? '[]', true);
                                    $multiple_audio_count = is_array($multiple_audios) ? count($multiple_audios) : 0;
                                    $date = new DateTime($sub['created_at']);
                                ?>
                                <tr>
                                    <td><?php echo $date->format('d/m/Y H:i'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($sub['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sub['email']); ?></td>
                                    <td><?php echo htmlspecialchars($sub['phone'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($sub['country'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($sub['birth_place'] ?: '-'); ?></td>
                                    <td>
                                        <?php if($has_photos): ?><span class="badge badge-primary">📸 <?php echo count($photos); ?></span><?php endif; ?>
                                        <?php if($has_audio): ?><span class="badge badge-success">🎵 Audio</span><?php endif; ?>
                                        <?php if($has_multiple_audios && $multiple_audio_count > 0): ?><span class="badge badge-audio">🎵 <?php echo $multiple_audio_count; ?> Audio<?php echo $multiple_audio_count > 1 ? 's' : ''; ?></span><?php endif; ?>
                                        <?php if(!$has_photos && !$has_audio && !$has_multiple_audios): ?><span style="color: #ccc;">-</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons-group">
                                            <a href="view.php?id=<?php echo $sub['id']; ?>" class="action-btn btn-view" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="action-btn btn-danger" onclick="deleteSubmission(<?php echo $sub['id']; ?>)" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo buildUrlWithFilters($page - 1); ?>">«</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo buildUrlWithFilters($i); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo buildUrlWithFilters($page + 1); ?>">»</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>

    <script>
        function downloadSubmission(submissionId) {
            window.open(`admin_download_submission.php?id=${submissionId}`, '_blank');
        }

        function deleteSubmission(submissionId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette soumission ? Cette action est irréversible.')) {
                fetch('admin_delete_submission.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ submission_id: submissionId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Soumission supprimée avec succès');
                        location.reload();
                    } else {
                        alert('Erreur lors de la suppression: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de la suppression');
                });
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
