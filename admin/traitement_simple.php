<?php
// Configuration
$upload_dir = '../uploads/';
$max_file_size = 10 * 1024 * 1024; // 10MB
$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowed_audio_types = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/webm', 'audio/mp4'];

// Créer le dossier uploads s'il n'existe pas
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Connexion à la base de données
require_once '../database/db.php';

// Fonction pour générer un nom de fichier unique
function generateUniqueFileName($originalName, $prefix = '') {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $timestamp = time();
    $random = rand(1000, 9999);
    return $prefix . $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

// Fonction pour valider et déplacer un fichier
function processFile($file, $prefix, $allowedTypes) {
    global $upload_dir, $max_file_size;
    
    // Vérifier si le fichier a été uploadé
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erreur lors de l'upload du fichier : " . $file['error']);
    }
    
    // Vérifier la taille
    if ($file['size'] > $max_file_size) {
        throw new Exception("Le fichier est trop volumineux (max " . ($max_file_size / 1024 / 1024) . "MB)");
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Type de fichier non autorisé : " . $mimeType);
    }
    
    // Générer un nom unique
    $uniqueName = generateUniqueFileName($file['name'], $prefix);
    $destination = $upload_dir . $uniqueName;
    
    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Impossible de déplacer le fichier vers " . $destination);
    }
    
    return $destination;
}

try {
    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode de requête non valide");
    }
    
    // Récupérer les données du formulaire
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $meeting_story = $_POST['meeting_story'] ?? '';
    
    // Validation des champs obligatoires
    if (empty($full_name) || empty($email)) {
        throw new Exception("Les champs nom et email sont obligatoires");
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'email n'est pas valide");
    }
    
    // Traiter les photos
    $photoPaths = [];
    if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        foreach ($_FILES['photos']['name'] as $key => $name) {
            if (!empty($name)) {
                $file = [
                    'name' => $name,
                    'type' => $_FILES['photos']['type'][$key],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                    'error' => $_FILES['photos']['error'][$key],
                    'size' => $_FILES['photos']['size'][$key]
                ];
                
                $photoPath = processFile($file, 'photo_', $allowed_image_types);
                $photoPaths[] = $photoPath;
            }
        }
    }
    
    // Traiter l'audio
    $audioPath = null;
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $audioPath = processFile($_FILES['audio_file'], 'audio_', $allowed_audio_types);
    }
    
    // Préparer la requête SQL
    $sql = "INSERT INTO submissions (
        full_name, email, phone, subject, meeting_story, 
        photos_paths, audio_path, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    // Convertir les chemins des photos en JSON
    $photosJson = json_encode($photoPaths);
    
    // Exécuter la requête
    $stmt->execute([
        $full_name,
        $email,
        $phone,
        $subject,
        $meeting_story,
        $photosJson,
        $audioPath
    ]);
    
    // Log pour débogage
    error_log("Soumission enregistrée avec succès. ID: " . $pdo->lastInsertId());
    error_log("Photos: " . $photosJson);
    error_log("Audio: " . ($audioPath ?? 'NULL'));
    
    // Rediriger vers le formulaire avec un message de succès
    header("Location: form_simple.php?success=1");
    exit;
    
} catch (Exception $e) {
    // Log l'erreur
    error_log("Erreur lors du traitement: " . $e->getMessage());
    
    // Rediriger vers le formulaire avec un message d'erreur
    header("Location: form_simple.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
