<?php
session_start();
require_once '../database/db.php';

$current_user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize inputs (remplacement de FILTER_SANITIZE_STRING)
    $full_name        = htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone            = htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8');
    $email            = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $country          = htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES, 'UTF-8');
    $subject          = htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $birth_place      = htmlspecialchars($_POST['birth_place'] ?? '', ENT_QUOTES, 'UTF-8');
    $book_about       = htmlspecialchars($_POST['book_about'] ?? '', ENT_QUOTES, 'UTF-8');
    $meeting_story    = htmlspecialchars($_POST['meeting_story'] ?? '', ENT_QUOTES, 'UTF-8');
    $proudest_moment  = htmlspecialchars($_POST['proudest_moment'] ?? '', ENT_QUOTES, 'UTF-8');
    $hard_times       = htmlspecialchars($_POST['hard_times'] ?? '', ENT_QUOTES, 'UTF-8');
    $children_message = htmlspecialchars($_POST['children_message'] ?? '', ENT_QUOTES, 'UTF-8');

    if (empty($full_name) || empty($email)) {
        $_SESSION['error_msg'] = "Veuillez remplir les champs obligatoires (Nom et Email).";
        header("Location: form.php");
        exit;
    }

    // 2. Gestion des uploads
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_photos = [];
    $uploaded_audios = [];

    // --- Photos ---
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['name'] as $i => $name) {
            if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                $unique_name = uniqid() . '_' . basename($name);
                $destination = $upload_dir . $unique_name;
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $destination)) {
                    $uploaded_photos[] = 'uploads/' . $unique_name;
                }
            }
        }
    }

    // --- Audios ---
    if (!empty($_FILES['audio_files']['name'][0])) {
        foreach ($_FILES['audio_files']['name'] as $i => $orig_name) {
            if ($_FILES['audio_files']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['audio_files']['tmp_name'][$i];
                $size     = $_FILES['audio_files']['size'][$i];

                // Utilisation de finfo pour éviter l’erreur mime_content_type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                $ext_map = [
                    'audio/webm' => 'webm',
                    'audio/ogg'  => 'ogg',
                    'audio/mpeg' => 'mp3',
                    'audio/mp4'  => 'm4a',
                    'audio/wav'  => 'wav',
                ];
                $safe_ext = $ext_map[$mime] ?? pathinfo($orig_name, PATHINFO_EXTENSION);

                if ($size > 50 * 1024 * 1024) {
                    $_SESSION['error_msg'] = "Le fichier audio est trop volumineux (max 50MB).";
                    header("Location: form.php");
                    exit;
                }

                $unique_name = 'audio_' . uniqid() . '.' . $safe_ext;
                $destination = $upload_dir . $unique_name;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $uploaded_audios[] = 'uploads/' . $unique_name;
                }
            }
        }
    }

    $photos_json = json_encode($uploaded_photos);
    $audios_json = json_encode($uploaded_audios);

    // 3. Insertion en base
    $sql = "INSERT INTO submissions (
                user_id, full_name, phone, email, country, subject, book_about,
                birth_place, meeting_story, proudest_moment, hard_times, children_message,
                photos_paths, audio_path, audio_paths
            ) VALUES (
                :user_id, :full_name, :phone, :email, :country, :subject, :book_about,
                :birth_place, :meeting_story, :proudest_moment, :hard_times, :children_message,
                :photos_paths, '', :audio_paths
            )";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id'        => $current_user_id,
            ':full_name'      => $full_name,
            ':phone'          => $phone,
            ':email'          => $email,
            ':country'        => $country,
            ':subject'        => $subject,
            ':book_about'     => $book_about,
            ':birth_place'    => $birth_place,
            ':meeting_story'  => $meeting_story,
            ':proudest_moment'=> $proudest_moment,
            ':hard_times'     => $hard_times,
            ':children_message'=> $children_message,
            ':photos_paths'   => $photos_json,
            ':audio_paths'    => $audios_json
        ]);

        $_SESSION['success_msg'] = "Merci ! Votre histoire a été enregistrée avec succès.";
        header("Location: form.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header("Location: form.php");
        exit;
    }

} else {
    header("Location: form.php");
    exit;
}
?>
