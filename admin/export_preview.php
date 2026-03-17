<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../database/db.php';

// Get filter parameters (same as admin_all_submissions.php)
$filter_name = $_GET['filter_name'] ?? '';
$filter_email = $_GET['filter_email'] ?? '';
$filter_country = $_GET['filter_country'] ?? '';
$filter_birth_place = $_GET['filter_birth_place'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];

if (!empty($filter_name)) {
    $where_conditions[] = "full_name LIKE :filter_name";
    $params[':filter_name'] = '%' . $filter_name . '%';
}

if (!empty($filter_email)) {
    $where_conditions[] = "email LIKE :filter_email";
    $params[':filter_email'] = '%' . $filter_email . '%';
}

if (!empty($filter_country)) {
    $where_conditions[] = "country LIKE :filter_country";
    $params[':filter_country'] = '%' . $filter_country . '%';
}

if (!empty($filter_birth_place)) {
    $where_conditions[] = "birth_place LIKE :filter_birth_place";
    $params[':filter_birth_place'] = '%' . $filter_birth_place . '%';
}

if (!empty($filter_date)) {
    $where_conditions[] = "DATE(created_at) = :filter_date";
    $params[':filter_date'] = $filter_date;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get all filtered submissions
try {
    $sql = "SELECT * FROM submissions $where_clause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    
    // Bind filter parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Clean text function
function cleanText($text) {
    if (empty($text)) return '';
    
    // Remove HTML tags
    $text = strip_tags($text);
    
    // Decode HTML entities
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Remove newlines and replace with spaces
    $text = str_replace(["\r\n", "\r", "\n"], " ", $text);
    
    // Remove multiple spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Clean extra spaces
    $text = trim($text);
    
    return $text;
}

// Calculate statistics
$totalPhotos = 0;
$totalMainAudios = 0;
$totalMultipleAudios = 0;
$submissionsWithPhotos = 0;
$submissionsWithMainAudio = 0;
$submissionsWithMultipleAudios = 0;

foreach ($submissions as $submission) {
    $photos = json_decode($submission['photos_paths'] ?? '[]', true);
    $multiple_audios = json_decode($submission['audio_paths'] ?? '[]', true);
    
    $photoCount = is_array($photos) ? count($photos) : 0;
    $multipleAudioCount = is_array($multiple_audios) ? count($multiple_audios) : 0;
    
    $totalPhotos += $photoCount;
    $totalMultipleAudios += $multipleAudioCount;
    
    if ($photoCount > 0) $submissionsWithPhotos++;
    if (!empty($submission['audio_path'])) $totalMainAudios++;
    if ($multipleAudioCount > 0) $submissionsWithMultipleAudios++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu Export Excel - GriotBook</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #B5901F 0%, #8b6f1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .stats-section {
            background: #f8f9fa;
            padding: 25px;
            border-bottom: 2px solid #e9ecef;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #B5901F;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #B5901F;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .filters-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }

        .table-container {
            padding: 25px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #B5901F;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
            border: 1px solid #8b6f1a;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #e9ecef;
            vertical-align: top;
            max-width: 200px;
            word-wrap: break-word;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #e3f2fd;
        }

        .number-cell {
            text-align: center;
            font-weight: bold;
            color: #B5901F;
        }

        .yes-cell {
            text-align: center;
            color: #28a745;
            font-weight: bold;
        }

        .no-cell {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
        }

        .actions {
            padding: 25px;
            background: #f8f9fa;
            text-align: center;
            border-top: 2px solid #e9ecef;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-excel {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
        }

        .btn-excel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .export-info {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                padding: 10px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 4px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-table"></i> Aperçu Export Excel</h1>
            <p>GriotBook - Export des données de soumissions</p>
        </div>

        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($submissions); ?></div>
                    <div class="stat-label">Total des soumissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalPhotos; ?></div>
                    <div class="stat-label">Total des photos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalMainAudios; ?></div>
                    <div class="stat-label">Audio principal</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalMultipleAudios; ?></div>
                    <div class="stat-label">Audios multiples</div>
                </div>
            </div>

            <?php if (!empty($filter_name) || !empty($filter_email) || !empty($filter_country) || !empty($filter_birth_place) || !empty($filter_date)): ?>
                <div class="filters-info">
                    <strong><i class="fas fa-filter"></i> Filtres appliqués :</strong>
                    <?php if (!empty($filter_name)): ?> Nom: "<?php echo htmlspecialchars($filter_name); ?>" <?php endif; ?>
                    <?php if (!empty($filter_email)): ?> Email: "<?php echo htmlspecialchars($filter_email); ?>" <?php endif; ?>
                    <?php if (!empty($filter_country)): ?> Pays: "<?php echo htmlspecialchars($filter_country); ?>" <?php endif; ?>
                    <?php if (!empty($filter_birth_place)): ?> Lieu: "<?php echo htmlspecialchars($filter_birth_place); ?>" <?php endif; ?>
                    <?php if (!empty($filter_date)): ?> Date: <?php echo htmlspecialchars($filter_date); ?> <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date de soumission</th>
                        <th>Nom complet</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Pays</th>
                        <th>Lieu de naissance</th>
                        <th>Sujet</th>
                        <th>À propos du livre</th>
                        <th>Histoire rencontre</th>
                        <th>Plus grande fierté</th>
                        <th>Épreuve marquante</th>
                        <th>Message aux enfants</th>
                        <th>Nombre de photos</th>
                        <th>Audio principal</th>
                        <th>Nombre d'audios multiples</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <?php 
                            $photos = json_decode($submission['photos_paths'] ?? '[]', true);
                            $multiple_audios = json_decode($submission['audio_paths'] ?? '[]', true);
                            
                            $photoCount = is_array($photos) ? count($photos) : 0;
                            $multipleAudioCount = is_array($multiple_audios) ? count($multiple_audios) : 0;
                            $hasMainAudio = !empty($submission['audio_path']) ? 'Oui' : 'Non';
                        ?>
                        <tr>
                            <td><?php echo $submission['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($submission['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['full_name'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['phone'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['email'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['country'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['birth_place'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['subject'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['book_about'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['meeting_story'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['proudest_moment'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['hard_times'])); ?></td>
                            <td><?php echo htmlspecialchars(cleanText($submission['children_message'])); ?></td>
                            <td class="number-cell"><?php echo $photoCount; ?></td>
                            <td class="<?php echo $hasMainAudio === 'Oui' ? 'yes-cell' : 'no-cell'; ?>"><?php echo $hasMainAudio; ?></td>
                            <td class="number-cell"><?php echo $multipleAudioCount; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <a href="export_to_excel.php?<?php echo http_build_query(array_filter([
                'filter_name' => $filter_name,
                'filter_email' => $filter_email,
                'filter_country' => $filter_country,
                'filter_birth_place' => $filter_birth_place,
                'filter_date' => $filter_date
            ])); ?>" class="btn btn-excel">
                <i class="fas fa-file-excel"></i> Télécharger en Excel
            </a>
            <a href="admin_all_submissions.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Retour aux soumissions
            </a>
            
            <div class="export-info">
                <strong><i class="fas fa-info-circle"></i> Information d'export :</strong><br>
                Exporté le <?php echo date('d/m/Y H:i:s'); ?> par <?php echo $_SESSION['user_name'] ?? 'Admin'; ?><br>
                Fichier : GriotBook_Export_Soumissions_<?php echo date('Y-m-d_H-i-s'); ?>.xlsx
            </div>
        </div>
    </div>
</body>
</html>
