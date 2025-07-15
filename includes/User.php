<?php
/**
 * Classe User pour gérer les utilisateurs
 * Gère l'authentification, les profils et les paramètres
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

class User {
    private $db;
    private $userData;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Authentifie un utilisateur
     */
    public function authenticate($username, $password) {
        $users = $this->db->getUsersXml();
        
        foreach ($users->user as $user) {
            if ((string)$user->username === $username) {
                if (password_verify($password, (string)$user->password_hash)) {
                    // Mettre à jour la dernière connexion
                    $user->last_login = date('Y-m-d\TH:i:s');
                    $this->db->saveXml($users, USERS_XML);
                    
                    // Créer la session
                    $_SESSION['user_id'] = (string)$user['id'];
                    $_SESSION['username'] = (string)$user->username;
                    $_SESSION['role'] = (string)$user['role'];
                    $_SESSION['login_time'] = time();
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Obtient l'ID de l'utilisateur connecté
     */
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Obtient les données de l'utilisateur connecté
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->getUserById($_SESSION['user_id']);
    }
    
    /**
     * Obtient un utilisateur par ID
     */
    public function getUserById($userId) {
        $users = $this->db->getUsersXml();
        $user = $this->db->findById($users, $userId);
        
        if ($user) {
            return $this->formatUserData($user);
        }
        
        return null;
    }
    
    /**
     * Obtient un utilisateur par nom d'utilisateur
     */
    public function getUserByUsername($username) {
        $users = $this->db->getUsersXml();
        $results = $this->db->findBy($users, ['username' => $username]);
        
        if (!empty($results)) {
            return $this->formatUserData($results[0]);
        }
        
        return null;
    }
    
    /**
     * Obtient un utilisateur par email
     */
    public function getUserByEmail($email) {
        $users = $this->db->getUsersXml();
        $results = $this->db->findBy($users, ['email' => $email]);
        
        if (!empty($results)) {
            return $this->formatUserData($results[0]);
        }
        
        return null;
    }
    
    /**
     * Crée un nouvel utilisateur
     */
    public function createUser($userData) {
        // Validation des données
        $errors = $this->validateUserData($userData);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Vérifier si l'utilisateur existe déjà
        if ($this->getUserByUsername($userData['username'])) {
            return ['success' => false, 'errors' => ['username' => getErrorMessage('user_already_exists')]];
        }
        
        if ($this->getUserByEmail($userData['email'])) {
            return ['success' => false, 'errors' => ['email' => 'Cette adresse email est déjà utilisée']];
        }
        
        $users = $this->db->getUsersXml();
        $newId = $this->db->generateId($users);
        
        // Créer le nouvel utilisateur
        $user = $users->addChild('user');
        $user->addAttribute('id', $newId);
        $user->addAttribute('status', 'offline');
        $user->addAttribute('role', 'user');
        $user->addAttribute('is_active', 'true');
        $user->addAttribute('is_verified', 'false');
        
        $user->addChild('username', htmlspecialchars($userData['username']));
        $user->addChild('email', htmlspecialchars($userData['email']));
        $user->addChild('password_hash', password_hash($userData['password'], PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]));
        
        // Profil
        $profile = $user->addChild('profile');
        $profile->addChild('first_name', htmlspecialchars($userData['first_name']));
        $profile->addChild('last_name', htmlspecialchars($userData['last_name']));
        $profile->addChild('avatar', '');
        $profile->addChild('bio', '');
        
        // Paramètres par défaut
        $settings = $user->addChild('settings');
        $settings->addChild('notifications', 'true');
        $settings->addChild('theme', 'light');
        $settings->addChild('language', 'fr');
        $settings->addChild('privacy_level', 'public');
        
        $user->addChild('created_at', date('Y-m-d\TH:i:s'));
        
        // Sauvegarder
        if ($this->db->saveXml($users, USERS_XML)) {
            return ['success' => true, 'user_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la création du compte']];
        }
    }
    
    /**
     * Met à jour le profil utilisateur
     */
    public function updateProfile($userId, $profileData) {
        $users = $this->db->getUsersXml();
        $user = $this->db->findById($users, $userId);
        
        if (!$user) {
            return ['success' => false, 'errors' => ['user' => getErrorMessage('user_not_found')]];
        }
        
        // Mettre à jour le profil
        if (isset($profileData['first_name'])) {
            $user->profile->first_name = htmlspecialchars($profileData['first_name']);
        }
        
        if (isset($profileData['last_name'])) {
            $user->profile->last_name = htmlspecialchars($profileData['last_name']);
        }
        
        if (isset($profileData['bio'])) {
            $user->profile->bio = htmlspecialchars($profileData['bio']);
        }
        
        if (isset($profileData['avatar'])) {
            $user->profile->avatar = htmlspecialchars($profileData['avatar']);
        }
        
        if ($this->db->saveXml($users, USERS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la mise à jour']];
        }
    }
    
    /**
     * Met à jour les paramètres utilisateur
     */
    public function updateSettings($userId, $settingsData) {
        $users = $this->db->getUsersXml();
        $user = $this->db->findById($users, $userId);
        
        if (!$user) {
            return ['success' => false, 'errors' => ['user' => getErrorMessage('user_not_found')]];
        }
        
        // Mettre à jour les paramètres
        if (isset($settingsData['notifications'])) {
            $user->settings->notifications = $settingsData['notifications'] ? 'true' : 'false';
        }
        
        if (isset($settingsData['theme'])) {
            $user->settings->theme = htmlspecialchars($settingsData['theme']);
        }
        
        if (isset($settingsData['language'])) {
            $user->settings->language = htmlspecialchars($settingsData['language']);
        }
        
        if (isset($settingsData['privacy_level'])) {
            $user->settings->privacy_level = htmlspecialchars($settingsData['privacy_level']);
        }
        
        if ($this->db->saveXml($users, USERS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la mise à jour']];
        }
    }
    
    /**
     * Change le mot de passe
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $users = $this->db->getUsersXml();
        $user = $this->db->findById($users, $userId);
        
        if (!$user) {
            return ['success' => false, 'errors' => ['user' => getErrorMessage('user_not_found')]];
        }
        
        // Vérifier l'ancien mot de passe
        if (!password_verify($currentPassword, (string)$user->password_hash)) {
            return ['success' => false, 'errors' => ['current_password' => 'Mot de passe actuel incorrect']];
        }
        
        // Valider le nouveau mot de passe
        if (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
            return ['success' => false, 'errors' => ['new_password' => getErrorMessage('password_too_short')]];
        }
        
        // Mettre à jour le mot de passe
        $user->password_hash = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]);
        
        if ($this->db->saveXml($users, USERS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors du changement de mot de passe']];
        }
    }
    
    /**
     * Met à jour le statut de l'utilisateur
     */
    public function updateStatus($userId, $status) {
        $users = $this->db->getUsersXml();
        $user = $this->db->findById($users, $userId);
        
        if (!$user) {
            return false;
        }
        
        $user['status'] = $status;
        return $this->db->saveXml($users, USERS_XML);
    }
    
    /**
     * Obtient tous les utilisateurs (pour l'administration)
     */
    public function getAllUsers() {
        $users = $this->db->getUsersXml();
        $userList = [];
        
        foreach ($users->user as $user) {
            $userList[] = $this->formatUserData($user);
        }
        
        return $userList;
    }
    
    /**
     * Recherche des utilisateurs
     */
    public function searchUsers($query) {
        $users = $this->db->getUsersXml();
        $results = [];
        
        foreach ($users->user as $user) {
            $username = strtolower((string)$user->username);
            $firstName = strtolower((string)$user->profile->first_name);
            $lastName = strtolower((string)$user->profile->last_name);
            $searchQuery = strtolower($query);
            
            if (strpos($username, $searchQuery) !== false ||
                strpos($firstName, $searchQuery) !== false ||
                strpos($lastName, $searchQuery) !== false) {
                $results[] = $this->formatUserData($user);
            }
        }
        
        return $results;
    }
    
    /**
     * Valide les données utilisateur
     */
    private function validateUserData($userData) {
        $errors = [];
        
        // Validation du nom d'utilisateur
        if (empty($userData['username'])) {
            $errors['username'] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($userData['username']) < MIN_USERNAME_LENGTH) {
            $errors['username'] = getErrorMessage('username_too_short');
        } elseif (strlen($userData['username']) > MAX_USERNAME_LENGTH) {
            $errors['username'] = 'Le nom d\'utilisateur est trop long';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            $errors['username'] = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores';
        }
        
        // Validation de l'email
        if (empty($userData['email'])) {
            $errors['email'] = 'L\'adresse email est requise';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = getErrorMessage('invalid_email');
        }
        
        // Validation du mot de passe
        if (empty($userData['password'])) {
            $errors['password'] = 'Le mot de passe est requis';
        } elseif (strlen($userData['password']) < MIN_PASSWORD_LENGTH) {
            $errors['password'] = getErrorMessage('password_too_short');
        }
        
        // Validation du prénom
        if (empty($userData['first_name'])) {
            $errors['first_name'] = 'Le prénom est requis';
        }
        
        // Validation du nom
        if (empty($userData['last_name'])) {
            $errors['last_name'] = 'Le nom est requis';
        }
        
        return $errors;
    }
    
    /**
     * Formate les données utilisateur
     */
    private function formatUserData($user) {
        return [
            'id' => (string)$user['id'],
            'username' => (string)$user->username,
            'email' => (string)$user->email,
            'status' => (string)$user['status'],
            'role' => (string)$user['role'],
            'is_active' => (string)$user['is_active'] === 'true',
            'is_verified' => (string)$user['is_verified'] === 'true',
            'profile' => [
                'first_name' => (string)$user->profile->first_name,
                'last_name' => (string)$user->profile->last_name,
                'avatar' => (string)$user->profile->avatar,
                'bio' => (string)$user->profile->bio
            ],
            'settings' => [
                'notifications' => (string)$user->settings->notifications === 'true',
                'theme' => (string)$user->settings->theme,
                'language' => (string)$user->settings->language,
                'privacy_level' => (string)$user->settings->privacy_level
            ],
            'created_at' => (string)$user->created_at,
            'last_login' => (string)$user->last_login
        ];
    }
} 