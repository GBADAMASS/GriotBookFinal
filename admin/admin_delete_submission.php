<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../database/db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$submission_id = $data['submission_id'] ?? 0;

if ($submission_id == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID soumission invalide']);
    exit;
}

try {
    // Get submission details to delete files
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Soumission non trouvée']);
        exit;
    }
    
    // Delete photos files
    $photos = json_decode($submission['photos_paths'], true);
    if (is_array($photos)) {
        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }
    }
    
    // Delete audio file
    if (!empty($submission['audio_path']) && file_exists($submission['audio_path'])) {
        unlink($submission['audio_path']);
    }
    
    // Delete submission from database
    $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Soumission supprimée avec succès']);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
