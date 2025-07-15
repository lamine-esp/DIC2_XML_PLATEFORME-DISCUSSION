<?php
/**
 * Configuration principale de l'application
 * Plateforme de Discussion en Ligne - Projet DSS XML
 */

// Configuration de base
define('APP_NAME', 'Plateforme de Discussion');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost:8000');

// Chemins de l'application
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuration de la base de données XML
define('USERS_XML', DATA_PATH . '/users.xml');
define('MESSAGES_XML', DATA_PATH . '/messages.xml');
define('GROUPS_XML', DATA_PATH . '/groups.xml');
define('CONTACTS_XML', DATA_PATH . '/contacts.xml');

// Configuration de sécurité
define('SESSION_NAME', 'messaging_session');
define('SESSION_LIFETIME', 3600); // 1 heure
define('PASSWORD_COST', 12); // Coût du hachage des mots de passe

// Configuration des uploads
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);

// Configuration des messages
define('MESSAGES_PER_PAGE', 20);
define('MAX_MESSAGE_LENGTH', 1000);

// Configuration des groupes
define('MAX_GROUP_MEMBERS', 100);
define('MAX_GROUP_NAME_LENGTH', 50);

// Configuration des utilisateurs
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 20);
define('MIN_PASSWORD_LENGTH', 8);

// Configuration des notifications
define('NOTIFICATION_TYPES', [
    'new_message' => 'Nouveau message',
    'group_invitation' => 'Invitation de groupe',
    'contact_request' => 'Demande de contact',
    'file_shared' => 'Fichier partagé'
]);

// Configuration des thèmes
define('AVAILABLE_THEMES', [
    'light' => 'Clair',
    'dark' => 'Sombre',
    'auto' => 'Automatique'
]);

// Configuration des langues
define('AVAILABLE_LANGUAGES', [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español'
]);

// Configuration des rôles
define('USER_ROLES', [
    'user' => 'Utilisateur',
    'admin' => 'Administrateur',
    'moderator' => 'Modérateur'
]);

// Configuration des statuts
define('USER_STATUSES', [
    'online' => 'En ligne',
    'offline' => 'Hors ligne',
    'away' => 'Absent',
    'busy' => 'Occupé'
]);

// Configuration des types de messages
define('MESSAGE_TYPES', [
    'text' => 'Texte',
    'file' => 'Fichier',
    'image' => 'Image',
    'system' => 'Système'
]);

// Configuration des types de destinataires
define('RECIPIENT_TYPES', [
    'user' => 'Utilisateur',
    'group' => 'Groupe'
]);

// Configuration des erreurs
define('ERROR_MESSAGES', [
    'invalid_credentials' => 'Nom d\'utilisateur ou mot de passe incorrect',
    'user_not_found' => 'Utilisateur non trouvé',
    'user_already_exists' => 'Cet utilisateur existe déjà',
    'invalid_email' => 'Adresse email invalide',
    'password_too_short' => 'Le mot de passe doit contenir au moins ' . MIN_PASSWORD_LENGTH . ' caractères',
    'username_too_short' => 'Le nom d\'utilisateur doit contenir au moins ' . MIN_USERNAME_LENGTH . ' caractères',
    'file_too_large' => 'Le fichier est trop volumineux (max ' . (MAX_FILE_SIZE / 1024 / 1024) . ' MB)',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'group_not_found' => 'Groupe non trouvé',
    'not_group_member' => 'Vous n\'êtes pas membre de ce groupe',
    'not_group_admin' => 'Vous n\'êtes pas administrateur de ce groupe',
    'message_not_found' => 'Message non trouvé',
    'unauthorized' => 'Accès non autorisé'
]);

// Configuration des succès
define('SUCCESS_MESSAGES', [
    'user_created' => 'Compte créé avec succès',
    'user_updated' => 'Profil mis à jour avec succès',
    'message_sent' => 'Message envoyé avec succès',
    'group_created' => 'Groupe créé avec succès',
    'group_updated' => 'Groupe mis à jour avec succès',
    'member_added' => 'Membre ajouté au groupe',
    'member_removed' => 'Membre retiré du groupe',
    'contact_added' => 'Contact ajouté',
    'contact_removed' => 'Contact supprimé',
    'settings_updated' => 'Paramètres mis à jour'
]);

// Initialisation de la session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Configuration des fuseaux horaires
date_default_timezone_set('Africa/Dakar');

// Configuration des erreurs (en développement)
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Fonction utilitaire pour obtenir la configuration
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Fonction utilitaire pour obtenir un message d'erreur
function getErrorMessage($key) {
    return ERROR_MESSAGES[$key] ?? 'Erreur inconnue';
}

// Fonction utilitaire pour obtenir un message de succès
function getSuccessMessage($key) {
    return SUCCESS_MESSAGES[$key] ?? 'Opération réussie';
} 