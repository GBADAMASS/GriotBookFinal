<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Accès non autorisé';
    exit;
}

require_once '../database/db.php';

$submission_id = $_GET['id'] ?? 0;

if ($submission_id == 0) {
    header('HTTP/1.0 400 Bad Request');
    echo 'ID de soumission invalide';
    exit;
}

try {
    // Get submission details
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        header('HTTP/1.0 404 Not Found');
        echo 'Soumission non trouvée';
        exit;
    }
    
    // Create a text file with all submission data
    $filename = 'submission_' . $submission_id . '_' . date('Y-m-d') . '.txt';
    
    $content = "=== SOUMISSION GRIOTBOOK ===\n";
    $content .= "ID: " . $submission['id'] . "\n";
    $content .= "Date: " . $submission['created_at'] . "\n";
    $content .= "Nom: " . $submission['full_name'] . "\n";
    $content .= "Email: " . $submission['email'] . "\n";
    $content .= "Téléphone: " . $submission['phone'] . "\n";
    $content .= "Pays: " . $submission['country'] . "\n";
    $content .= "Sujet: " . $submission['subject'] . "\n";
    $content .= "Lieu de naissance: " . $submission['birth_place'] . "\n\n";
    
    $content .= "=== HISTOIRE DE RENCONTRE ===\n\n";
    $content .= $submission['meeting_story'] . "\n\n";
    
    if (!empty($submission['proudest_moment'])) {
        $content .= "=== MOMENT LE PLUS FIER ===\n\n";
        $content .= $submission['proudest_moment'] . "\n\n";
    }
    
    if (!empty($submission['hard_times'])) {
        $content .= "=== PÉRIODES DIFFICILES ===\n\n";
        $content .= $submission['hard_times'] . "\n\n";
    }
    
    if (!empty($submission['children_message'])) {
        $content .= "=== MESSAGE AUX ENFANTS ===\n\n";
        $content .= $submission['children_message'] . "\n\n";
    }
    
    // Add file information
    $photos = json_decode($submission['photos_paths'], true);
    $content .= "=== FICHIERS ===\n\n";
    
    if (is_array($photos) && count($photos) > 0) {
        $content .= "Photos (" . count($photos) . "):\n";
        foreach ($photos as $i => $photo) {
            $content .= "  " . ($i + 1) . ". " . basename($photo) . "\n";
            $content .= "     Chemin: " . $photo . "\n";
        }
        $content .= "\n";
    }
    
    if (!empty($submission['audio_path'])) {
        $content .= "Fichier audio:\n";
        $content .= "  " . basename($submission['audio_path']) . "\n";
        $content .= "  Chemin: " . $submission['audio_path'] . "\n\n";
    }
    
    $content .= "=== FIN DU RAPPORT ===\n";
    
    // Send the file to browser
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $content;
    
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Erreur: ' . $e->getMessage();
}
?>
