<?php
if (!$user->isLoggedIn()) {
    redirect('index.php?page=login');
}

$currentUser = $user->getCurrentUser();
$successMessage = '';
$errorMessage = '';

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $profileData = [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'bio' => $_POST['bio'] ?? ''
                ];
                
                $result = $user->updateProfile($currentUser['id'], $profileData);
                if ($result['success']) {
                    $successMessage = 'Profil mis à jour avec succès';
                    $currentUser = $user->getCurrentUser(); // Recharger les données
                } else {
                    $errorMessage = implode(', ', $result['errors']);
                }
                break;
                
            case 'change_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                // Vérifier l'ancien mot de passe
                if (!password_verify($currentPassword, $currentUser['password_hash'])) {
                    $errorMessage = 'Mot de passe actuel incorrect';
                } elseif ($newPassword !== $confirmPassword) {
                    $errorMessage = 'Les nouveaux mots de passe ne correspondent pas';
                } elseif (strlen($newPassword) < 6) {
                    $errorMessage = 'Le nouveau mot de passe doit contenir au moins 6 caractères';
                } else {
                    $result = $user->changePassword($currentUser['id'], $newPassword);
                    if ($result['success']) {
                        $successMessage = 'Mot de passe modifié avec succès';
                    } else {
                        $errorMessage = implode(', ', $result['errors']);
                    }
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header { background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: white; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; background: #fff; color: #25d366; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 3em; border: 4px solid white; }
        .profile-card { transition: all 0.3s ease; }
        .profile-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .info-item { padding: 15px; border-bottom: 1px solid #eee; }
        .info-item:last-child { border-bottom: none; }
        .info-label { font-weight: 600; color: #666; }
        .info-value { color: #333; }
    </style>
</head>
<body>
<?php include 'pages/_navbar.php'; ?>

<!-- En-tête du profil -->
<div class="profile-header py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 text-center">
                <div class="profile-avatar mx-auto mb-3">
                    <?php 
                    $firstName = $currentUser['profile']['first_name'];
                    $lastName = $currentUser['profile']['last_name'];
                    echo strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    ?>
                </div>
            </div>
            <div class="col-md-9">
                <h2 class="mb-2"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                <p class="mb-1">@<?php echo htmlspecialchars($currentUser['username']); ?></p>
                <?php if (!empty($currentUser['profile']['bio'])): ?>
                    <p class="mb-0"><i class="fas fa-quote-left me-2"></i><?php echo htmlspecialchars($currentUser['profile']['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
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

    <div class="row">
        <!-- Informations du profil -->
        <div class="col-lg-8 mb-4">
            <div class="card profile-card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Informations personnelles</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-label"><i class="fas fa-user me-2"></i>Nom complet</span>
                            </div>
                            <div class="col-md-9">
                                <span class="info-value"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-label"><i class="fas fa-at me-2"></i>Nom d'utilisateur</span>
                            </div>
                            <div class="col-md-9">
                                <span class="info-value">@<?php echo htmlspecialchars($currentUser['username']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-label"><i class="fas fa-envelope me-2"></i>Email</span>
                            </div>
                            <div class="col-md-9">
                                <span class="info-value"><?php echo htmlspecialchars($currentUser['email']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-label"><i class="fas fa-quote-left me-2"></i>Bio</span>
                            </div>
                            <div class="col-md-9">
                                <span class="info-value">
                                    <?php echo !empty($currentUser['profile']['bio']) ? htmlspecialchars($currentUser['profile']['bio']) : '<em class="text-muted">Aucune bio</em>'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="info-label"><i class="fas fa-calendar me-2"></i>Membre depuis</span>
                            </div>
                            <div class="col-md-9">
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($currentUser['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit"></i> Modifier le profil
                        </button>
                        
                        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key"></i> Changer le mot de passe
                        </button>
                        
                        <a href="index.php?page=settings" class="btn btn-outline-info">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        
                        <a href="index.php?page=messages" class="btn btn-outline-success">
                            <i class="fas fa-comments"></i> Mes messages
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card profile-card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary"><?php echo count($contact->getUserContacts($currentUser['id'])); ?></h4>
                                <small class="text-muted">Contacts</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?php echo count($group->getUserGroups($currentUser['id'])); ?></h4>
                            <small class="text-muted">Groupes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifier le profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Prénom</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['profile']['first_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Nom</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['profile']['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea name="bio" id="bio" class="form-control" rows="3" 
                                  placeholder="Parlez-nous de vous..."><?php echo htmlspecialchars($currentUser['profile']['bio'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Changer le mot de passe -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key"></i> Changer le mot de passe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 