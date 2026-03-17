<?php
/**
 * Footer commun pour GriotBook
 * Utilisé sur index.php et form.php
 * Détecte automatiquement si on est dans admin ou à la racine pour ajuster les chemins
 */

// Détecter si on est dans admin pour ajuster les chemins
$current_path = $_SERVER['PHP_SELF'] ?? '';
$is_admin = (strpos($current_path, '/admin/') !== false);
$is_formulaire = (strpos($current_path, '/formulaire/') !== false);

// Déterminer le chemin de base selon le contexte
if ($is_admin) {
    $base_path = '../';
} elseif ($is_formulaire) {
    $base_path = '../';
} else {
    $base_path = '';
}
?>
<!-- Footer commun -->
<footer class="contacts_wrap footer-dark">
    <div class="contacts_wrap_inner">
        <div class="content_wrap">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    <img src="<?php echo $base_path; ?>logo/1.png" class="logo_footer" alt="" width="60" style="height: auto;">
                </a>
            </div>
            <div class="contacts_address aligncenter">
                <div class="about_company">
                    GriotBook est dédié à la préservation du patrimoine familial. Nous aidons les familles à
                    capturer, écrire et transmettre leurs plus beaux souvenirs à travers des livres d'exception.
                </div>
                <address style="font-style: normal;">
                    Email: <a href="mailto:contact@griotbook.com">contact@griotbook.com</a><br>
                    Suivez-nous pour plus d'histoires.
                </address>
            </div>
            <div class="sc_socials sc_socials_type_icons sc_socials_shape_square sc_socials_size_small">
                <div class="sc_socials_item">
                    <a href="#" target="_blank" class="social_icons social_instagram">
                        <span class="icon-instagramm"></span>
                    </a>
                </div>
                <div class="sc_socials_item">
                    <a href="#" target="_blank" class="social_icons social_facebook">
                        <span class="icon-facebook"></span>
                    </a>
                </div>
                <div class="sc_socials_item">
                    <a href="#" target="_blank" class="social_icons social_linkedin">
                        <span class="icon-linkedin"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="copyright_wrap copyright-dark">
    <div class="copyright_wrap_inner">
        <div class="content_wrap aligncenter">
            <div class="copyright_text" style="font-size: 12px; opacity: 0.7;">
                © 2026 GriotBook. Tous droits réservés.
                <span style="margin: 0 10px;">|</span>
                <a href="#">Mentions Légales</a>
                <span style="margin: 0 10px;">|</span>
                <a href="#">Politique de Confidentialité</a>
            </div>
        </div>
    </div>
</div>
