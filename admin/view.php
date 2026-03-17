<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin/login.php");
    exit;
}

require_once '../database/db.php';

// Get submission ID from URL
$submission_id = $_GET['id'] ?? 0;

if ($submission_id == 0) {
    header("Location: admin_all_submissions.php");
    exit;
}

try {
    // Get submission details
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        header("Location: admin_all_submissions.php");
        exit;
    }
    
    // Debug: Afficher les données brutes
    error_log("Submission data: " . print_r($submission, true));
    
    // Get user info
    $stmt_user = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt_user->execute([$submission['email']]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    // Parse photos avec gestion d'erreur
    $photos = [];
    $photos_json = $submission['photos_paths'] ?? '';
    
    if (!empty($photos_json)) {
        $decoded = json_decode($photos_json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $photos = $decoded;
        } else {
            error_log("JSON decode error for photos: " . json_last_error_msg());
            error_log("Original photos_paths: " . $photos_json);
        }
    }
    
    $has_photos = is_array($photos) && count($photos) > 0;
    $has_audio = !empty($submission['audio_path']);
    $has_multiple_audios = !empty($submission['audio_paths']);
    
    // Debug: Afficher les résultats
    error_log("Photos count: " . count($photos));
    error_log("Has audio: " . ($has_audio ? 'YES' : 'NO'));
    error_log("Has multiple audios: " . ($has_multiple_audios ? 'YES' : 'NO'));
    error_log("Audio path: " . ($submission['audio_path'] ?? 'EMPTY'));
    error_log("Audio paths: " . ($submission['audio_paths'] ?? 'EMPTY'));
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la soumission - GriotBook</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel='stylesheet' href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap" type='text/css' media='all'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --griot-gold: #b5901f;
            --griot-gold-dark: #8b6f1a;
            --griot-gold-light: #c9a42d;
            --card-bg: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background-color: #f5f5f5;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .top-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--text-primary);
        }

        .back-btn {
            background: var(--griot-gold);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: var(--griot-gold-dark);
            transform: translateY(-1px);
        }

        .content-area {
            padding: 30px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, var(--griot-gold) 0%, var(--griot-gold-dark) 100%);
            color: white;
            padding: 18px 24px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-content {
            padding: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--griot-gold);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateX(2px);
        }

        .info-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 15px;
        }

        .content-section {
            margin-bottom: 25px;
        }

        .content-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--griot-gold);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-text {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--griot-gold);
            white-space: pre-wrap;
            line-height: 1.8;
            font-size: 15px;
        }

        /* Styles pour les fichiers */
        .files-section {
            margin-top: 25px;
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .file-item {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .file-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: var(--griot-gold);
        }

        .file-item.photo-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .file-icon {
            font-size: 48px;
            color: var(--griot-gold);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .file-item:hover .file-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .file-name {
            font-size: 16px;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-weight: 600;
            word-break: break-word;
            line-height: 1.4;
        }

        .file-size {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .file-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 15px;
        }

        .action-btn {
            background: var(--griot-gold);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .action-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .action-btn:hover {
            background: var(--griot-gold-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(181, 144, 31, 0.4);
        }

        .action-btn i {
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--griot-gold), var(--griot-gold-dark));
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #d32f2f);
        }

        /* Audio player intégré */
        .audio-player-inline {
            width: 100%;
            margin: 20px 0;
            border-radius: 12px;
            background: #f8f9fa;
            padding: 15px;
        }

        .audio-player-inline audio {
            width: 100%;
            height: 40px;
            border-radius: 8px;
        }

        /* Photo preview */
        .photo-preview {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            object-fit: cover;
            margin: 15px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .photo-preview:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* Modal pour l'audio */
        .audio-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .audio-modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 700px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .audio-modal h3 {
            margin-bottom: 30px;
            color: var(--text-primary);
            font-family: 'Playfair Display', serif;
            font-size: 24px;
        }

        .audio-player {
            width: 100%;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Modal pour les images */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .image-modal-content {
            max-width: 95%;
            max-height: 95%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-modal img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            display: block;
        }

        .image-modal-controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-success {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 25px;
            opacity: 0.3;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .file-actions {
                flex-direction: column;
            }
            
            .content-area {
                padding: 20px 15px;
            }
            
            .top-header {
                padding: 15px 20px;
            }
            
            .page-title {
                font-size: 22px;
            }
        }
    </style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Détails de la soumission</h1>
                <a href="admin_all_submissions.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Retour aux soumissions
                </a>
            </header>

            <div class="content-area">
                <?php if(isset($error)): ?>
                    <div style="color: var(--danger-color); padding: 15px; background: #ffebee; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--danger-color);">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="content-grid">
                    <!-- Informations de base -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-user"></i> Informations de l'utilisateur
                        </div>
                        <div class="card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Nom</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['full_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['email']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Téléphone</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['phone'] ?: '-'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Pays</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['country'] ?: '-'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">À propos de qui est ce livre ?</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['book_about'] ?: '-'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Date</div>
                                    <div class="info-value"><?php echo (new DateTime($submission['created_at']))->format('d/m/Y H:i'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de la soumission -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-book"></i> Détails de la soumission
                        </div>
                        <div class="card-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Où êtes-vous né(e) ?</div>
                                    <div class="info-value"><?php echo htmlspecialchars($submission['birth_place'] ?: '-'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Statut</div>
                                    <div class="info-value">
                                        <span class="badge badge-success">Reçue</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu de l'histoire -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-pen"></i> L'histoire
                    </div>
                    <div class="card-content">
                        <div class="content-section">
                            <div class="content-title">Comment avez-vous rencontré votre partenaire ?</div>
                            <div class="content-text"><?php echo nl2br(htmlspecialchars($submission['meeting_story'] ?: 'Non spécifié')); ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($submission['proudest_moment'])): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-trophy"></i> Moment le plus fier
                    </div>
                    <div class="card-content">
                        <div class="content-section">
                            <div class="content-title">Votre plus grande fierté ?</div>
                            <div class="content-text"><?php echo nl2br(htmlspecialchars($submission['proudest_moment'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($submission['hard_times'])): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-cloud"></i> Épreuves marquantes
                    </div>
                    <div class="card-content">
                        <div class="content-section">
                            <div class="content-title">Une épreuve marquante ?</div>
                            <div class="content-text"><?php echo nl2br(htmlspecialchars($submission['hard_times'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($submission['children_message'])): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-child"></i> Message aux enfants
                    </div>
                    <div class="card-content">
                        <div class="content-section">
                            <div class="content-title">Quel message pour vos enfants ?</div>
                            <div class="content-text"><?php echo nl2br(htmlspecialchars($submission['children_message'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- SECTION PHOTOS -->
                <div class="card" style="margin-bottom: 25px;">
                    <div class="card-header">
                        <i class="fas fa-camera"></i> Photos envoyées
                        <span style="margin-left: auto; font-size: 14px; opacity: 0.8;">
                            <?php echo count($photos); ?> photo(s)
                        </span>
                    </div>
                    <div class="card-content">
                        <?php if ($has_photos): ?>
                            <div class="photos-grid">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="file-item photo-item">
                                        <div class="file-size">
                                            <?php 
                                                $full_path = '../' . $photo;
                                                if (file_exists($full_path)) {
                                                    echo round(filesize($full_path) / 1024, 2) . ' KB';
                                                    echo '<br><span style="color: green; font-size: 11px;">✓ Fichier trouvé</span>';
                                                } else {
                                                    echo 'N/A<br><span style="color: red; font-size: 11px;">✗ Fichier manquant</span>';
                                                }
                                            ?>
                                        </div>
                                        
                                        <!-- Photo preview -->
                                        <?php if (file_exists('../' . $photo)): ?>
                                            <img src="../<?php echo htmlspecialchars($photo); ?>" 
                                                 class="photo-preview" 
                                                 alt="Photo <?php echo $index + 1; ?>"
                                                 onclick="openImageModal('<?php echo htmlspecialchars($photo); ?>')"
                                                 style="cursor: pointer;">
                                            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                                <i class="fas fa-mouse-pointer"></i> Cliquez pour agrandir
                                            </div>
                                        <?php else: ?>
                                            <div style="background: #f8f9fa; height: 150px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">
                                                <div>
                                                    <i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 10px;"></i>
                                                    <div style="font-size: 12px;">Image non disponible</div>
                                                    <div style="font-size: 10px; color: #666;">Chemin: ../<?php echo htmlspecialchars($photo); ?></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="file-actions">
                                            <?php if (file_exists('../' . $photo)): ?>
                                                <button class="action-btn btn-primary" onclick="openImageModal('<?php echo htmlspecialchars($photo); ?>')">
                                                    <i class="fas fa-eye"></i> Visualiser
                                                </button>
                                            <?php endif; ?>
                                            <a href="../<?php echo htmlspecialchars($photo); ?>" download="<?php echo htmlspecialchars(basename($photo)); ?>" class="action-btn btn-secondary">
                                                <i class="fas fa-download"></i> Télécharger
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-camera" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                                <h4 style="margin-bottom: 10px; color: #666;">Aucune photo envoyée</h4>
                                <p style="color: #999; font-size: 14px;">Cette soumission ne contient aucune photo.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SECTION AUDIO -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-microphone"></i> Fichiers audio
                        <span style="margin-left: auto; font-size: 14px; opacity: 0.8;">
                            <?php echo $has_audio ? '1 fichier' : '0 fichier'; ?>
                        </span>
                    </div>
                    <div class="card-content">
                        <div class="content-section">
                            <h3><i class="fas fa-microphone"></i> Enregistrement audio</h3>
                            <div class="audio-content">
                                <?php if ($has_audio || $has_multiple_audios): ?>
                                    <!-- Audio unique -->
                                    <?php if ($has_audio): ?>
                                        <div class="audio-item">
                                            <div class="audio-info">
                                                <h4>Enregistrement principal</h4>
                                                <div class="file-size">
                                                    <?php 
                                                        // Correction : utiliser le chemin relatif correct
                                                        $audio_file = $submission['audio_path']; // Ex: "uploads/audio_uniqid.mp3"
                                                        $full_path = '../' . $audio_file;
                                                        
                                                        if (file_exists($full_path)) {
                                                            echo 'Fichier trouvé<br><span style="color: green; font-size: 11px;">✓ Disponible</span>';
                                                            echo '<br><span style="color: #666; font-size: 10px;">Chemin: ' . htmlspecialchars($audio_file) . '</span>';
                                                            
                                                            // Détecter le type MIME pour la balise audio
                                                            $mime_type = mime_content_type($full_path);
                                                            $audio_type = '';
                                                            switch($mime_type) {
                                                                case 'audio/webm': $audio_type = 'audio/webm'; break;
                                                                case 'audio/ogg': $audio_type = 'audio/ogg'; break;
                                                                case 'audio/mpeg': $audio_type = 'audio/mpeg'; break;
                                                                case 'audio/mp4': $audio_type = 'audio/mp4'; break;
                                                                case 'audio/wav': $audio_type = 'audio/wav'; break;
                                                                default: $audio_type = 'audio/webm'; // fallback
                                                            }
                                                        } else {
                                                            echo 'N/A<br><span style="color: red; font-size: 11px;">✗ Fichier manquant</span>';
                                                            echo '<br><span style="color: #666; font-size: 10px;">Recherche: ' . htmlspecialchars($full_path) . '</span>';
                                                        }
                                                    ?>
                                                </div>
                                                
                                                <!-- Audio player intégré avec la bonne procédure -->
                                                <?php if (file_exists($full_path)): ?>
                                                    <div class="audio-player-inline" style="margin: 15px 0;">
                                                        <audio controls style="width: 100%;" preload="metadata">
                                                            <source src="../<?php echo htmlspecialchars($audio_file); ?>" type="<?php echo $audio_type; ?>">
                                                            Votre navigateur ne supporte pas l'élément audio.
                                                        </audio>
                                                    </div>
                                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                                        <i class="fas fa-play-circle"></i> Lecteur audio intégré
                                                        <br><small>Type: <?php echo $mime_type; ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <div style="background: #fff8e1; padding: 20px; border-radius: 8px; text-align: center; border: 2px dashed #ffc107;">
                                                        <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: #ffc107; margin-bottom: 10px;"></i>
                                                        <div style="font-size: 14px; color: #856404;">Audio non disponible</div>
                                                        <div style="font-size: 11px; color: #666;">Chemin recherché: <?php echo htmlspecialchars($full_path); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="file-actions">
                                                    <?php if (file_exists($full_path)): ?>
                                                        <button class="action-btn btn-primary" onclick="openAudioModal('<?php echo htmlspecialchars($audio_file); ?>')">
                                                            <i class="fas fa-play"></i> Lecture complète
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="../<?php echo htmlspecialchars($audio_file); ?>" download="<?php echo htmlspecialchars(basename($audio_file)); ?>" class="action-btn btn-secondary">
                                                        <i class="fas fa-download"></i> Télécharger
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Fichiers audio multiples -->
                                    <?php if ($has_multiple_audios): ?>
                                        <?php 
                                            $audio_paths = json_decode($submission['audio_paths'] ?? '[]', true);
                                            if (is_array($audio_paths) && count($audio_paths) > 0):
                                        ?>
                                            <div class="multiple-audios" style="margin-top: 20px;">
                                                <h4 style="margin-bottom: 15px; color: #333;">
                                                    <i class="fas fa-music"></i> Fichiers audio supplémentaires (<?php echo count($audio_paths); ?>)
                                                </h4>
                                                <?php foreach ($audio_paths as $index => $audio_path): ?>
                                                    <?php 
                                                        $full_path = '../' . $audio_path;
                                                        $filename = basename($audio_path);
                                                        $file_exists = file_exists($full_path);
                                                        
                                                        if ($file_exists) {
                                                            $mime_type = mime_content_type($full_path);
                                                            $audio_type = '';
                                                            switch($mime_type) {
                                                                case 'audio/webm': $audio_type = 'audio/webm'; break;
                                                                case 'audio/ogg': $audio_type = 'audio/ogg'; break;
                                                                case 'audio/mpeg': $audio_type = 'audio/mpeg'; break;
                                                                case 'audio/mp4': $audio_type = 'audio/mp4'; break;
                                                                case 'audio/wav': $audio_type = 'audio/wav'; break;
                                                                default: $audio_type = 'audio/webm'; // fallback
                                                            }
                                                        }
                                                    ?>
                                                    <div class="audio-item" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e9ecef;">
                                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                                <i class="fas fa-music" style="color: #666;"></i>
                                                                <span style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($filename); ?></span>
                                                                <?php if ($file_exists): ?>
                                                                    <span style="color: green; font-size: 12px;">✓ Disponible</span>
                                                                <?php else: ?>
                                                                    <span style="color: red; font-size: 12px;">✗ Manquant</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div style="display: flex; gap: 8px;">
                                                                <?php if ($file_exists): ?>
                                                                    <button class="action-btn btn-primary" onclick="openAudioModal('<?php echo htmlspecialchars($audio_path); ?>')" style="padding: 8px 12px; font-size: 12px;">
                                                                        <i class="fas fa-play"></i> Écouter
                                                                    </button>
                                                                <?php endif; ?>
                                                                <a href="../<?php echo htmlspecialchars($audio_path); ?>" download="<?php echo htmlspecialchars($filename); ?>" class="action-btn btn-secondary" style="padding: 8px 12px; font-size: 12px;">
                                                                    <i class="fas fa-download"></i> Télécharger
                                                                </a>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($file_exists): ?>
                                                            <audio controls style="width: 100%; margin-top: 10px;" preload="metadata">
                                                                <source src="../<?php echo htmlspecialchars($audio_path); ?>" type="<?php echo $audio_type; ?>">
                                                                Votre navigateur ne supporte pas l'élément audio.
                                                            </audio>
                                                            <div style="font-size: 11px; color: #666; margin-top: 5px;">
                                                                Type: <?php echo $mime_type; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div style="background: #fff8e1; padding: 10px; border-radius: 4px; text-align: center; border: 1px dashed #ffc107; margin-top: 10px;">
                                                                <div style="font-size: 12px; color: #856404;">Fichier non trouvé</div>
                                                                <div style="font-size: 10px; color: #666;">Chemin: <?php echo htmlspecialchars($full_path); ?></div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-microphone" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                                        <h4 style="margin-bottom: 10px; color: #666;">Aucun fichier audio</h4>
                                        <p style="color: #999; font-size: 14px;">Cette soumission ne contient aucun enregistrement audio.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions simplifiées -->
                <div style="text-align: center; margin-top: 30px;">
                    <a href="admin_all_submissions.php" class="action-btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                    <button class="action-btn btn-danger" onclick="deleteSubmission()">
                        <i class="fas fa-trash"></i> Supprimer cette soumission
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour l'audio -->
    <div id="audioModal" class="audio-modal">
        <div class="audio-modal-content">
            <h3><i class="fas fa-music"></i> Lecture audio</h3>
            <audio id="audioPlayer" controls class="audio-player">
                Votre navigateur ne supporte pas l'élément audio.
            </audio>
            <div class="file-actions" style="justify-content: center; margin-top: 20px;">
                <button class="action-btn btn-danger" onclick="closeAudioModal()">
                    <i class="fas fa-times"></i> Fermer
                </button>
                <a id="audioDownloadLink" download class="action-btn btn-secondary">
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
        </div>
    </div>

    <!-- Modal pour les images -->
    <div id="imageModal" class="image-modal">
        <div class="image-modal-content">
            <img id="imageViewer" style="max-width: 100%; max-height: 90vh; object-fit: contain;">
            <div class="image-modal-controls">
                <button class="action-btn btn-danger" onclick="closeImageModal()">
                    <i class="fas fa-times"></i> Fermer
                </button>
                <a id="imageDownloadLink" download class="action-btn btn-secondary">
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function openAudioModal(audioPath) {
            const modal = document.getElementById('audioModal');
            const player = document.getElementById('audioPlayer');
            const downloadLink = document.getElementById('audioDownloadLink');
            
            // Construire le chemin correct depuis le dossier admin
            const fullPath = audioPath.startsWith('uploads/') ? '../' + audioPath : audioPath;
            
            // Définir la source audio avec tous les formats possibles
            const audioElement = document.createElement('audio');
            audioElement.innerHTML = `
                <source src="${fullPath}" type="audio/mpeg">
                <source src="${fullPath}" type="audio/wav">
                <source src="${fullPath}" type="audio/ogg">
                <source src="${fullPath}" type="audio/webm">
                <source src="${fullPath}" type="audio/mp4">
            `;
            
            player.innerHTML = audioElement.innerHTML;
            player.src = fullPath;
            
            // Configurer le lien de téléchargement
            downloadLink.href = fullPath;
            downloadLink.download = audioPath.split('/').pop();
            
            // Afficher la modal
            modal.style.display = 'flex';
            
            // Démarrer la lecture automatiquement
            player.play().catch(function(error) {
                console.log('Auto-play prevented:', error);
            });
        }

        function closeAudioModal() {
            const modal = document.getElementById('audioModal');
            const player = document.getElementById('audioPlayer');
            
            // Arrêter la lecture
            player.pause();
            player.currentTime = 0;
            
            // Cacher la modal
            modal.style.display = 'none';
        }

        function openImageModal(imagePath) {
            const modal = document.getElementById('imageModal');
            const viewer = document.getElementById('imageViewer');
            const downloadLink = document.getElementById('imageDownloadLink');
            
            // Construire le chemin correct depuis le dossier admin
            const fullPath = imagePath.startsWith('uploads/') ? '../' + imagePath : imagePath;
            
            // Définir la source de l'image
            viewer.src = fullPath;
            
            // Configurer le lien de téléchargement
            downloadLink.href = fullPath;
            downloadLink.download = imagePath.split('/').pop();
            
            // Afficher la modal
            modal.style.display = 'flex';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            const viewer = document.getElementById('imageViewer');
            
            // Cacher la modal
            modal.style.display = 'none';
            
            // Nettoyer la source
            viewer.src = '';
        }

        // Fermer les modals en cliquant à l'extérieur
        document.getElementById('audioModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAudioModal();
            }
        });

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Fermer avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAudioModal();
                closeImageModal();
            }
        });

        // Navigation au clavier pour les modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && document.getElementById('imageModal').style.display === 'flex') {
                // Navigation entre images si plusieurs
                e.preventDefault();
            }
            if (e.key === 'ArrowRight' && document.getElementById('imageModal').style.display === 'flex') {
                // Navigation entre images si plusieurs
                e.preventDefault();
            }
        });

        function deleteSubmission() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette soumission ? Cette action est irréversible.')) {
                window.location.href = 'admin_delete_submission.php?id=<?php echo $submission_id; ?>';
            }
        }
    </script>
</body>
</html>
