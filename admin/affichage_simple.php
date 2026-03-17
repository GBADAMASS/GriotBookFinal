<?php
require_once '../database/db.php';

// Récupérer toutes les soumissions
try {
    $stmt = $pdo->query("SELECT * FROM submissions ORDER BY created_at DESC");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affichage des soumissions - GriotBook</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --griot-gold: #b5901f;
            --griot-gold-dark: #8b6f1a;
            --text-primary: #333333;
            --text-secondary: #666666;
            --border-color: #e0e0e0;
            --success-color: #4caf50;
            --danger-color: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e9ecef 100%);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: var(--griot-gold);
            margin-bottom: 10px;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 16px;
        }

        .nav-links {
            text-align: center;
            margin-bottom: 30px;
        }

        .nav-links a {
            display: inline-block;
            background: var(--griot-gold);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: var(--griot-gold-dark);
            transform: translateY(-2px);
        }

        .submission-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .submission-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .submission-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text-primary);
        }

        .submission-date {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .submission-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 500;
        }

        .submission-content {
            margin-bottom: 20px;
        }

        .content-section {
            margin-bottom: 15px;
        }

        .content-label {
            font-weight: 600;
            color: var(--griot-gold);
            margin-bottom: 5px;
        }

        .content-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--griot-gold);
            white-space: pre-wrap;
        }

        .files-section {
            margin-top: 20px;
        }

        .files-title {
            font-weight: 600;
            color: var(--griot-gold);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .file-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .file-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .file-icon {
            font-size: 32px;
            color: var(--griot-gold);
            margin-bottom: 10px;
        }

        .file-name {
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .file-size {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .file-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-btn {
            background: var(--griot-gold);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: var(--griot-gold-dark);
            transform: translateY(-1px);
        }

        .audio-player {
            width: 100%;
            margin: 10px 0;
        }

        .photo-preview {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin: 10px 0;
            cursor: pointer;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            font-size: 12px;
        }

        .debug-info h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .debug-info pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 11px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .submission-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .files-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Soumissions GriotBook</h1>
            <p>Affichage complet des fichiers envoyés</p>
        </div>

        <div class="nav-links">
            <a href="form_simple.php">
                <i class="fas fa-plus"></i> Nouvelle soumission
            </a>
            <a href="admin/view.php">
                <i class="fas fa-cog"></i> Admin Dashboard
            </a>
        </div>

        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Aucune soumission</h3>
                <p>Il n'y a aucune soumission pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($submissions as $submission): ?>
                <div class="submission-card">
                    <div class="submission-header">
                        <div>
                            <div class="submission-title"><?php echo htmlspecialchars($submission['full_name']); ?></div>
                            <div class="submission-date">
                                <?php echo (new DateTime($submission['created_at']))->format('d/m/Y H:i'); ?>
                            </div>
                        </div>
                        <div>
                            <span style="background: var(--griot-gold); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                ID: <?php echo $submission['id']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="submission-info">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($submission['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Téléphone</div>
                            <div class="info-value"><?php echo htmlspecialchars($submission['phone'] ?: '-'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Sujet</div>
                            <div class="info-value"><?php echo htmlspecialchars($submission['subject'] ?: '-'); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($submission['meeting_story'])): ?>
                    <div class="submission-content">
                        <div class="content-section">
                            <div class="content-label">Histoire</div>
                            <div class="content-text"><?php echo nl2br(htmlspecialchars($submission['meeting_story'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Debug info -->
                    <div class="debug-info">
                        <h4><i class="fas fa-bug"></i> Debug Information</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div><strong>Photos JSON:</strong></div>
                            <div><pre><?php echo htmlspecialchars($submission['photos_paths'] ?? 'NULL'); ?></pre></div>
                            <div><strong>Audio Path:</strong></div>
                            <div><pre><?php echo htmlspecialchars($submission['audio_path'] ?? 'NULL'); ?></pre></div>
                        </div>
                    </div>

                    <!-- Fichiers -->
                    <?php
                    $photos = json_decode($submission['photos_paths'], true) ?: [];
                    $hasPhotos = is_array($photos) && count($photos) > 0;
                    $hasAudio = !empty($submission['audio_path']);
                    ?>

                    <?php if ($hasPhotos || $hasAudio): ?>
                    <div class="files-section">
                        <div class="files-title">
                            <i class="fas fa-paperclip"></i> Fichiers joints
                            <span style="margin-left: auto; font-size: 14px; opacity: 0.8;">
                                <?php echo count($photos) + ($hasAudio ? 1 : 0); ?> fichier(s)
                            </span>
                        </div>
                        
                        <div class="files-grid">
                            <?php if ($hasPhotos): ?>
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <div class="file-name">Photo #<?php echo $index + 1; ?></div>
                                        <div class="file-size">
                                            <?php 
                                                if (file_exists($photo)) {
                                                    echo round(filesize($photo) / 1024, 2) . ' KB';
                                                    echo '<br><span style="color: green; font-size: 10px;">✓ Fichier trouvé</span>';
                                                } else {
                                                    echo 'N/A<br><span style="color: red; font-size: 10px;">✗ Manquant</span>';
                                                }
                                            ?>
                                        </div>
                                        
                                        <?php if (file_exists($photo)): ?>
                                            <img src="<?php echo htmlspecialchars($photo); ?>" 
                                                 class="photo-preview" 
                                                 alt="Photo <?php echo $index + 1; ?>"
                                                 onclick="window.open(this.src, '_blank')">
                                            <audio controls class="audio-player" style="display: none;">
                                                <source src="<?php echo htmlspecialchars($photo); ?>" type="audio/mpeg">
                                            </audio>
                                        <?php endif; ?>
                                        
                                        <div class="file-actions">
                                            <?php if (file_exists($photo)): ?>
                                                <button class="action-btn" onclick="window.open('<?php echo htmlspecialchars($photo); ?>', '_blank')">
                                                    <i class="fas fa-eye"></i> Voir
                                                </button>
                                            <?php endif; ?>
                                            <a href="<?php echo htmlspecialchars($photo); ?>" download class="action-btn">
                                                <i class="fas fa-download"></i> Télécharger
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($hasAudio): ?>
                                <div class="file-item">
                                    <div class="file-icon">
                                        <i class="fas fa-music"></i>
                                    </div>
                                    <div class="file-name">Audio</div>
                                    <div class="file-size">
                                        <?php 
                                            if (file_exists($submission['audio_path'])) {
                                                echo round(filesize($submission['audio_path']) / 1024, 2) . ' KB';
                                                echo '<br><span style="color: green; font-size: 10px;">✓ Fichier trouvé</span>';
                                            } else {
                                                echo 'N/A<br><span style="color: red; font-size: 10px;">✗ Manquant</span>';
                                            }
                                        ?>
                                    </div>
                                    
                                    <?php if (file_exists($submission['audio_path'])): ?>
                                        <audio controls class="audio-player">
                                            <source src="<?php echo htmlspecialchars($submission['audio_path']); ?>" type="audio/mpeg">
                                            <source src="<?php echo htmlspecialchars($submission['audio_path']); ?>" type="audio/wav">
                                            <source src="<?php echo htmlspecialchars($submission['audio_path']); ?>" type="audio/ogg">
                                            <source src="<?php echo htmlspecialchars($submission['audio_path']); ?>" type="audio/webm">
                                            Votre navigateur ne supporte pas l'élément audio.
                                        </audio>
                                    <?php endif; ?>
                                    
                                    <div class="file-actions">
                                        <a href="<?php echo htmlspecialchars($submission['audio_path']); ?>" download class="action-btn">
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Fonction pour ouvrir les images en plein écran
        function openImage(src) {
            window.open(src, '_blank');
        }
    </script>
</body>
</html>
