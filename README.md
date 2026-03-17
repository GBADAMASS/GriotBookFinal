# GriotBook - Site Web de Partage d'Histoires

GriotBook est une plateforme web permettant aux utilisateurs de partager leurs histoires personnelles avec photos et fichiers audio, et aux administrateurs de gérer le contenu avec des fonctionnalités avancées d'export et de filtrage.

## 📋 Table des Matières

- [Base de Données](#base-de-données)
- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du Projet](#structure-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Dépendances](#dépendances)

## 🗄️ Base de Données

### Nom de la Base de Données
```
GriotBook
```

### Script SQL de Création des Tables

Le projet utilise MySQL (ou MariaDB). Voici le script pour initialiser la base de données :

```sql
-- Création de la base de données
CREATE DATABASE IF NOT EXISTS GriotBook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE GriotBook;

-- Table des utilisateurs (pour les administrateurs uniquement)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des soumissions d'histoires
CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Peut être NULL pour les soumissions anonymes
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    subject VARCHAR(255),
    birth_place TEXT,
    book_about TEXT,
    meeting_story TEXT,
    proudest_moment TEXT,
    hard_times TEXT,
    children_message TEXT,
    photos_paths JSON,
    audio_path VARCHAR(255),
    audio_paths JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Index pour optimiser les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_submissions_user_id ON submissions(user_id);
CREATE INDEX idx_submissions_created_at ON submissions(created_at);

-- Table WhatsApp pour le bouton flottant
CREATE TABLE IF NOT EXISTS whatsapp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(50) NOT NULL,
    message TEXT DEFAULT '',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion d'un compte administrateur par défaut
-- Mot de passe : admin123 (hashé avec PASSWORD_BCRYPT)
INSERT INTO users (full_name, email, password, role) VALUES 
('Administrateur', 'admin@griotbook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

## 🚀 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache recommandé)
- Extensions PHP requises : PDO, PDO MySQL, JSON, Fileinfo, GD, Zip
- Composer (pour PhpSpreadsheet)

### Étapes d'Installation

1. **Cloner ou copier le projet**
   Placez les fichiers dans le dossier racine de votre serveur local (par exemple `c:\xampp\htdocs\GriotBookSiteWeb`).

2. **Installer les dépendances**
   ```bash
   cd c:\xampp\htdocs\GriotBookSiteWeb
   composer install
   ```

3. **Configurer la base de données**
   - Importez le code SQL ci-dessus dans phpMyAdmin ou via la ligne de commande MySQL.
   - Mettez à jour les informations de connexion dans le fichier `database/db.php`.

4. **Créer les dossiers nécessaires**
   Si le dossier `uploads/` n'existe pas ou n'est pas inscriptible, créez-le et ajustez les droits :
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **Accéder à l'application**
   - Site public : `http://localhost/GriotBookSiteWeb/`
   - Formulaire : `http://localhost/GriotBookSiteWeb/formulaire/form.php`
   - Tableau de bord admin : `http://localhost/GriotBookSiteWeb/admin/login.php`

## ⚙️ Configuration

### Fichier de Connexion (`database/db.php`)
```php
$host = "localhost";   
$user = "root";        
$password = "";        
$dbname = "GriotBook"; 
```

### Compte Administrateur par Défaut
- **Email** : admin@griotbook.com
- **Mot de passe** : admin123

> ⚠️ **Important** : Modifiez le mot de passe depuis l'interface ou la BDD directement après le premier déploiement !

## 📱 Bouton WhatsApp Flottant

Le site intègre un bouton WhatsApp fixe pour permettre aux visiteurs de contacter rapidement les gérants de GriotBook.
Le bouton s'ouvre désormais toujours avec une conversation vide (sans message pré-rempli) pour plus de flexibilité.
Les paramètres de ce bouton (numéro uniquement) se configurent depuis la page `admin/dashboard.php` via le Back-office.

## 📁 Structure du Projet

```
GriotBookSiteWeb/
├── admin/                  # Back-office d'administration
│   ├── login.php           # Page de connexion
│   ├── logout_user.php     # Script de déconnexion
│   ├── dashboard.php       # Tableau de bord avec statistiques
│   ├── view.php            # Visualisation d'une soumission complète (avec lecteur audio)
│   ├── admin_all_submissions.php # Liste de toutes les soumissions avec filtrage avancé
│   ├── admin_delete_submission.php # Suppression
│   ├── export_preview.php  # Page d'aperçu avant export Excel
│   ├── export_to_excel.php # Export Excel natif (.xlsx)
│   └── includes/           # Composants header, footer, etc.
├── css/                    # Feuilles de style principales
├── js/                     # Scripts d'animation, sliders, etc.
├── database/               # Fichiers liés à la base de données
│   └── db.php              # Configuration de connexion PDO MySQL
├── formulaire/             # Pages interactives du formulaire utilisateur
│   ├── form.php            # Le formulaire (textes, photos multiples JSON, audios multiples)
│   └── submit.php          # Traitement PHP du formulaire et sauvegarde BDD
├── index.php               # Page d'accueil publique en Landing Page
├── uploads/                # Fichiers téléversés (Photos et Audios WebM/MP3)
├── vendor/                 # Dépendances Composer (PhpSpreadsheet)
└── README.md               # Ce fichier d'informations
```

## ✨ Fonctionnalités

### Pour les Visiteurs / Clients
- ✅ **Formulaire accessible sans compte** : On peut raconter son histoire tout de suite.
- ✅ **Upload de fichiers multiples** : Les utilisateurs peuvent envoyer plusieurs photos et plusieurs fichiers audio.
- ✅ **Enregistrement vocal natif** : Le visiteur peut enregistrer sa voix directement depuis le site grâce à l'API MediaRecorder.
- ✅ **Aperçu des fichiers** : Prévisualisation des photos et des audios avant soumission.
- ✅ **Suppression individuelle** : Possibilité de supprimer des fichiers individuellement avant soumission.
- ✅ **Compteur dynamique** : Affichage du nombre de fichiers sélectionnés.
- ✅ **Expérience Mobile** : L'interface, les sliders et la navigation s'adaptent aux écrans tactiles.

### Pour les Administrateurs
- ✅ **Accès sécurisé** : Espace `admin/` fermé, protégé par mot de passe.
- ✅ **Supervision totale** : Affichage clair des données texte, carrousel d'images et lecteur de notes vocales hébergées.
- ✅ **Filtrage avancé** : Filtrage par nom, email, pays, lieu de naissance, et date de soumission.
- ✅ **Export Excel professionnel** : Export natif .xlsx avec PhpSpreadsheet, mise en forme professionnelle et statistiques intégrées.
- ✅ **Aperçu avant export** : Page d'aperçu HTML avant téléchargement du fichier Excel.
- ✅ **Badges colorés** : Affichage visuel du nombre de photos et d'audios avec badges distinctifs.
- ✅ **Pagination préservée** : Les filtres sont conservés lors de la navigation.
- ✅ **Design responsive** : Interface adaptée à tous les écrans.
- ✅ **Paramètres WhatsApp simplifiés** : Configuration du numéro WhatsApp uniquement (plus de message par défaut).

### Nouvelles Fonctionnalités (Mise à jour 2024)
- 🆕 **Export Excel .xlsx natif** : Vraiment compatible Excel avec mise en forme professionnelle
- 🆕 **Filtrage multi-critères** : Recherche combinée sur plusieurs champs
- 🆕 **Aperçu avant export** : Vérification visuelle des données avant téléchargement
- 🆕 **Statistiques intégrées** : Comptes automatiques dans les exports
- 🆕 **Design modernisé** : Interface épurée et professionnelle
- 🆕 **Gestion multi-fichiers** : Support de plusieurs photos et audios par soumission
- 🆕 **WhatsApp simplifié** : Bouton sans message pré-rempli pour plus de flexibilité

## 📊 Export et Analyse

### Types d'Export
- **Excel (.xlsx)** : Format natif avec mise en forme professionnelle, statistiques et filtres appliqués
- **Filtrage préservé** : Les critères de recherche sont inclus dans l'export
- **Statistiques automatiques** : Comptes de photos, audios, et soumissions
- **Métadonnées** : Informations d'export et filtres appliqués

### Champs Inclus dans l'Export
- Informations personnelles (nom, email, téléphone, pays, lieu)
- Contenu textuel (sujet, histoires, messages)
- Comptes de fichiers (nombre de photos, nombre d'audios)
- Indicateurs de présence (audio principal: Oui/Non)

## 🎨 Design et UX

### Interface Administrateur
- **Design moderne** : Gradients, ombres, animations fluides
- **Responsive** : Adaptation parfaite mobile/desktop/tablette
- **Intuitif** : Navigation claire et actions logiques
- **Professionnel** : Couleurs cohérentes et typographie soignée

### Interface Utilisateur
- **Accessible** : Pas besoin de compte pour soumettre
- **Visuel** : Aperçu immédiat des fichiers uploadés
- **Guidé** : Instructions claires et feedback utilisateur
- **Mobile-first** : Optimisé pour les appareils mobiles

## 🔧 Dépendances

### PhpSpreadsheet
Pour la génération de fichiers Excel natifs :
```bash
composer require phpoffice/phpspreadsheet
```

### Extensions PHP Requises
- `pdo` : Connexion base de données
- `pdo_mysql` : Driver MySQL
- `json` : Gestion des données JSON
- `fileinfo` : Vérification des fichiers uploadés
- `gd` : Traitement d'images (optionnel)
- `zip` : Génération des fichiers Excel

---

**GriotBook** - Partagez votre histoire, laissez votre empreinte.

*Version 2.0 - Mise à jour avec export Excel avancé et filtrage multi-critères*
