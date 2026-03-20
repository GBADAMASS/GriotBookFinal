<?php
session_start();
require_once '../database/db.php';

// Récupérer le full_name depuis la session si connecté, sinon utiliser une chaîne vide
$full_name = $_SESSION['user_name'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr" class="scheme_original">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">

    <link rel="icon" type="image/x-icon" href="../logo/1.png" />
    <title>GriotBook &#8211; votre bibliothèque numérique d'histoire</title>
    <link rel='stylesheet' href="https://fonts.googleapis.com/css?family=Droid+Serif:400,400i,700,700i|Grand+Hotel|Open+Sans:300,400,600,700,800|Raleway:100,200,300,400,500,600,700,800,900|Source+Sans+Pro:300,300i,400,400i,600,600i,700,700i|Ubuntu:300,300i,400,400i,500,500i,700,700i&amp;subset=latin-ext" type='text/css' media='all' >
    <link rel='stylesheet' href='../js/vendor/revslider/settings.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../js/vendor/woo/woocommerce-layout.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../js/vendor/woo/woocommerce-smallscreen.css' type='text/css' media='only screen and (max-width: 768px)' />
    <link rel='stylesheet' href='../js/vendor/woo/woocommerce.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/fontello/css/fontello.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/style.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/core.animation.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/shortcodes.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../js/vendor/woo/plugin.woocommerce.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/skin.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/doc-style.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/responsive.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/skin.responsive.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../js/vendor/comp/comp.min.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/custom.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/core.messages.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/core.portfolio.css' type='text/css' media='all' />

    <link rel='stylesheet' href="https://fonts.googleapis.com/css?family=Droid+Serif:400,400i,700,700i|Open+Sans:300,400,600,700,800|Raleway:100,200,300,400,500,600,700,800,900&subset=latin-ext" type='text/css' media='all'>
    <link rel='stylesheet' href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;600&display=swap" type='text/css' media='all'>
    <link rel='stylesheet' href='../css/fontello/css/fontello.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/style.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/core.animation.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/shortcodes.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/skin.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/responsive.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/skin.responsive.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/custom.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/core.messages.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/footer.css' type='text/css' media='all' />
    <link rel='stylesheet' href='../css/whatsapp-float.css' type='text/css' media='all' />

        <style>
        /* ---- CSS Variables ---- */
        :root { --griot-gold: #b5901f; }

        /* ---- Global ---- */
        body { background-color: #d4d4cf; margin: 0; padding: 0; }
        
        /* ---- FIX FORMULAIRE LARGEUR ---- */
        .page_wrap {
            max-width: none !important;
            width: 100% !important;
        }
        
        /* ---- FORCER FOOTER VISIBLE ---- */
        .footer_wrap {
            display: block !important;
            visibility: visible !important;
            position: relative !important;
            z-index: 999 !important;
        }
        
        /* ---- FORCER TAILLE LOGO ---- */
        .logo_footer {
            max-width: 80px !important;
            height: auto !important;
            width: 80px !important;
        }
        
        /* ---- RESPONSIVITE FOOTER FORCEE ---- */
        @media (max-width: 768px) {
            .footer_wrap {
                padding: 40px 0 20px !important;
                margin-top: 30px !important;
            }
            
            .contacts_wrap_inner {
                padding: 0 15px !important;
            }
            
            .logo_footer img,
            .logo img {
                width: 60px !important;
                max-width: 60px !important;
                height: auto !important;
            }
            
            .contacts_address {
                font-size: 14px !important;
                text-align: center !important;
            }
            
            .copyright_text {
                font-size: 11px !important;
            }
        }
        
        @media (max-width: 480px) {
            .footer_wrap {
                padding: 30px 0 15px !important;
                margin-top: 20px !important;
            }
            
            .contacts_wrap_inner {
                padding: 0 10px !important;
            }
            
            .logo_footer img,
            .logo img {
                width: 50px !important;
                max-width: 50px !important;
                height: auto !important;
            }
            
            .contacts_address {
                font-size: 13px !important;
                text-align: center !important;
            }
            
            .copyright_text {
                font-size: 10px !important;
                text-align: center !important;
            }
        }
        
        .form_page_wrap {
            max-width: 600px !important;
            margin: 0 auto !important;
            padding: 70px 30px 80px !important;
        }

        /* ---- FORM HEADER ---- */
        .form_header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            padding-top: 15px;
        }
        .form_header::before {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background-color: var(--griot-gold);
            margin: 0 auto 25px;
            border-radius: 2px;
        }
        .back_link {
            position: absolute;
            left: 0;
            top: 15px;
            color: #888;
            font-family: 'Lato', sans-serif;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .back_link:hover { color: var(--griot-gold); }
        .form_header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 700;
            color: #1d1d1d;
            margin: 0 0 14px;
        }
        .form_header p {
            font-size: 15px;
            color: #777;
            max-width: 460px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ---- SECTIONS ---- */
        .form_section { 
            margin: 0 auto 40px auto; 
            width: 100%;
            max-width: 560px;
            box-sizing: border-box;
            padding: 0 20px;
        }
        
        /* Forcer les mêmes marges pour toutes les sections */
        .form_section:nth-child(1),
        .form_section:nth-child(2),
        .form_section:nth-child(3) {
            padding: 0 20px !important;
            margin: 0 auto 40px auto !important;
            max-width: 560px !important;
            width: 100% !important;
        }
        
        .form_section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: #333;
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e6e6e6;
        }

        /* ---- ROW / COL ---- */
        .form_row { 
            display: flex; 
            gap: 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .form_col { 
            flex: 1; 
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }
        .form_group { 
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        /* ---- LABELS ---- */
        .form_label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        /* ---- INPUTS & TEXTAREAS ---- */
        .form_input, .form_textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            font-family: 'Lato', sans-serif;
            font-size: 14px;
            color: #333;
            box-sizing: border-box;
            transition: border-color 0.25s, box-shadow 0.25s;
        }
        .form_input:focus, .form_textarea:focus {
            outline: none;
            border-color: var(--griot-gold);
            box-shadow: 0 0 0 3px rgba(181, 144, 31, 0.1);
        }
        .form_textarea { 
            resize: vertical; 
            min-height: 100px; 
            line-height: 1.55; 
        }
        .form_input::placeholder, .form_textarea::placeholder { 
            color: #bbb; 
        }

        /* ---- UPLOAD AREA ---- */
        .upload_area {
            display: block;
            border: 2px dashed #d5d5d5;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: border-color 0.25s, background 0.25s;
            width: 100%;
            box-sizing: border-box;
        }
        .upload_area:hover { border-color: var(--griot-gold); background: #fdf9f0; }
        .upload_icon { font-size: 30px; color: #aaa; margin-bottom: 12px; display: block; }
        .upload_text { font-size: 14px; color: #555; margin-bottom: 5px; }
        .upload_subtext { font-size: 12px; color: #999; }
        .file_input { display: none; }

        /* ---- UPLOAD AREA DRAG ACTIVE ---- */
        .upload_area.drag_over {
            border-color: var(--griot-gold);
            background: #fdf9f0;
        }
        /* ---- PHOTO THUMBNAIL PREVIEW ---- */
        .photo_thumb {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #eee;
            transition: border-color 0.2s;
        }
        .photo_thumb:hover { border-color: var(--griot-gold); }
        .photo_thumb_wrap {
            position: relative;
            width: 90px;
            height: 90px;
        }
        .photo_thumb_remove {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ff5e5e;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* ---- AUDIO GRID (2 cards side by side) ---- */
        .audio_grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 0;
            width: 100%;
            box-sizing: border-box;
        }
        .audio_card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.25s, box-shadow 0.25s;
            font-family: 'Lato', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            user-select: none;
            width: 100%;
            box-sizing: border-box;
        }
        .audio_card:hover {
            border-color: var(--griot-gold);
            box-shadow: 0 3px 10px rgba(181, 144, 31, 0.12);
        }
        .audio_card.recording {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.15);
            animation: pulse-red 1.2s ease-in-out infinite;
        }
        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 0 3px rgba(231,76,60,0.15); }
            50%       { box-shadow: 0 0 0 8px rgba(231,76,60,0.05); }
        }
        .audio_card_icon { font-size: 32px; }
        .audio_card_label { font-size: 14px; font-weight: 600; color: #333; }
        .audio_card_sub   { font-size: 12px; color: #999; }
        @media (max-width: 480px) {
            .audio_grid { grid-template-columns: 1fr; }
        }

        /* ---- CONSENT ---- */
        .checkbox_group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 30px;
            background: #f6f4ef;
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #ede8dc;
            max-width: 520px;
            margin: 0 auto 30px auto;
        }
        .checkbox_input { margin-top: 2px; flex-shrink: 0; }
        .checkbox_label { font-size: 12px; color: #666; line-height: 1.65; }

        /* ---- SUBMIT BUTTON ---- */
        .submit_btn {
            background: var(--griot-gold);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 5px;
            font-family: 'Lato', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            max-width: 520px;
            box-sizing: border-box;
            letter-spacing: 0.5px;
            transition: background 0.3s, transform 0.2s;
            margin: 0 auto 30px auto;
            display: block;
        }
        .submit_btn:hover { background: #c9a42d; transform: translateY(-1px); }

        /* ---- ALERTS ---- */
        .alert { 
            padding: 14px 16px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            font-size: 14px; 
            width: 100%;
            box-sizing: border-box;
        }
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .alert-error   { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 650px) {
            .form_page_wrap { 
                padding: 55px 16px 60px; 
                max-width: 100% !important;
            }
            .form_row { flex-direction: column; gap: 0; }
            .form_header h1 { font-size: 26px; }
            .back_link { position: static; margin-bottom: 15px; display: inline-flex; }
            .audio_buttons { flex-direction: column; }
            .form_section {
                padding: 0 10px !important;
                margin: 0 auto 40px auto !important;
                max-width: 100% !important;
            }
            .form_section:nth-child(1),
            .form_section:nth-child(2),
            .form_section:nth-child(3) {
                padding: 0 10px !important;
                margin: 0 auto 40px auto !important;
                max-width: 100% !important;
            }
            .checkbox_group {
                max-width: 100% !important;
                margin: 0 auto 30px auto !important;
            }
            .submit_btn {
                max-width: 100% !important;
                margin: 0 auto 30px auto !important;
                display: block !important;
            }
        }
        
        /* ---- RESPONSIVE NAVBAR ---- */
    @media (max-width: 768px) {
        /* Header professionnel et minimaliste */
        .top_panel_middle {
            position: relative !important;
            height: 70px !important;
            background: #fff !important;
            border-bottom: 1px solid #e5e5e5 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        }
        
        .content_wrap {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            height: 100% !important;
            padding: 0 20px !important;
        }
        
        .menu_main_wrap {
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
        }
        
        .logo img {
            height: auto !important;
            max-width: 140px !important;
        }
        
        .menu_main_nav {
            display: none !important;
        }
        
        .hamburger_menu {
            display: block !important;
            width: 24px;
            height: 18px;
            position: relative;
            cursor: pointer;
            z-index: 1001;
        }
        
        .hamburger_menu span {
            display: block;
            height: 2px;
            width: 100%;
            background: #333;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }
        
        .hamburger_menu span:last-child {
            margin-bottom: 0;
        }
    }
    </style>
</head>

<body class="home page body_filled article_style_stretch scheme_original top_panel_show top_panel_above sidebar_hide sidebar_outer_hide preloader vc_responsive">
<div id="page_preloader"></div>
<a id="toc_home" class="sc_anchor" title="Home" data-description="&lt;i&gt;Return to Home&lt;/i&gt; - &lt;br&gt;navigate to home page of the site" data-icon="icon-home" data-url="index.html" data-separator="yes"></a>
<a id="toc_top" class="sc_anchor" title="To Top" data-description="&lt;i&gt;Back to top&lt;/i&gt; - &lt;br&gt;scroll to top of the page" data-icon="icon-double-up" data-url="" data-separator="yes"></a>

<div class="body_wrap">
    <div class="page_wrap">
        <div class="top_panel_fixed_wrap"></div>
        <header class="top_panel_wrap top_panel_style_3 scheme_original">
            <div class="top_panel_wrap_inner top_panel_inner_style_3 top_panel_position_above">
                <div class="top_panel_middle">
                    <div class="content_wrap">
                        <div class="contact_logo">
                            <div class="logo">
                                <a href="index.html">
                                    <img src="../logo/1.png" class="logo_main" alt="" width="128" height="124">
                                    <img src="../logo/1.png" class="logo_fixed" alt="" width="161" height="47">
                                </a>
                            </div>
                        </div>
                        <div class="menu_main_wrap">
                            <a href="#" class="menu_main_responsive_button icon-menu"></a>
                            <nav class="menu_main_nav_area">
                                <ul id="menu_main" class="menu_main_nav">
                                    <li class="menu-item current-menu-item"><a href="index.html">Comment ca marche</a></li>
                                    <li class="menu-item current-menu-item"><a href="index.html">Pour qui</a></li>
                                    <li class="menu-item current-menu-item"><a href="index.html">Pourquoi</a></li>
                                    <li class="menu-item">
                                        <a href="#" class="sc_button sc_button_square sc_button_style_filled sc_button_size_small">Commencer votre histoire</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="header_mobile">
            <div class="content_wrap">
                <div class="menu_button icon-menu"></div>
                <div class="logo">
                    <a href="index.html">
                        <img src="logo/1.png" class="logo_main" alt="" width="128" height="124">
                    </a>
                </div>
            </div>
            <div class="side_wrap">
                <div class="close">Close</div>
                <div class="panel_top">
                    <nav class="menu_main_nav_area">
                        <ul id="menu_main_mobile" class="menu_main_nav">
                            <li class="menu-item current-menu-item"><a href="index.php">Comment ca marche</a></li>
                            <li class="menu-item current-menu-item"><a href="index.php">Pour qui</a></li>
                            <li class="menu-item current-menu-item"><a href="index.php">Pourquoi</a></li>
                            <li class="menu-item">
                                <a href="#" class="sc_button sc_button_square sc_button_style_filled sc_button_size_small">Commencer votre histoire</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="mask"></div>
        </div>

        <div class="page_wrap">
    <div class="form_page_wrap">
        <div class="form_header">
            <h1>Commencez votre histoire</h1>
            <p>Partagez votre récit et vos souvenirs pour qu'ils traversent les générations.</p>
        </div>

        <?php
        // Messages de session
        if (isset($_SESSION['success_msg'])) {
            echo '<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    ' . htmlspecialchars($_SESSION['success_msg']) . '
                  </div>';
            unset($_SESSION['success_msg']); // Supprimer après affichage
        }
        
        if (isset($_SESSION['error_msg'])) {
            echo '<div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    ' . htmlspecialchars($_SESSION['error_msg']) . '
                  </div>';
            unset($_SESSION['error_msg']); // Supprimer après affichage
        }
        
        // Messages GET (backup)
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    Votre soumission a été enregistrée avec succès !
                  </div>';
        }
        
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    Une erreur est survenue : ' . htmlspecialchars($_GET['error']) . '
                  </div>';
        }
        ?>

        <form action="submit.php" method="post" enctype="multipart/form-data" id="mainForm">
            
            <!-- SECTION 1: INFORMATIONS PERSONNELLES -->
            <div class="form_section">
                <h2>Informations personnelles</h2>
                <div class="form_row">
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="full_name">Nom complet *</label>
                            <input type="text" id="full_name" name="full_name" class="form_input" 
                                   value="<?php echo htmlspecialchars($full_name); ?>" required>
                        </div>
                    </div>
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form_input" required>
                        </div>
                    </div>
                </div>
                <div class="form_row">
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" class="form_input">
                        </div>
                    </div>
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="country">Pays</label>
                            <input type="text" id="country" name="country" class="form_input">
                        </div>
                    </div>
                </div>
                <div class="form_row">
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" class="form_input">
                        </div>
                    </div>
                    <div class="form_col">
                        <div class="form_group">
                            <label class="form_label" for="birth_place">Où êtes-vous né(e) ?</label>
                            <input type="text" id="birth_place" name="birth_place" class="form_input">
                        </div>
                    </div>
                </div>
                <div class="form_group">
                    <label class="form_label" for="book_about">À propos de qui est ce livre ?</label>
                    <input type="text" id="book_about" name="book_about" class="form_input" placeholder="Mon père, ma grand-mère...">
                </div>
            </div>
            </div>

            <!-- SECTION 2: L'HISTOIRE -->
            <div class="form_section">
                <h2>L'histoire</h2>
                <div class="form_group">
                    <label class="form_label" for="meeting_story">Comment avez-vous rencontré votre partenaire ?</label>
                    <textarea id="meeting_story" name="meeting_story" class="form_textarea" 
                              placeholder="Prenez votre temps pour répondre..."></textarea>
                </div>
                <div class="form_group" style="margin-top: 20px;">
                    <label class="form_label" for="proudest_moment">Votre plus grande fierté ?</label>
                    <textarea id="proudest_moment" name="proudest_moment" class="form_textarea" 
                              placeholder="Prenez votre temps pour répondre..."></textarea>
                </div>
                <div class="form_group" style="margin-top: 20px;">
                    <label class="form_label" for="hard_times">Une épreuve marquante ?</label>
                    <textarea id="hard_times" name="hard_times" class="form_textarea" 
                              placeholder="Prenez votre temps pour répondre..."></textarea>
                </div>
                <div class="form_group" style="margin-top: 20px;">
                    <label class="form_label" for="children_message">Quel message pour vos enfants ?</label>
                    <textarea id="children_message" name="children_message" class="form_textarea" 
                              placeholder="Prenez votre temps pour répondre..."></textarea>
                </div>
            </div>

            <!-- SECTION 3: FICHIERS -->
            <div class="form_section">
                <h2>Photos et souvenirs</h2>
                
                <!-- Upload de photos -->
                <div class="form_group">
                    <label class="form_label">Ajoutez des photos</label>
                    <div class="upload_area" onclick="document.getElementById('photo_input').click()">
                        <i class="fas fa-camera upload_icon"></i>
                        <div class="upload_text">Cliquez pour sélectionner une photo</div>
                        <div class="upload_subtext">PNG, JPG, JPEG jusqu'à 10MB</div>
                        <input type="file" id="photo_input" class="file_input" name="photos[]" multiple accept=".png,.jpg,.jpeg">
                    </div>
                    <div id="photo_preview" style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
                </div>
            </div>

            <!-- SECTION 4: AUDIO -->
            <div class="form_section">
                <h2>Enregistrement audio</h2>
                <div class="audio_grid">
                    <div class="audio_card" id="record_btn">
                        <i class="fas fa-microphone audio_card_icon"></i>
                        <div class="audio_card_label">Enregistrer</div>
                        <div class="audio_card_sub">Cliquez pour commencer</div>
                    </div>
                    <div class="audio_card" id="upload_btn" onclick="document.getElementById('audio_file_input').click()">
                        <i class="fas fa-upload audio_card_icon"></i>
                        <div class="audio_card_label">Téléverser</div>
                        <div class="audio_card_sub">MP3, WAV, M4A, OGG, WEBM (Max 50MB)</div>
                        <input type="file" id="audio_file_input" class="file_input" accept=".mp3,.wav,.m4a,.ogg,.webm,.mp4" multiple>
                    </div>
                </div>
                
                <div id="audio_preview" style="margin-top: 20px; display: none;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid var(--griot-gold);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: #333;">
                                <i class="fas fa-music"></i> Enregistrement audio
                            </span>
                            <button type="button" onclick="removeRecording()" style="background: #ff5e5e; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                        <audio id="recorded_audio" controls style="width: 100%;"></audio>
                    </div>
                </div>
                
                <div id="audio_files_preview" style="margin-top: 20px; display: none;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid var(--griot-gold);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: #333;">
                                <i class="fas fa-music"></i> Fichiers audio (<span id="audio_count">0</span>)
                            </span>
                        </div>
                        <div id="audio_files_list" style="display: flex; flex-direction: column; gap: 10px;"></div>
                    </div>
                </div>
                
                <input type="file" id="audio_input" name="audio_files[]" multiple style="display: none;">
            </div>

            <!-- CONSENT -->
            <div class="checkbox_group">
                <input type="checkbox" id="consent" class="checkbox_input" required>
                <label for="consent" class="checkbox_label">
                    J'accepte que mon histoire et mes fichiers soient conservés et partagés selon les conditions d'utilisation de GriotBook.
                </label>
            </div>

            <!-- SUBMIT -->
            <button type="submit" class="submit_btn">
                <i class="fas fa-paper-plane"></i> Envoyer votre histoire
            </button>
        </form>
    </div>
    <?php 
    // Utiliser le même footer que index.php avec ses styles et responsivité
    require_once '../admin/includes/whatsapp_button.php'; 
    echo getWhatsAppButton(); 
    ?>
            <footer class="contacts_wrap scheme_original">
            <div class="contacts_wrap_inner">
                <div class="content_wrap">
                    <div class="logo">
                        <a href="index.html">
                            <img src="../logo/1.png" class="logo_footer" alt="" width="95" height="90">
                        </a>
                    </div>
                    <div class="contacts_address">
                        <div class="about_company">
                            GriotBook préserve et partage les histoires audio et photo de chacun. 
                            Une plateforme pour que vos voix et vos souvenirs deviennent un héritage vivant pour les générations futures.
                        </div>
                        <address class="address_right">
                            Email: contact@griotbook.com<br>
                            Support: help@griotbook.com
                        </address>
                        <address class="address_left">
                            Lome, Togo<br>
                            Plateforme mondiale d'histoires
                        </address>
                    </div>
                    <div class="sc_socials sc_socials_type_icons sc_socials_shape_square sc_socials_size_medium">
                        <div class="sc_socials_item">
                            <a href="#" target="_blank" class="social_icons social_twitter">
                                <span class="icon-twitter"></span>
                            </a>
                        </div>
                        <div class="sc_socials_item">
                            <a href="#" target="_blank" class="social_icons social_facebook">
                                <span class="icon-facebook"></span>
                            </a>
                        </div>
                        <div class="sc_socials_item">
                            <a href="#" target="_blank" class="social_icons social_gplus">
                                <span class="icon-gplus"></span>
                            </a>
                        </div>
                        <div class="sc_socials_item">
                            <a href="#" target="_blank" class="social_icons social_linkedin">
                                <span class="icon-linkedin"></span>
                            </a>
                        </div>
                        <div class="sc_socials_item">
                            <a href="#" target="_blank" class="social_icons social_skype">
                                <span class="icon-skype"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <div class="copyright_wrap copyright_style_text  scheme_original">
        </div>
    </div>
</div>
<div id="popup_registration" class="popup_wrap popup_registration bg_tint_light">
    <a href="#" class="popup_close"></a>
    <div class="form_wrap">
        <form name="registration_form" method="post" class="popup_form registration_form">
            <input type="hidden" name="redirect_to" value="#" />
            <div class="form_left">
                <div class="popup_form_field login_field iconed_field icon-user">
                    <input type="text" id="registration_username" name="registration_username" value="" placeholder="User name (login)">
                </div>
                <div class="popup_form_field email_field iconed_field icon-mail">
                    <input type="text" id="registration_email" name="registration_email" value="" placeholder="E-mail">
                </div>
                <div class="popup_form_field agree_field">
                    <input type="checkbox" value="agree" id="registration_agree" name="registration_agree">
                    <label for="registration_agree">I agree with</label>
                    <a href="#">Terms &amp; Conditions</a>
                </div>
                <div class="popup_form_field submit_field">
                    <input type="submit" class="submit_button" value="Sign Up">
                </div>
            </div>
            <div class="form_right">
                <div class="popup_form_field password_field iconed_field icon-lock">
                    <input type="password" id="registration_pwd" name="registration_pwd" value="" placeholder="Password">
                </div>
                <div class="popup_form_field password_field iconed_field icon-lock">
                    <input type="password" id="registration_pwd2" name="registration_pwd2" value="" placeholder="Confirm Password">
                </div>
                <div class="popup_form_field description_field">Minimum 6 characters</div>
            </div>
        </form>
        <div class="result message_block"></div>
    </div>
</div>
<div id="popup_login" class="popup_wrap popup_login bg_tint_light">
    <a href="#" class="popup_close"></a>
    <div class="form_wrap">
        <div class="form_left">
            <form action="#" method="post" name="login_form" class="popup_form login_form">
                <input type="hidden" name="redirect_to" value="#">
                <div class="popup_form_field login_field iconed_field icon-user">
                    <input type="text" id="log" name="log" value="" placeholder="Login or Email">
                </div>
                <div class="popup_form_field password_field iconed_field icon-lock">
                    <input type="password" id="password" name="pwd" value="" placeholder="Password">
                </div>
                <div class="popup_form_field remember_field">
                    <a href="#" class="forgot_password">Forgot password?</a>
                    <input type="checkbox" value="forever" id="rememberme" name="rememberme">
                    <label for="rememberme">Remember me</label>
                </div>
                <div class="popup_form_field submit_field">
                    <input type="submit" class="submit_button" value="Login">
                </div>
            </form>
        </div>
        <div class="form_right">
            <div class="login_socials_title">You can login using your social profile</div>
            <div class="login_socials_list">
                <div class="sc_socials sc_socials_type_icons sc_socials_shape_round sc_socials_size_tiny">
                    <div class="sc_socials_item">
                        <a href="#" target="_blank" class="social_icons social_facebook">
                            <span class="icon-facebook"></span>
                        </a>
                    </div>
                    <div class="sc_socials_item">
                        <a href="#" target="_blank" class="social_icons social_twitter">
                            <span class="icon-twitter"></span>
                        </a>
                    </div>
                    <div class="sc_socials_item">
                        <a href="#" target="_blank" class="social_icons social_gplus">
                            <span class="icon-gplus"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="login_socials_problem"><a href="#">Problem with login?</a></div>
            <div class="result message_block"></div>
        </div>
    </div>
</div>

<a href="#" class="scroll_to_top icon-up" title="Scroll to top"></a>
<div class="custom_html_section"></div>

<script type='text/javascript' src='../js/vendor/jquery/jquery.js'></script>
<script type='text/javascript' src='../js/vendor/jquery/jquery-migrate.min.js'></script>
<script type='text/javascript' src='../js/custom/custom.js'></script>
<script type='text/javascript' src='../js/vendor/esg/jquery.themepunch.tools.min.js'></script>
<script type='text/javascript' src='../js/vendor/revslider/jquery.themepunch.revolution.min.js'></script>
<script type="text/javascript" src="../js/vendor/revslider/extensions/revolution.extension.slideanims.min.js"></script>
<script type="text/javascript" src="../js/vendor/revslider/extensions/revolution.extension.layeranimation.min.js"></script>
<script type="text/javascript" src="../js/vendor/revslider/extensions/revolution.extension.navigation.min.js"></script>
<script type="text/javascript" src="../js/vendor/revslider/extensions/revolution.extension.parallax.min.js"></script>
<script type='text/javascript' src='../js/vendor/modernizr.min.js'></script>
<script type='text/javascript' src='../js/vendor/ui/core.min.js'></script>
<script type='text/javascript' src='../js/vendor/superfish.js'></script>
<script type='text/javascript' src='../js/custom/jquery.slidemenu.js'></script>
<script type='text/javascript' src='../js/custom/core.utils.js'></script>
<script type='text/javascript' src='../js/custom/core.init.js'></script>
<script type='text/javascript' src='../js/custom/init.js'></script>
<script type='text/javascript' src='../js/custom/embed.min.js'></script>
<script type='text/javascript' src='../js/custom/shortcodes.js'></script>
<script type='text/javascript' src='../js/custom/core.messages.js'></script>
<script type='text/javascript' src='../js/vendor/comp/comp_front.min.js'></script>
<script type='text/javascript' src='../js/vendor/isotope.pkgd.min.js'></script>
<script type='text/javascript' src='../js/vendor/jquery.hoverdir.js'></script>

<script>
    let mediaRecorder = null;
    let audioChunks = [];
    let recordedBlob = null;

    // Gestion des photos
    document.getElementById('photo_input').addEventListener('change', function(e) {
        const files = e.target.files;
        const preview = document.getElementById('photo_preview');
        preview.innerHTML = '';
        
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const wrap = document.createElement('div');
                    wrap.className = 'photo_thumb_wrap';
                    wrap.innerHTML = `
                        <img src="${e.target.result}" class="photo_thumb" alt="Photo ${i+1}">
                        <button type="button" class="photo_thumb_remove" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    preview.appendChild(wrap);
                };
                
                reader.readAsDataURL(file);
            }
        }
    });

    // Gestion de l'enregistrement audio
    document.getElementById('record_btn').addEventListener('click', function() {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            // Arrêter l'enregistrement
            mediaRecorder.stop();
            this.classList.remove('recording');
            this.querySelector('.audio_card_label').textContent = 'Enregistrer';
            this.querySelector('.audio_card_sub').textContent = 'Cliquez pour commencer';
        } else {
            // Démarrer l'enregistrement
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Votre navigateur ne supporte pas l'enregistrement audio.");
                return;
            }
            
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function(stream) {
                    audioChunks = [];
                    mediaRecorder = new MediaRecorder(stream);
                    
                    mediaRecorder.addEventListener('dataavailable', function(e) {
                        if (e.data && e.data.size > 0) {
                            audioChunks.push(e.data);
                        }
                    });
                    
                    mediaRecorder.addEventListener('stop', function() {
                        recordedBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const audioUrl = URL.createObjectURL(recordedBlob);
                        
                        // Afficher l'audio
                        const audioElement = document.getElementById('recorded_audio');
                        audioElement.src = audioUrl;
                        document.getElementById('audio_preview').style.display = 'block';
                        
                        // Attacher le blob au formulaire
                        const audioInput = document.getElementById('audio_input');
                        const dataTransfer = new DataTransfer();
                        const file = new File([recordedBlob], 'recording.webm', { type: 'audio/webm' });
                        dataTransfer.items.add(file);
                        audioInput.files = dataTransfer.files;
                        
                        // Arrêter le micro
                        stream.getTracks().forEach(track => track.stop());
                    });
                    
                    // Démarrer
                    mediaRecorder.start();
                    document.getElementById('record_btn').classList.add('recording');
                    document.getElementById('record_btn').querySelector('.audio_card_label').textContent = 'Arrêter';
                    document.getElementById('record_btn').querySelector('.audio_card_sub').textContent = 'Enregistrement en cours...';
                })
                .catch(function(err) {
                    console.error('Erreur microphone:', err);
                    alert("Impossible d'accéder au microphone.");
                });
        }
    });

    // Gestion de l'upload de fichiers audio multiples
    document.getElementById('audio_file_input').addEventListener('change', function(e) {
        const files = e.target.files;
        const audioFilesList = document.getElementById('audio_files_list');
        const audioFilesPreview = document.getElementById('audio_files_preview');
        const audioCount = document.getElementById('audio_count');
        
        // Vider la liste existante
        audioFilesList.innerHTML = '';
        
        if (files.length > 0) {
            audioFilesPreview.style.display = 'block';
            
            // Mettre à jour le compteur
            audioCount.textContent = files.length;
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileURL = URL.createObjectURL(file);
                
                const audioItem = document.createElement('div');
                audioItem.style.cssText = 'display: flex; align-items: center; justify-content: space-between; background: white; padding: 10px; border-radius: 5px; border: 1px solid #ddd;';
                audioItem.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-music" style="color: #666;"></i>
                        <span style="font-size: 14px; color: #333;">${file.name}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <audio controls style="width: 200px; height: 30px;">
                            <source src="${fileURL}" type="${file.type}">
                        </audio>
                        <button type="button" onclick="this.parentElement.parentElement.remove(); updateAudioCount();" style="background: #ff5e5e; color: white; border: none; padding: 5px 8px; border-radius: 3px; cursor: pointer; font-size: 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                audioFilesList.appendChild(audioItem);
            }
            
            // Copier les fichiers vers l'input caché
            const audioInput = document.getElementById('audio_input');
            audioInput.files = files;
        } else {
            audioFilesPreview.style.display = 'none';
            audioCount.textContent = '0';
        }
    });
    
    // Fonction pour mettre à jour le compteur lorsqu'un fichier est supprimé
    function updateAudioCount() {
        const audioFilesList = document.getElementById('audio_files_list');
        const audioCount = document.getElementById('audio_count');
        const audioFilesPreview = document.getElementById('audio_files_preview');
        const remainingFiles = audioFilesList.children.length;
        
        audioCount.textContent = remainingFiles;
        
        if (remainingFiles === 0) {
            audioFilesPreview.style.display = 'none';
            document.getElementById('audio_file_input').value = '';
        }
    }

    function removeRecording() {
        document.getElementById('audio_preview').style.display = 'none';
        document.getElementById('recorded_audio').src = '';
        document.getElementById('audio_input').value = '';
        document.getElementById('audio_file_input').value = '';
        document.getElementById('audio_files_preview').style.display = 'none';
        document.getElementById('audio_files_list').innerHTML = '';
        document.getElementById('audio_count').textContent = '0';
        recordedBlob = null;
        audioChunks = [];
        
        // Réinitialiser les boutons audio
        document.getElementById('record_btn').classList.remove('recording');
        document.getElementById('record_btn').querySelector('.audio_card_label').textContent = 'Enregistrer';
        document.getElementById('record_btn').querySelector('.audio_card_sub').textContent = 'Cliquez pour commencer';
    }

    // Avant la soumission, copier le fichier audio vers le champ caché
    document.getElementById('mainForm').addEventListener('submit', function(e) {
        const visibleInput = document.getElementById('audio_file_input');
        const hiddenInput = document.getElementById('audio_input');
        
        if (visibleInput.files.length > 0) {
            // Copier les fichiers du champ visible vers le champ caché
            const dataTransfer = new DataTransfer();
            for (let file of visibleInput.files) {
                dataTransfer.items.add(file);
            }
            hiddenInput.files = dataTransfer.files;
            console.log('Fichier audio copié:', visibleInput.files[0].name);
        }
    });

</script>

<?php require_once '../admin/includes/whatsapp_button.php'; echo getWhatsAppButton('../css/whatsapp-float.css'); ?>
</body>

</html>