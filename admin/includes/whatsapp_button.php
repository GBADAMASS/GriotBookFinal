<?php
// Détecter le contexte pour ajuster le chemin
$current_path = $_SERVER['PHP_SELF'] ?? '';
$is_formulaire = (strpos($current_path, '/formulaire/') !== false);

// Déterminer le chemin selon le contexte
$css_path = $is_formulaire ? '../css/whatsapp-float.css' : 'css/whatsapp-float.css';
$db_path = $is_formulaire ? '../database/db.php' : 'database/db.php';

// Essayer de charger la base de données
if (file_exists($db_path)) {
    require_once $db_path;
}

function getWhatsAppButton($css_path = 'css/whatsapp-float.css') {
    global $pdo;
    
    // Numéro WhatsApp par défaut si la BD n'est pas disponible
    $default_phone = '+228070000000';
    $phone_number = $default_phone;
    
    try {
        // Si PDO est disponible, essayer de récupérer depuis la BD
        if (isset($pdo) && !empty($pdo)) {
            $stmt = $pdo->prepare("SELECT phone_number FROM whatsapp WHERE is_active = TRUE LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['phone_number'])) {
                $phone_number = $result['phone_number'];
            }
        }
    } catch (Exception $e) {
        // Si erreur, utiliser le numéro par défaut
        $phone_number = $default_phone;
    }
    
    // Clean phone number
    $clean_number = preg_replace('/[^0-9+]/', '', $phone_number);
    if (empty($clean_number)) {
        $clean_number = '228070000000'; // Fallback
    }
    // Remove + sign if present
    $clean_number = str_replace('+', '', $clean_number);
    
    // Build WhatsApp URL
    $whatsapp_url = "https://wa.me/{$clean_number}";
    
    return '<!-- WhatsApp Button -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="' . htmlspecialchars($css_path) . '">
<a href="' . htmlspecialchars($whatsapp_url) . '" target="_blank" class="whatsapp-float" title="Contactez-nous sur WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="tooltip">Contactez-nous sur WhatsApp</span>
</a>';
}
?>
