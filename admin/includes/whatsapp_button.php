<?php
// Détecter le contexte pour ajuster le chemin de la BDD
$current_path = $_SERVER['PHP_SELF'] ?? '';
$is_admin = (strpos($current_path, '/admin/') !== false);
$is_formulaire = (strpos($current_path, '/formulaire/') !== false);

// Déterminer le chemin de la BDD selon le contexte
if ($is_admin) {
    $db_path = '../database/db.php';
    $css_path = '../css/whatsapp-float.css';
} elseif ($is_formulaire) {
    $db_path = '../database/db.php';
    $css_path = '../css/whatsapp-float.css';
} else {
    $db_path = 'database/db.php';
    $css_path = 'css/whatsapp-float.css';
}

require_once $db_path;

function getWhatsAppButton() {
    try {
        // Check if whatsapp table exists, create it if not
        $stmt = $GLOBALS['pdo']->query("SHOW TABLES LIKE 'whatsapp'");
        if ($stmt->rowCount() == 0) {
            // Create table
            $GLOBALS['pdo']->exec("CREATE TABLE IF NOT EXISTS whatsapp (
                id INT AUTO_INCREMENT PRIMARY KEY,
                phone_number VARCHAR(50) NOT NULL,
                message TEXT DEFAULT 'Bonjour ! Je souhaite en savoir plus sur GriotBook.',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert default test data
            $GLOBALS['pdo']->exec("INSERT INTO whatsapp (phone_number, message, is_active) 
                                   VALUES ('+228070000000', 'Bonjour ! Je souhaite en savoir plus sur GriotBook.', TRUE)");
        }
        
        // Get active WhatsApp settings
        $stmt = $GLOBALS['pdo']->prepare("SELECT phone_number, message FROM whatsapp WHERE is_active = TRUE LIMIT 1");
        $stmt->execute();
        $whatsapp_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no active WhatsApp number found, don't show button
        if (!$whatsapp_data || empty($whatsapp_data['phone_number'])) {
            return '';
        }
        
        $phone_number = $whatsapp_data['phone_number'];
        
        // Clean phone number
        $clean_number = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Build WhatsApp URL without message (always empty)
        $whatsapp_url = "https://wa.me/{$clean_number}";
        
        return '
        <!-- FontAwesome for WhatsApp icon -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="' . ($css_path ?? 'css/whatsapp-float.css') . '">
        <a href="' . htmlspecialchars($whatsapp_url) . '" target="_blank" class="whatsapp-float">
            <i class="fab fa-whatsapp"></i>
            <span class="tooltip">Contactez-nous sur WhatsApp</span>
        </a>';
        
    } catch (Exception $e) {
        // Don't show button if there's an error
        return '';
    }
}
?>
