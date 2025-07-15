<?php
if (!$user->isLoggedIn()) {
    redirect('index.php?page=login');
}

$currentUser = $user->getCurrentUser();
$contacts = $contact->getUserContacts($currentUser['id']);
$allUsers = $user->getAllUsers();

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_contact':
                $contactUserId = $_POST['contact_user_id'];
                $nickname = $_POST['nickname'] ?? '';
                $result = $contact->addContact($currentUser['id'], $contactUserId, $nickname);
                if ($result['success']) {
                    $successMessage = 'Contact ajouté avec succès';
                } else {
                    $errorMessage = implode(', ', $result['errors']);
                }
                break;
                
            case 'add_external_contact':
                $firstName = $_POST['first_name'] ?? '';
                $lastName = $_POST['last_name'] ?? '';
                $phoneNumber = $_POST['phone_number'] ?? '';
                $nickname = $_POST['nickname'] ?? '';
                
                if (empty($firstName) || empty($lastName) || empty($phoneNumber)) {
                    $errorMessage = 'Tous les champs sont obligatoires';
                } else {
                    $result = $contact->addExternalContact($currentUser['id'], $firstName, $lastName, $phoneNumber, $nickname);
                    if ($result['success']) {
                        $successMessage = 'Contact externe ajouté avec succès';
                    } else {
                        $errorMessage = implode(', ', $result['errors']);
                    }
                }
                break;
                
            case 'remove_contact':
                $contactId = $_POST['contact_id'];
                if ($contact->removeContactById($contactId)) {
                    $successMessage = 'Contact supprimé avec succès';
                } else {
                    $errorMessage = 'Erreur lors de la suppression';
                }
                break;
                
            case 'toggle_favorite':
                $contactId = $_POST['contact_id'];
                if ($contact->toggleFavoriteById($contactId)) {
                    $successMessage = 'Statut favori mis à jour';
                } else {
                    $errorMessage = 'Erreur lors de la mise à jour';
                }
                break;
                
            case 'update_nickname':
                $contactId = $_POST['contact_user_id'];
                $nickname = $_POST['nickname'];
                if ($contact->updateNicknameById($contactId, $nickname)) {
                    $successMessage = 'Surnom mis à jour';
                } else {
                    $errorMessage = 'Erreur lors de la mise à jour';
                }
                break;
        }
        // Recharger les contacts après modification
        $contacts = $contact->getUserContacts($currentUser['id']);
    }
}

// Recherche de contacts
$searchTerm = $_GET['search'] ?? '';
if (!empty($searchTerm)) {
    $contacts = array_filter($contacts, function($contact) use ($searchTerm) {
        $contactUser = $user->getUserById($contact['contact_user_id']);
        $displayName = $contact['nickname'] ?: ($contactUser['profile']['first_name'] . ' ' . $contactUser['profile']['last_name']);
        return stripos($displayName, $searchTerm) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contacts - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .contact-card { transition: all 0.3s ease; }
        .contact-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .favorite-contact { background: #fff3cd; }
        .contact-avatar { width: 50px; height: 50px; border-radius: 50%; background: #25d366; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .action-buttons .btn { margin: 2px; }
        
        /* Styles pour les contacts cliquables */
        .contact-link {
            display: block;
            transition: all 0.2s ease;
        }
        
        .contact-link:hover {
            text-decoration: none !important;
            color: #25d366 !important;
        }
        
        .contact-card:hover .contact-link {
            color: #25d366 !important;
        }
        
        /* Indicateur visuel pour les contacts cliquables */
        .contact-card:has(.contact-link) {
            cursor: pointer;
            border-left: 3px solid #25d366;
        }
        
        /* Style pour les contacts externes (non cliquables) */
        .contact-card:not(:has(.contact-link)) {
            border-left: 3px solid #6c757d;
        }
    </style>
</head>
<body>
<?php include 'pages/_navbar.php'; ?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-address-book"></i> Mes Contacts</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="fas fa-plus"></i> Ajouter un contact
                    </button>
                </div>
                <div class="card-body">
                    <!-- Barre de recherche -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="get" class="d-flex">
                                <input type="hidden" name="page" value="contacts">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher un contact..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <button type="submit" class="btn btn-outline-secondary ms-2"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Messages de succès/erreur -->
                    <?php if (isset($successMessage)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Liste des contacts -->
                    <div class="row">
                        <?php if (empty($contacts)): ?>
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun contact trouvé</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                    <i class="fas fa-plus"></i> Ajouter votre premier contact
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($contacts as $contactData): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card contact-card <?php echo $contactData['is_favorite'] ? 'favorite-contact' : ''; ?>">
                                        <div class="card-body">
                                            <?php if ($contactData['type'] === 'user' && !empty($contactData['contact_user_id'])): ?>
                                                <!-- Contact utilisateur - Carte entière cliquable -->
                                                <a href="index.php?page=messages&conversation=<?php echo $contactData['contact_user_id']; ?>&type=user" 
                                                   class="text-decoration-none text-dark contact-link">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="contact-avatar me-3">
                                                            <?php 
                                                            $contactUser = $user->getUserById($contactData['contact_user_id']);
                                                            if ($contactUser && isset($contactUser['profile'])) {
                                                                $firstName = $contactUser['profile']['first_name'];
                                                                $lastName = $contactUser['profile']['last_name'];
                                                                echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                                            } else {
                                                                echo '?';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                <?php 
                                                                $contactUser = $user->getUserById($contactData['contact_user_id']);
                                                                if ($contactUser && isset($contactUser['profile'])) {
                                                                    $displayName = $contactData['nickname'] ?: $contactUser['profile']['first_name'] . ' ' . $contactUser['profile']['last_name'];
                                                                    echo htmlspecialchars($displayName);
                                                                } else {
                                                                    echo htmlspecialchars('Utilisateur inconnu');
                                                                }
                                                                ?>
                                                                <?php if ($contactData['is_favorite']): ?>
                                                                    <i class="fas fa-star text-warning"></i>
                                                                <?php endif; ?>
                                                            </h6>
                                                            <small class="text-muted">
                                                                <?php 
                                                                $contactUser = $user->getUserById($contactData['contact_user_id']);
                                                                if ($contactUser) {
                                                                    echo '@' . htmlspecialchars($contactUser['username']);
                                                                } else {
                                                                    echo 'Utilisateur supprimé';
                                                                }
                                                                ?>
                                                            </small>
                                                        </div>
                                                        <div class="ms-2">
                                                            <i class="fas fa-comment text-success"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php else: ?>
                                                <!-- Contact externe - Affichage normal -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="contact-avatar me-3">
                                                        <?php 
                                                        $firstName = $contactData['first_name'];
                                                        $lastName = $contactData['last_name'];
                                                        echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                                        ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">
                                                            <?php 
                                                            $displayName = $contactData['nickname'] ?: $contactData['first_name'] . ' ' . $contactData['last_name'];
                                                            echo htmlspecialchars($displayName);
                                                            ?>
                                                            <?php if ($contactData['is_favorite']): ?>
                                                                <i class="fas fa-star text-warning"></i>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($contactData['phone_number']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="action-buttons d-flex flex-wrap">
                                                <?php if ($contactData['type'] === 'external'): ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-phone"></i> Appeler
                                                    </button>
                                                <?php else: ?>
                                                    <a href="index.php?page=messages&conversation=<?php echo $contactData['contact_user_id']; ?>&type=user" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-comment"></i> Chat
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-warning btn-sm" 
                                                        onclick="toggleFavorite(<?php echo $contactData['id']; ?>)">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                                
                                                <button class="btn btn-info btn-sm" 
                                                        onclick="editNickname(<?php echo $contactData['id']; ?>, '<?php echo htmlspecialchars($contactData['nickname']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="removeContact(<?php echo $contactData['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter un contact -->
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Ajouter un contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs" id="contactTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-contact" type="button" role="tab">
                            <i class="fas fa-user"></i> Utilisateur existant
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="external-tab" data-bs-toggle="tab" data-bs-target="#external-contact" type="button" role="tab">
                            <i class="fas fa-phone"></i> Contact externe
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="contactTabsContent">
                    <!-- Onglet Utilisateur existant -->
                    <div class="tab-pane fade show active" id="user-contact" role="tabpanel">
                        <form method="post">
                            <input type="hidden" name="action" value="add_contact">
                            
                            <div class="mb-3">
                                <label for="contact_user_id" class="form-label">Utilisateur</label>
                                <select name="contact_user_id" id="contact_user_id" class="form-select" required>
                                    <option value="">Sélectionner un utilisateur</option>
                                    <?php foreach ($allUsers as $userData): ?>
                                        <?php if ($userData['id'] !== $currentUser['id'] && !$contact->isContact($currentUser['id'], $userData['id'])): ?>
                                            <option value="<?php echo $userData['id']; ?>">
                                                <?php echo htmlspecialchars($userData['profile']['first_name'] . ' ' . $userData['profile']['last_name']); ?> 
                                                (@<?php echo htmlspecialchars($userData['username']); ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nickname" class="form-label">Surnom (optionnel)</label>
                                <input type="text" name="nickname" id="nickname" class="form-control" placeholder="Surnom personnalisé">
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Onglet Contact externe -->
                    <div class="tab-pane fade" id="external-contact" role="tabpanel">
                        <form method="post">
                            <input type="hidden" name="action" value="add_external_contact">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">Prénom *</label>
                                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Nom *</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Numéro de téléphone *</label>
                                <input type="tel" name="phone_number" id="phone_number" class="form-control" placeholder="771234567" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="external_nickname" class="form-label">Surnom (optionnel)</label>
                                <input type="text" name="nickname" id="external_nickname" class="form-control" placeholder="Surnom personnalisé">
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Éditer le surnom -->
<div class="modal fade" id="editNicknameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le surnom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_nickname">
                    <input type="hidden" name="contact_user_id" id="edit_contact_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_nickname" class="form-label">Nouveau surnom</label>
                        <input type="text" name="nickname" id="edit_nickname" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleFavorite(contactId) {
    if (confirm('Modifier le statut favori de ce contact ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="toggle_favorite">
            <input type="hidden" name="contact_id" value="${contactId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function editNickname(contactId, currentNickname) {
    document.getElementById('edit_contact_user_id').value = contactId;
    document.getElementById('edit_nickname').value = currentNickname;
    new bootstrap.Modal(document.getElementById('editNicknameModal')).show();
}

function removeContact(contactId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="remove_contact">
            <input type="hidden" name="contact_id" value="${contactId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html> 