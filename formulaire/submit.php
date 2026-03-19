<?php
session_start();
require_once '../database/db.php';

// Récupérer l'ID de l'utilisateur s'il est connecté
$current_user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize simple text inputs
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING) ?? '';
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) ?? '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING) ?? '';
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING) ?? '';
    
    $birth_place = filter_input(INPUT_POST, 'birth_place', FILTER_SANITIZE_STRING) ?? '';
    $book_about = filter_input(INPUT_POST, 'book_about', FILTER_SANITIZE_STRING) ?? '';
    $meeting_story = filter_input(INPUT_POST, 'meeting_story', FILTER_SANITIZE_STRING) ?? '';
    $proudest_moment = filter_input(INPUT_POST, 'proudest_moment', FILTER_SANITIZE_STRING) ?? '';
    $hard_times = filter_input(INPUT_POST, 'hard_times', FILTER_SANITIZE_STRING) ?? '';
    $children_message = filter_input(INPUT_POST, 'children_message', FILTER_SANITIZE_STRING) ?? '';

    // Basic validation
    if (empty($full_name) || empty($email)) {
        $_SESSION['error_msg'] = "Veuillez remplir les champs obligatoires (Nom et Email).";
        header("Location: form.php");
        exit;
    }

    // 2. Handle File Uploads
    $upload_dir = __DIR__ . '/../uploads/';
    error_log("Upload directory: " . $upload_dir);
    
    if (!is_dir($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            error_log("Upload directory created successfully");
        } else {
            error_log("Failed to create upload directory");
            $_SESSION['error_msg'] = "Erreur: impossible de créer le dossier de téléchargement.";
            header("Location: form.php");
            exit;
        }
    }
    
    if (!is_writable($upload_dir)) {
        error_log("Upload directory is not writable");
        $_SESSION['error_msg'] = "Erreur: le dossier de téléchargement n'est pas accessible en écriture.";
        header("Location: form.php");
        exit;
    }

    $uploaded_photos = [];
    $uploaded_audios = [];

    // Process Photos
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $file_count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $tmp_name = $_FILES['photos']['tmp_name'][$i];
            $name = basename($_FILES['photos']['name'][$i]);
            $error = $_FILES['photos']['error'][$i];

            if ($error === UPLOAD_ERR_OK) {
                // Generate unique name to prevent overwriting
                $unique_name = uniqid() . '_' . $name;
                $destination = $upload_dir . $unique_name;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $uploaded_photos[] = 'uploads/' . $unique_name; // Store relative path
                }
            }
        }
    }

    // Process Multiple Audio Files (supports uploaded files and MediaRecorder blobs: webm, ogg, mp3, wav, m4a)
    if (isset($_FILES['audio_files']) && !empty($_FILES['audio_files']['name'][0])) {
        $audio_file_count = count($_FILES['audio_files']['name']);
        for ($i = 0; $i < $audio_file_count; $i++) {
            $tmp_name  = $_FILES['audio_files']['tmp_name'][$i];
            $orig_name = basename($_FILES['audio_files']['name'][$i]);
            $error     = $_FILES['audio_files']['error'][$i];
            $size      = $_FILES['audio_files']['size'][$i];

            if ($error === UPLOAD_ERR_OK) {
                $mime = mime_content_type($tmp_name);

                // Déterminer l'extension sécurisée à partir du MIME type
                $ext_map = [
                    'audio/webm'     => 'webm',
                    'audio/ogg'      => 'ogg', 
                    'audio/mpeg'     => 'mp3',
                    'audio/mp4'      => 'm4a',
                    'audio/wav'      => 'wav',
                    'audio/x-wav'    => 'wav',
                    'audio/mp3'      => 'mp3',
                    'audio/x-m4a'    => 'm4a',
                ];
                
                $safe_ext = $ext_map[$mime] ?? pathinfo($orig_name, PATHINFO_EXTENSION);
                
                // Validation de l'extension
                if (!in_array(strtolower($safe_ext), ['webm','ogg','mp3','m4a','wav','mp4'])) {
                    // Essayer de détecter depuis le nom du fichier
                    $safe_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    if (!in_array($safe_ext, ['webm','ogg','mp3','m4a','wav','mp4'])) {
                        $safe_ext = 'webm'; // fallback par défaut
                    }
                }

                $unique_name  = 'audio_' . uniqid() . '.' . $safe_ext;
                $destination  = $upload_dir . $unique_name;

                // Validation de la taille (max 50MB)
                $max_size = 50 * 1024 * 1024; // 50MB
                if ($size > $max_size) {
                    $_SESSION['error_msg'] = "Le fichier audio '{$orig_name}' est trop volumineux. Taille maximale: 50MB.";
                    header("Location: form.php");
                    exit;
                }

                if (move_uploaded_file($tmp_name, $destination)) {
                    $uploaded_audios[] = 'uploads/' . $unique_name;
                    
                    // Log pour débogage
                    error_log("Audio uploaded: " . $unique_name . " (MIME: " . $mime . ", Size: " . $size . " bytes)");
                    error_log("Audio path stored: " . $destination);
                } else {
                    error_log("Failed to upload audio: " . $orig_name);
                }
            }
        }
    }

    // Also handle single audio file (for backward compatibility with recording)
    $audio_path = '';
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
        $tmp_name  = $_FILES['audio_file']['tmp_name'];
        $orig_name = basename($_FILES['audio_file']['name']);
        $mime      = mime_content_type($tmp_name);

        // Déterminer l'extension sécurisée à partir du MIME type
        $ext_map = [
            'audio/webm'     => 'webm',
            'audio/ogg'      => 'ogg', 
            'audio/mpeg'     => 'mp3',
            'audio/mp4'      => 'm4a',
            'audio/wav'      => 'wav',
            'audio/x-wav'    => 'wav',
            'audio/mp3'      => 'mp3',
            'audio/x-m4a'    => 'm4a',
        ];
        
        $safe_ext = $ext_map[$mime] ?? pathinfo($orig_name, PATHINFO_EXTENSION);
        
        // Validation de l'extension
        if (!in_array(strtolower($safe_ext), ['webm','ogg','mp3','m4a','wav','mp4'])) {
            // Essayer de détecter depuis le nom du fichier
            $safe_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            if (!in_array($safe_ext, ['webm','ogg','mp3','m4a','wav','mp4'])) {
                $safe_ext = 'webm'; // fallback par défaut
            }
        }

        $unique_name  = 'audio_' . uniqid() . '.' . $safe_ext;
        $destination  = $upload_dir . $unique_name;

        // Validation supplémentaire de la taille (max 50MB)
        $max_size = 50 * 1024 * 1024; // 50MB
        if ($_FILES['audio_file']['size'] > $max_size) {
            $_SESSION['error_msg'] = "Le fichier audio est trop volumineux. Taille maximale: 50MB.";
            header("Location: form.php");
            exit;
        }

        if (move_uploaded_file($tmp_name, $destination)) {
            $audio_path = 'uploads/' . $unique_name;
            
            // Log pour débogage
            error_log("Single audio uploaded: " . $unique_name . " (MIME: " . $mime . ", Size: " . $_FILES['audio_file']['size'] . " bytes)");
            error_log("Audio path stored in DB: " . $audio_path);
            error_log("Full destination path: " . $destination);
        } else {
            error_log("Failed to upload single audio file");
            $_SESSION['error_msg'] = "Erreur lors du téléchargement du fichier audio.";
            header("Location: form.php");
            exit;
        }
    }

    // Convert photos array to JSON for database storage
    $photos_json = json_encode($uploaded_photos);
    $audios_json = json_encode($uploaded_audios);

    // 3. Insert into Database (with check for missing columns)
    try {
        // Double check if 'book_about' column exists (safety since we are in dev/migration phase)
        $pdo->exec("ALTER TABLE submissions ADD COLUMN IF NOT EXISTS book_about TEXT AFTER subject");
        // Add column for multiple audio files
        $pdo->exec("ALTER TABLE submissions ADD COLUMN IF NOT EXISTS audio_paths TEXT AFTER audio_path");
    } catch (Exception $e) {
        // Column might already exist or DB driver doesn't support IF NOT EXISTS for ADD COLUMN
        // We log and continue
        error_log("Schema check: " . $e->getMessage());
    }

    $sql = "INSERT INTO submissions (
                user_id,
                full_name, phone, email, country, subject, book_about,
                birth_place, meeting_story, proudest_moment, hard_times, children_message, 
                photos_paths, audio_path, audio_paths
            ) VALUES (
                :user_id,
                :full_name, :phone, :email, :country, :subject, :book_about,
                :birth_place, :meeting_story, :proudest_moment, :hard_times, :children_message, 
                :photos_paths, :audio_path, :audio_paths
            )";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'    => $current_user_id,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':country' => $country,
            ':subject' => $subject,
            ':book_about' => $book_about,
            ':birth_place' => $birth_place,
            ':meeting_story' => $meeting_story,
            ':proudest_moment' => $proudest_moment,
            ':hard_times' => $hard_times,
            ':children_message' => $children_message,
            ':photos_paths' => $photos_json,
            ':audio_path' => $audio_path,
            ':audio_paths' => $audios_json
        ]);

        // Success
        $_SESSION['success_msg'] = "Merci ! Votre histoire a été enregistrée avec succès. Nous vous contacterons bientôt.";
        header("Location: form.php");
        exit;

    } catch (PDOException $e) {
        // Error
        $_SESSION['error_msg'] = "Une erreur est survenue lors de l'enregistrement : " . $e->getMessage();
        header("Location: form.php");
        exit;
    }

} else {
    // Not a POST request
    header("Location: form.php");
    exit;
}
?>
