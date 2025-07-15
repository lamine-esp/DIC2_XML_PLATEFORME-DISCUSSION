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
            case 'update_settings':
                $settingsData = [
                    'notifications' => isset($_POST['notifications']),
                    'theme' => $_POST['theme'] ?? 'light',
                    'language' => $_POST['language'] ?? 'fr',
                    'privacy_level' => $_POST['privacy_level'] ?? 'public'
                ];
                
                $result = $user->updateSettings($currentUser['id'], $settingsData);
                if ($result['success']) {
                    $successMessage = 'Paramètres mis à jour avec succès';
                    $currentUser = $user->getCurrentUser(); // Recharger les données
                } else {
                    $errorMessage = implode(', ', $result['errors']);
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
    <title>Paramètres - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .settings-card { transition: all 0.3s ease; }
        .settings-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .setting-item { padding: 15px; border-bottom: 1px solid #eee; }
        .setting-item:last-child { border-bottom: none; }
        .setting-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
        .icon-notifications { background: #25d366; }
        .icon-privacy { background: #ff6b6b; }
        .icon-theme { background: #4ecdc4; }
        .icon-security { background: #45b7d1; }
        .icon-account { background: #96ceb4; }
        .form-switch .form-check-input { width: 3em; }
    </style>
</head>
<body>
<?php include 'pages/_navbar.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Paramètres</h5>
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

                    <form method="post">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <!-- Notifications -->
                        <div class="card settings-card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-bell"></i> Notifications</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="setting-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="setting-icon icon-notifications me-3">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Notifications push</h6>
                                                <small class="text-muted">Recevoir des notifications pour les nouveaux messages</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="notifications" id="notifications" 
                                                   <?php echo $currentUser['settings']['notifications'] ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Apparence -->
                        <div class="card settings-card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-palette"></i> Apparence</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="setting-item">
                                    <div class="d-flex align-items-center">
                                        <div class="setting-icon icon-theme me-3">
                                            <i class="fas fa-palette"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Thème</h6>
                                            <small class="text-muted">Choisir l'apparence de l'interface</small>
                                        </div>
                                        <div class="ms-3">
                                            <select name="theme" class="form-select form-select-sm" style="width: 150px;">
                                                <option value="light" <?php echo $currentUser['settings']['theme'] === 'light' ? 'selected' : ''; ?>>Clair</option>
                                                <option value="dark" <?php echo $currentUser['settings']['theme'] === 'dark' ? 'selected' : ''; ?>>Sombre</option>
                                                <option value="auto" <?php echo $currentUser['settings']['theme'] === 'auto' ? 'selected' : ''; ?>>Automatique</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="d-flex align-items-center">
                                        <div class="setting-icon icon-theme me-3">
                                            <i class="fas fa-language"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Langue</h6>
                                            <small class="text-muted">Langue de l'interface</small>
                                        </div>
                                        <div class="ms-3">
                                            <select name="language" class="form-select form-select-sm" style="width: 150px;">
                                                <option value="fr" <?php echo $currentUser['settings']['language'] === 'fr' ? 'selected' : ''; ?>>Français</option>
                                                <option value="en" <?php echo $currentUser['settings']['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                                <option value="es" <?php echo $currentUser['settings']['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Confidentialité -->
                        <div class="card settings-card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Confidentialité</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="setting-item">
                                    <div class="d-flex align-items-center">
                                        <div class="setting-icon icon-privacy me-3">
                                            <i class="fas fa-user-secret"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Niveau de confidentialité</h6>
                                            <small class="text-muted">Contrôler qui peut voir votre profil</small>
                                        </div>
                                        <div class="ms-3">
                                            <select name="privacy_level" class="form-select form-select-sm" style="width: 150px;">
                                                <option value="public" <?php echo $currentUser['settings']['privacy_level'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                                <option value="contacts" <?php echo $currentUser['settings']['privacy_level'] === 'contacts' ? 'selected' : ''; ?>>Contacts uniquement</option>
                                                <option value="private" <?php echo $currentUser['settings']['privacy_level'] === 'private' ? 'selected' : ''; ?>>Privé</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sécurité -->
                        <div class="card settings-card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-lock"></i> Sécurité</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="setting-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="setting-icon icon-security me-3">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Authentification à deux facteurs</h6>
                                                <small class="text-muted">Ajouter une couche de sécurité supplémentaire</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="enable2FA()">
                                            Activer
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="setting-icon icon-security me-3">
                                                <i class="fas fa-history"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Historique des connexions</h6>
                                                <small class="text-muted">Voir les dernières connexions</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="viewLoginHistory()">
                                            Voir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Compte -->
                        <div class="card settings-card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user"></i> Compte</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="setting-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="setting-icon icon-account me-3">
                                                <i class="fas fa-download"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Exporter mes données</h6>
                                                <small class="text-muted">Télécharger toutes vos données</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData()">
                                            Exporter
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="setting-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="setting-icon icon-account me-3">
                                                <i class="fas fa-trash"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Supprimer mon compte</h6>
                                                <small class="text-muted">Action irréversible</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAccount()">
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function enable2FA() {
    alert('Fonctionnalité d\'authentification à deux facteurs en développement');
}

function viewLoginHistory() {
    alert('Historique des connexions en développement');
}

function exportData() {
    alert('Export des données en développement');
}

function deleteAccount() {
    if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
        alert('Suppression de compte en développement');
    }
}
</script>
</body>
</html> 