<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $whatsapp_number = $_POST['whatsapp_number'] ?? '';
        
        // Create whatsapp table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS whatsapp (
            id INT AUTO_INCREMENT PRIMARY KEY,
            phone_number VARCHAR(50) NOT NULL,
            message TEXT DEFAULT '',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // First, deactivate all existing entries
        $pdo->exec("UPDATE whatsapp SET is_active = FALSE");
        
        // Insert or update WhatsApp settings (with empty message)
        $stmt = $pdo->prepare("INSERT INTO whatsapp (phone_number, message, is_active) 
                                   VALUES (?, '', TRUE) 
                                   ON DUPLICATE KEY UPDATE 
                                   phone_number = VALUES(phone_number), 
                                   message = '', 
                                   is_active = VALUES(is_active)");
        $stmt->execute([$whatsapp_number]);
        
        $_SESSION['success_msg'] = "Paramètres WhatsApp sauvegardés avec succès!";
        
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Erreur lors de la sauvegarde : " . $e->getMessage();
    }
}

// Redirect back to dashboard
header("Location: dashboard.php#settings");
exit;
?>
