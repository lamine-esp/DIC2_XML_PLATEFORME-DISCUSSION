<?php
if (!$user->isLoggedIn()) {
    redirect('index.php?page=login');
}
$currentUser = $user->getCurrentUser();
$userGroups = $group->getUserGroups($currentUser['id']);
$publicGroups = $group->getPublicGroups();

// Gestion des actions AJAX
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_members':
            $groupId = $_GET['group_id'] ?? '';
            if ($groupId && $group->isMember($groupId, $currentUser['id'])) {
                $members = $group->getGroupMembers($groupId);
                include 'pages/_group_members.php';
            } else {
                echo '<div class="text-center py-4"><p class="text-muted">Accès non autorisé</p></div>';
            }
            exit;
            
        case 'show_add_members':
            $groupId = $_GET['group_id'] ?? '';
            if ($groupId && $group->isMember($groupId, $currentUser['id'])) {
                $userContacts = $contact->getUserContacts($currentUser['id']);
                $allUsers = $user->getAllUsers();
                include 'pages/_add_members_to_group.php';
            } else {
                echo '<div class="text-center py-4"><p class="text-muted">Vous devez être membre du groupe pour ajouter des contacts</p></div>';
            }
            exit;
    }
}

// Traitement des actions
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_group':
                $result = $group->createGroup([
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'created_by' => $currentUser['id'],
                    'privacy' => $_POST['privacy'] ?? 'public'
                ]);
                if ($result['success']) {
                    $successMessage = 'Groupe créé avec succès !';
                } else {
                    $errorMessage = 'Erreur : ' . implode(', ', $result['errors']);
                }
                break;
                
            case 'join_group':
                $result = $group->addMember($_POST['group_id'], $currentUser['id']);
                if ($result['success']) {
                    $successMessage = 'Vous avez rejoint le groupe.';
                } else {
                    $errorMessage = 'Erreur : ' . implode(', ', $result['errors']);
                }
                break;
                
            case 'quit_group':
                $result = $group->removeMember($_POST['group_id'], $currentUser['id'], $currentUser['id']);
                if ($result['success']) {
                    $successMessage = 'Vous avez quitté le groupe.';
                } else {
                    $errorMessage = 'Erreur : ' . implode(', ', $result['errors']);
                }
                break;
                
            case 'delete_group':
                $result = $group->deleteGroup($_POST['group_id'], $currentUser['id']);
                if ($result['success']) {
                    $successMessage = 'Groupe supprimé.';
                } else {
                    $errorMessage = 'Erreur : ' . implode(', ', $result['errors']);
                }
                break;

            case 'add_members_to_group':
                $groupId = $_POST['group_id'];
                $selectedContacts = $_POST['selected_contacts'] ?? [];
                
                if (empty($selectedContacts)) {
                    $errorMessage = 'Veuillez sélectionner au moins un utilisateur à ajouter.';
                } else {
                    $result = $group->addMultipleMembers($groupId, $selectedContacts, $currentUser['id']);
                    if ($result['success']) {
                        $successMessage = $result['success_count'] . ' utilisateur(s) ajouté(s) au groupe.';
                    } else {
                        $errorMessage = 'Erreur lors de l\'ajout des utilisateurs.';
                    }
                }
                break;
        }
    }
    // Rafraîchir les listes après action
    $userGroups = $group->getUserGroups($currentUser['id']);
    $publicGroups = $group->getPublicGroups();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Groupes - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .group-card { transition: all 0.3s ease; }
        .group-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .group-avatar { width: 60px; height: 60px; border-radius: 50%; background: #25d366; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em; }
        .member-count { background: #e9ecef; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; }
        .privacy-badge { font-size: 0.7em; }
    </style>
</head>
<body>
<?php include 'pages/_navbar.php'; ?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Mes Groupes</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                        <i class="fas fa-plus"></i> Créer un groupe
                    </button>
                </div>
                <div class="card-body">
                    <!-- Messages de succès/erreur -->
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Mes groupes -->
                    <h6 class="mb-3"><i class="fas fa-user-friends"></i> Mes groupes (<?php echo count($userGroups); ?>)</h6>
                    <div class="row">
                        <?php if (empty($userGroups)): ?>
                            <div class="col-12 text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Vous n'avez rejoint aucun groupe</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                                    <i class="fas fa-plus"></i> Créer votre premier groupe
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userGroups as $g): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card group-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="group-avatar me-3">
                                                    <?php echo strtoupper(substr($g['name'], 0, 2)); ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($g['name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($g['description']); ?></small>
                                                    <div class="mt-1">
                                                        <span class="member-count">
                                                            <i class="fas fa-users"></i> <?php echo count($g['members']); ?> membres
                                                        </span>
                                                        <span class="badge <?php echo $g['settings']['privacy'] === 'public' ? 'bg-success' : 'bg-warning'; ?> privacy-badge ms-2">
                                                            <?php echo $g['settings']['privacy'] === 'public' ? 'Public' : 'Privé'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex flex-wrap gap-1">
                                                <a href="index.php?page=messages&conversation=<?php echo $g['id']; ?>&type=group" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-comments"></i> Conversation
                                                </a>
                                                
                                                <button class="btn btn-info btn-sm" 
                                                        onclick="viewMembers(<?php echo $g['id']; ?>, '<?php echo htmlspecialchars($g['name']); ?>')">
                                                    <i class="fas fa-users"></i> Membres
                                                </button>
                                                
                                                <?php if ($group->isAdmin($g['id'], $currentUser['id'])): ?>
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="editGroup(<?php echo $g['id']; ?>, '<?php echo htmlspecialchars($g['name']); ?>', '<?php echo htmlspecialchars($g['description']); ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-info btn-sm" 
                                                        onclick="addMembersToGroup(<?php echo $g['id']; ?>, '<?php echo htmlspecialchars($g['name']); ?>')">
                                                    <i class="fas fa-user-plus"></i> Ajouter des contacts
                                                </button>
                                                
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="quitGroup(<?php echo $g['id']; ?>)">
                                                    <i class="fas fa-sign-out-alt"></i> Quitter
                                                </button>
                                                
                                                <?php if ($group->isAdmin($g['id'], $currentUser['id']) && $g['settings']['privacy'] === 'public'): ?>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="deleteGroup(<?php echo $g['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Groupes publics -->
                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-globe"></i> Groupes publics disponibles</h6>
                    <div class="row">
                        <?php if (empty($publicGroups)): ?>
                            <div class="col-12 text-center py-3">
                                <p class="text-muted">Aucun groupe public disponible</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($publicGroups as $g): ?>
                                <?php if (!$group->isMember($g['id'], $currentUser['id'])): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card group-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="group-avatar me-3">
                                                        <?php echo strtoupper(substr($g['name'], 0, 2)); ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($g['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($g['description']); ?></small>
                                                        <div class="mt-1">
                                                            <span class="member-count">
                                                                <i class="fas fa-users"></i> <?php echo count($g['members']); ?> membres
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <button class="btn btn-primary btn-sm w-100" 
                                                        onclick="joinGroup(<?php echo $g['id']; ?>)">
                                                    <i class="fas fa-sign-in-alt"></i> Rejoindre
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Créer un groupe -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Créer un nouveau groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_group">
                    
                    <div class="mb-3">
                        <label for="group_name" class="form-label">Nom du groupe</label>
                        <input type="text" name="name" id="group_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="group_description" class="form-label">Description</label>
                        <textarea name="description" id="group_description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="group_privacy" class="form-label">Type de groupe</label>
                        <select name="privacy" id="group_privacy" class="form-select">
                            <option value="public">Public (visible par tous)</option>
                            <option value="private">Privé (invitation uniquement)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer le groupe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Voir les membres -->
<div class="modal fade" id="viewMembersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users"></i> Membres du groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="membersList">
                <!-- Liste des membres chargée dynamiquement -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter des membres -->
<div class="modal fade" id="addMembersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Ajouter des membres au groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="addMembersModalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function joinGroup(groupId) {
    if (confirm('Voulez-vous rejoindre ce groupe ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="join_group">
            <input type="hidden" name="group_id" value="${groupId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function quitGroup(groupId) {
    if (confirm('Êtes-vous sûr de vouloir quitter ce groupe ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="quit_group">
            <input type="hidden" name="group_id" value="${groupId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteGroup(groupId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ? Cette action est irréversible.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_group">
            <input type="hidden" name="group_id" value="${groupId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function viewMembers(groupId, groupName) {
    // Charger les membres du groupe via AJAX
    fetch(`index.php?page=groups&action=get_members&group_id=${groupId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('membersList').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewMembersModal')).show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement des membres:', error);
            document.getElementById('membersList').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <p>Erreur lors du chargement des membres</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('viewMembersModal')).show();
        });
}

function addMembersToGroup(groupId, groupName) {
    // Charger le modal d'ajout de membres
    fetch(`index.php?page=groups&action=show_add_members&group_id=${groupId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('addMembersModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('addMembersModal')).show();
        })
        .catch(error => {
            console.error('Erreur lors du chargement du modal:', error);
        });
}

function editGroup(groupId, groupName, groupDescription) {
    // Ici on pourrait ouvrir un modal d'édition
    alert('Fonctionnalité d\'édition en développement');
}
</script>
</body>
</html> 