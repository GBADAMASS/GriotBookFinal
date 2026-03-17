<?php
/**
 * Script d'envoi d'email pour GriotBook
 * Utilisé pour le formulaire de contact
 * Détecte automatiquement si on est dans admin ou à la racine
 */

// Détecter si on est dans admin pour ajuster les chemins si nécessaire
$is_admin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);

// Configuration
$contact_email = 'contact@griotbook.com';
$response = array('error' => '');

// Récupération et validation des données
$type = $_REQUEST['type'] ?? '';

if ($type === 'contact') {
    parse_str($_POST['data'] ?? '', $post_data);
    
    $user_name = stripslashes(strip_tags(trim($post_data['username'] ?? '')));
    $user_email = stripslashes(strip_tags(trim($post_data['email'] ?? '')));
    $user_subject = stripslashes(strip_tags(trim($post_data['subject'] ?? '')));
    $user_msg = stripslashes(strip_tags(trim($post_data['message'] ?? '')));
    
    // Validation basique
    if (empty($user_name) || empty($user_email) || empty($user_msg)) {
        $response['error'] = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'Adresse email invalide.';
    } else {
        // Préparation de l'email
        $subject = 'Message depuis GriotBook - ' . $user_subject;
        $message = "Nouveau message de contact\n\n";
        $message .= "Nom: " . $user_name . "\n";
        $message .= "Email: " . $user_email . "\n";
        $message .= "Sujet: " . $user_subject . "\n";
        $message .= "Message:\n" . $user_msg . "\n";
        
        $headers = array(
            'From: ' . $user_email,
            'Reply-To: ' . $user_email,
            'Content-Type: text/plain; charset="utf-8"',
            'X-Mailer: PHP/' . phpversion()
        );
        
        // Envoi de l'email
        if (mail($contact_email, $subject, $message, implode("\r\n", $headers))) {
            $response['success'] = 'Message envoyé avec succès !';
        } else {
            $response['error'] = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
        }
    }
} else {
    $response['error'] = 'Type de requête non valide.';
}

// Réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>