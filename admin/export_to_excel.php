<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../database/db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

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

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('GriotBook Admin')
    ->setLastModifiedBy('GriotBook Admin')
    ->setTitle('Export des Soumissions GriotBook')
    ->setSubject('Export des données de soumissions')
    ->setDescription('Export des soumissions avec filtres appliqués');

// Define styles
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B5901F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
];

$dataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_TOP],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
    'font' => ['size' => 11]
];

$numberStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'B5901F']]
];

$yesStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '28a745']]
];

$noStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'dc3545']]
];

// Set column headers
$headers = [
    'A' => 'ID',
    'B' => 'Date de soumission',
    'C' => 'Nom complet',
    'D' => 'Téléphone',
    'E' => 'Email',
    'F' => 'Pays',
    'G' => 'Lieu de naissance',
    'H' => 'Sujet',
    'I' => 'À propos du livre',
    'J' => 'Histoire rencontre',
    'K' => 'Plus grande fierté',
    'L' => 'Épreuve marquante',
    'M' => 'Message aux enfants',
    'N' => 'Nombre de photos',
    'O' => 'Audio principal',
    'P' => 'Nombre d\'audios multiples'
];

// Apply headers and styling
foreach ($headers as $column => $header) {
    $sheet->setCellValue($column . '1', $header);
}

// Apply header style to all header cells
$sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

// Set column widths
$columnWidths = [
    'A' => 8,   // ID
    'B' => 20,  // Date
    'C' => 25,  // Nom
    'D' => 15,  // Téléphone
    'E' => 25,  // Email
    'F' => 15,  // Pays
    'G' => 20,  // Lieu
    'H' => 20,  // Sujet
    'I' => 25,  // Livre
    'J' => 30,  // Histoire rencontre
    'K' => 30,  // Fierté
    'L' => 30,  // Épreuve
    'M' => 30,  // Message enfants
    'N' => 15,  // Nombre de photos
    'O' => 15,  // Audio principal
    'P' => 20   // Nombre d'audios multiples
];

foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Add data rows
$row = 2;
foreach ($submissions as $submission) {
    // Decode JSON arrays
    $photos = json_decode($submission['photos_paths'] ?? '[]', true);
    $multiple_audios = json_decode($submission['audio_paths'] ?? '[]', true);
    
    // Count photos and audios
    $photoCount = is_array($photos) ? count($photos) : 0;
    $multipleAudioCount = is_array($multiple_audios) ? count($multiple_audios) : 0;
    
    // Check if has main audio
    $hasMainAudio = !empty($submission['audio_path']) ? 'Oui' : 'Non';
    
    // Add row data
    $sheet->setCellValue('A' . $row, $submission['id']);
    $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($submission['created_at'])));
    $sheet->setCellValue('C' . $row, cleanText($submission['full_name']));
    $sheet->setCellValue('D' . $row, cleanText($submission['phone']));
    $sheet->setCellValue('E' . $row, cleanText($submission['email']));
    $sheet->setCellValue('F' . $row, cleanText($submission['country']));
    $sheet->setCellValue('G' . $row, cleanText($submission['birth_place']));
    $sheet->setCellValue('H' . $row, cleanText($submission['subject']));
    $sheet->setCellValue('I' . $row, cleanText($submission['book_about']));
    $sheet->setCellValue('J' . $row, cleanText($submission['meeting_story']));
    $sheet->setCellValue('K' . $row, cleanText($submission['proudest_moment']));
    $sheet->setCellValue('L' . $row, cleanText($submission['hard_times']));
    $sheet->setCellValue('M' . $row, cleanText($submission['children_message']));
    $sheet->setCellValue('N' . $row, $photoCount);
    $sheet->setCellValue('O' . $row, $hasMainAudio);
    $sheet->setCellValue('P' . $row, $multipleAudioCount);
    
    // Apply styles to the row
    $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($dataStyle);
    $sheet->getStyle('N' . $row . ':P' . $row)->applyFromArray($numberStyle);
    
    // Apply specific style for audio column
    if ($hasMainAudio === 'Oui') {
        $sheet->getStyle('O' . $row)->applyFromArray($yesStyle);
    } else {
        $sheet->getStyle('O' . $row)->applyFromArray($noStyle);
    }
    
    $row++;
}

// Add summary section
$summaryRow = $row + 2;

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

// Add summary title
$sheet->mergeCells('A' . $summaryRow . ':P' . $summaryRow);
$sheet->setCellValue('A' . $summaryRow, 'RÉSUMÉ DE L\'EXPORT');
$sheet->getStyle('A' . $summaryRow)->applyFromArray([
    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

// Summary data
$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Total des soumissions:');
$sheet->setCellValue('B' . $summaryRow, count($submissions));
$sheet->getStyle('A' . $summaryRow . ':B' . $summaryRow)->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E8']]
]);

// File statistics
$summaryRow++;
$sheet->mergeCells('A' . $summaryRow . ':P' . $summaryRow);
$sheet->setCellValue('A' . $summaryRow, 'STATISTIQUES DES FICHIERS');
$sheet->getStyle('A' . $summaryRow)->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Total des photos:');
$sheet->setCellValue('B' . $summaryRow, $totalPhotos);

$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Soumissions avec photos:');
$sheet->setCellValue('B' . $summaryRow, $submissionsWithPhotos);

$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Audio principal (enregistrement):');
$sheet->setCellValue('B' . $summaryRow, $totalMainAudios);

$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Total des audios multiples:');
$sheet->setCellValue('B' . $summaryRow, $totalMultipleAudios);

$summaryRow++;
$sheet->setCellValue('A' . $summaryRow, 'Soumissions avec audios multiples:');
$sheet->setCellValue('B' . $summaryRow, $submissionsWithMultipleAudios);

// Filters info
$summaryRow++;
$sheet->mergeCells('A' . $summaryRow . ':P' . $summaryRow);
$sheet->setCellValue('A' . $summaryRow, 'FILTRES APPLIQUÉS:');
$sheet->getStyle('A' . $summaryRow)->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9C27B0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

$filtersText = [];
if (!empty($filter_name)) $filtersText[] = "Nom: $filter_name";
if (!empty($filter_email)) $filtersText[] = "Email: $filter_email";
if (!empty($filter_country)) $filtersText[] = "Pays: $filter_country";
if (!empty($filter_birth_place)) $filtersText[] = "Lieu: $filter_birth_place";
if (!empty($filter_date)) $filtersText[] = "Date: $filter_date";

if (empty($filtersText)) {
    $filtersText[] = "Aucun filtre appliqué";
}

$summaryRow++;
$sheet->mergeCells('A' . $summaryRow . ':P' . $summaryRow);
$sheet->setCellValue('A' . $summaryRow, implode(', ', $filtersText));

// Export info
$summaryRow++;
$sheet->mergeCells('A' . $summaryRow . ':P' . $summaryRow);
$sheet->setCellValue('A' . $summaryRow, 'Exporté le: ' . date('d/m/Y H:i:s') . ' par: ' . ($_SESSION['user_name'] ?? 'Admin'));
$sheet->getStyle('A' . $summaryRow)->applyFromArray([
    'font' => ['size' => 10, 'italic' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']]
]);

// Set filename
$filename = 'GriotBook_Export_Soumissions_' . date('Y-m-d_H-i-s') . '.xlsx';

// Send headers
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

// Create writer and save to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
?>
