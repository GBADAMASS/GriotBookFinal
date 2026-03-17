<?php
// Pas de session_start() ici, il est déjà dans les pages principales
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['user_name'] ?? 'Admin';
$admin_email = $_SESSION['user_email'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
$is_dashboard = ($current_page == 'dashboard.php');
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../logo/1.png" alt="GriotBook" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 8px; object-fit: contain; background: white; padding: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>GriotBook</h3>
        </div>
        <p style="font-size: 12px; opacity: 0.7;">Administration</p>
    </div>

    <div class="admin-profile">
        <div class="admin-avatar">
            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
        </div>
        <div class="admin-name"><?php echo htmlspecialchars($admin_name); ?></div>
        <div class="admin-email"><?php echo htmlspecialchars($admin_email); ?></div>
    </div>

    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $is_dashboard ? 'active' : ''; ?>" <?php echo $is_dashboard ? 'onclick="showSection(\'dashboard\'); return false;"' : ''; ?>>
            <i class="fas fa-tachometer-alt"></i>
            Tableau de bord
        </a>
        <a href="admin_all_submissions.php" class="menu-item <?php echo ($current_page == 'admin_all_submissions.php' || $current_page == 'view.php') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i>
            Soumissions
        </a>
        <a href="<?php echo $is_dashboard ? '#settings' : 'dashboard.php#settings'; ?>" class="menu-item" <?php echo $is_dashboard ? 'onclick="showSection(\'settings\'); return false;"' : ''; ?>>
            <i class="fas fa-cog"></i>
            Paramètres
        </a>
    </nav>
</aside>
