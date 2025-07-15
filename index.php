<?php
/**
 * Point d'entrée principal de l'application
 * Plateforme de Discussion en Ligne - Projet DSS XML
 */

// Inclure la configuration
require_once 'config/config.php';

// Inclure les classes nécessaires
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Message.php';
require_once 'includes/Group.php';
require_once 'includes/Contact.php';

// Initialiser les objets
$user = new User();
$message = new Message();
$group = new Group();
$contact = new Contact();

// Définir le mode développement
define('DEVELOPMENT_MODE', true);

// Gestion des erreurs
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fonction pour afficher les messages d'erreur
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Fonction pour afficher les messages de succès
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Traitement des actions AJAX
if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['action']) {
        case 'login':
            if (isset($_POST['username']) && isset($_POST['password'])) {
                if ($user->authenticate($_POST['username'], $_POST['password'])) {
                    $response = ['success' => true, 'redirect' => 'index.php?page=dashboard'];
                } else {
                    $response = ['success' => false, 'message' => getErrorMessage('invalid_credentials')];
                }
            }
            break;
            
        case 'register':
            if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'first_name' => $_POST['first_name'] ?? '',
                    'last_name' => $_POST['last_name'] ?? ''
                ];
                
                $result = $user->createUser($userData);
                $response = $result;
            }
            break;
            
        case 'send_message':
            if ($user->isLoggedIn() && isset($_POST['recipient_id']) && isset($_POST['content'])) {
                $senderId = $user->getCurrentUserId();
                $recipientType = $_POST['recipient_type'] ?? 'user';
                $recipientId = $_POST['recipient_id'];
                $content = $_POST['content'];
                
                $result = $message->sendMessage($senderId, $recipientType, $recipientId, $content);
                $response = $result;
            }
            break;
            
        case 'create_group':
            if ($user->isLoggedIn() && isset($_POST['name']) && isset($_POST['description'])) {
                $groupData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'created_by' => $user->getCurrentUserId(),
                    'privacy' => $_POST['privacy'] ?? 'public'
                ];
                
                $result = $group->createGroup($groupData);
                $response = $result;
            }
            break;
            
        case 'add_contact':
            if ($user->isLoggedIn() && isset($_POST['contact_user_id'])) {
                $userId = $user->getCurrentUserId();
                $contactUserId = $_POST['contact_user_id'];
                $nickname = $_POST['nickname'] ?? null;
                
                $result = $contact->addContact($userId, $contactUserId, $nickname);
                $response = $result;
            }
            break;
    }
    
    // Réponse JSON pour les requêtes AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Traitement des actions GET pour les modals
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_members':
            if ($user->isLoggedIn() && isset($_GET['group_id'])) {
                $groupId = $_GET['group_id'];
                $members = $group->getGroupMembers($groupId);
                
                // Générer le HTML pour la liste des membres
                $html = '<div class="row">';
                if (empty($members)) {
                    $html .= '<div class="col-12 text-center py-4"><p class="text-muted">Aucun membre dans ce groupe</p></div>';
                } else {
                    foreach ($members as $member) {
                        $userInfo = $member['user_info'];
                        
                        // Vérifier si l'utilisateur existe et a des données valides
                        if ($userInfo && isset($userInfo['profile']) && isset($userInfo['profile']['first_name']) && isset($userInfo['profile']['last_name'])) {
                            $firstName = $userInfo['profile']['first_name'] ?? '';
                            $lastName = $userInfo['profile']['last_name'] ?? '';
                            $username = $userInfo['username'] ?? '';
                            $roleBadge = $member['role'] === 'admin' ? 'badge bg-danger' : 'badge bg-secondary';
                            
                            $html .= '
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center p-2 border rounded">
                                    <div class="avatar me-3">
                                        ' . strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)) . '
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">' . htmlspecialchars($firstName . ' ' . $lastName) . '</h6>
                                        <small class="text-muted">@' . htmlspecialchars($username) . '</small>
                                    </div>
                                    <span class="' . $roleBadge . '">' . ucfirst($member['role']) . '</span>
                                </div>
                            </div>';
                        }
                    }
                }
                $html .= '</div>';
                
                echo $html;
                exit;
            }
            break;
            
        case 'show_add_members':
            if ($user->isLoggedIn() && isset($_GET['group_id'])) {
                $groupId = $_GET['group_id'];
                $currentUser = $user->getCurrentUser();
                $contacts = $contact->getUserContacts($currentUser['id']);
                $groupInfo = $group->getGroupById($groupId);
                
                // Filtrer les contacts qui ne sont pas déjà dans le groupe
                $availableContacts = [];
                foreach ($contacts as $contactData) {
                    if (!$group->isMember($groupId, $contactData['contact_user_id'])) {
                        $availableContacts[] = $contactData;
                    }
                }
                
                // Générer le HTML pour le modal d'ajout de membres
                $html = '<form method="post" id="addMembersForm">';
                $html .= '<input type="hidden" name="action" value="add_members_to_group">';
                $html .= '<input type="hidden" name="group_id" value="' . $groupId . '">';
                
                if (empty($availableContacts)) {
                    $html .= '<div class="text-center py-4">';
                    $html .= '<i class="fas fa-users fa-2x text-muted mb-3"></i>';
                    $html .= '<p class="text-muted">Tous vos contacts sont déjà dans ce groupe</p>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="mb-3">';
                    $html .= '<label class="form-label">Sélectionner les contacts à ajouter :</label>';
                    $html .= '<div class="row">';
                    
                    foreach ($availableContacts as $contactData) {
                        $contactUser = $user->getUserById($contactData['contact_user_id']);
                        
                        // Vérifier si l'utilisateur existe et a des données valides
                        if ($contactUser && isset($contactUser['profile']) && isset($contactUser['profile']['first_name']) && isset($contactUser['profile']['last_name'])) {
                            $firstName = $contactUser['profile']['first_name'] ?? '';
                            $lastName = $contactUser['profile']['last_name'] ?? '';
                            $username = $contactUser['username'] ?? '';
                            $displayName = $contactData['nickname'] ?: ($firstName . ' ' . $lastName);
                            
                            $html .= '
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="selected_contacts[]" 
                                           value="' . $contactData['contact_user_id'] . '" id="contact_' . $contactData['contact_user_id'] . '">
                                    <label class="form-check-label" for="contact_' . $contactData['contact_user_id'] . '">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                ' . strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)) . '
                                            </div>
                                            <div>
                                                <strong>' . htmlspecialchars($displayName) . '</strong><br>
                                                <small class="text-muted">@' . htmlspecialchars($username) . '</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>';
                        }
                    }
                    
                    $html .= '</div>';
                    $html .= '</div>';
                    
                    $html .= '<div class="text-end">';
                    $html .= '<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>';
                    $html .= '<button type="submit" class="btn btn-primary">Ajouter les membres</button>';
                    $html .= '</div>';
                }
                
                $html .= '</form>';
                
                echo $html;
                exit;
            }
            break;
    }
}

// Déterminer la page à afficher
$page = $_GET['page'] ?? 'home';

// Vérifier l'authentification pour les pages protégées
$protectedPages = ['dashboard', 'messages', 'groups', 'contacts', 'profile', 'settings'];
if (in_array($page, $protectedPages) && !$user->isLoggedIn()) {
    redirect('index.php?page=login');
}

// Inclure la page appropriée
$pageFile = "pages/$page.php";
if (file_exists($pageFile)) {
    include $pageFile;
} else {
    // Page 404
    include 'pages/404.php';
}
?> 