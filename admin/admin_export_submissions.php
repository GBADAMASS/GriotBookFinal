<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Accès non autorisé';
    exit;
}

require_once '../database/db.php';

try {
    // Get all submissions
    $stmt = $pdo->query("SELECT * FROM submissions ORDER BY created_at DESC");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($submissions)) {
        header('HTTP/1.0 404 Not Found');
        echo 'Aucune soumission à exporter';
        exit;
    }
    
    // Create CSV content
    $csv_content = "ID,Date,Nom,Email,Téléphone,Pays,Sujet,Lieu de naissance,Histoire de rencontre,Moment le plus fier,Périodes difficiles,Message aux enfants,Photos,Fichier audio\n";
    
    foreach ($submissions as $sub) {
        $photos = json_decode($sub['photos_paths'], true);
        $photos_list = is_array($photos) ? implode(';', $photos) : '';
        
        $csv_content .= sprintf(
            "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
            $sub['id'],
            $sub['created_at'],
            str_replace(',', ';', $sub['full_name']),
            $sub['email'],
            $sub['phone'],
            $sub['country'],
            str_replace(',', ';', $sub['subject']),
            str_replace(',', ';', $sub['birth_place']),
            str_replace(["\n", "\r", ","], [" ", " ", ";"], $sub['meeting_story']),
            str_replace(["\n", "\r", ","], [" ", " ", ";"], $sub['proudest_moment']),
            str_replace(["\n", "\r", ","], [" ", " ", ";"], $sub['hard_times']),
            str_replace(["\n", "\r", ","], [" ", " ", ";"], $sub['children_message']),
            $photos_list,
            $sub['audio_path']
        );
    }
    
    // Set headers for CSV download
    $filename = 'soumissions_export_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output CSV content
    echo "\xEF\xBB\xBF" . $csv_content; // UTF-8 BOM for Excel compatibility
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Erreur: ' . $e->getMessage();
}
?>
