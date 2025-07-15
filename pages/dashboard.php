<?php
// Vérifier que l'utilisateur est connecté
if (!$user->isLoggedIn()) {
    redirect('index.php?page=login');
}

$currentUser = $user->getCurrentUser();
$unreadCount = $message->getUnreadCount($currentUser['id']);
$userContacts = $contact->getUserContacts($currentUser['id']);
$userGroups = $group->getUserGroups($currentUser['id']);
$recentConversations = $message->getRecentConversations($currentUser['id']);

// Dédupliquer les conversations par type et id (conversations réelles)
$uniqueConversations = [];
foreach ($recentConversations as $conv) {
    $key = $conv['type'] . '_' . $conv['id'];
    $uniqueConversations[$key] = $conv;
}
$realConversationsCount = count($uniqueConversations);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=dashboard">
                <i class="fas fa-comments"></i> <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?page=dashboard">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=messages">
                            <i class="fas fa-comments"></i> Messages
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=groups">
                            <i class="fas fa-users"></i> Groupes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contacts">
                            <i class="fas fa-address-book"></i> Contacts
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['profile']['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="index.php?page=profile">
                                <i class="fas fa-user-edit"></i> Profil
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?page=settings">
                                <i class="fas fa-cog"></i> Paramètres
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=logout">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="card-title">
                                    Bonjour, <?php echo htmlspecialchars($currentUser['profile']['first_name']); ?> !
                                </h2>
                                <p class="card-text">
                                    Bienvenue sur votre tableau de bord. Vous avez 
                                    <strong><?php echo $unreadCount; ?> message(s) non lu(s)</strong>.
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-user-circle fa-4x text-light opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-comments fa-2x text-primary mb-2"></i>
                        <h5 class="card-title"><?php echo $realConversationsCount; ?></h5>
                        <p class="card-text">Conversations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                        <h5 class="card-title"><?php echo count($userGroups); ?></h5>
                        <p class="card-text">Groupes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-address-book fa-2x text-info mb-2"></i>
                        <h5 class="card-title"><?php echo count($userContacts); ?></h5>
                        <p class="card-text">Contacts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-envelope fa-2x text-warning mb-2"></i>
                        <h5 class="card-title"><?php echo $unreadCount; ?></h5>
                        <p class="card-text">Non lus</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Conversations -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-comments"></i> Conversations récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentConversations)): ?>
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Aucune conversation récente
                            </p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php 
                                $validConversations = [];
                                foreach (array_slice($recentConversations, 0, 5) as $conversation): 
                                    $showConversation = true;
                                    
                                    if ($conversation['type'] === 'user') {
                                        $otherUser = $user->getUserById($conversation['id']);
                                        if (!$otherUser || !isset($otherUser['profile'])) {
                                            $showConversation = false;
                                        }
                                    } else {
                                        $groupInfo = $group->getGroupById($conversation['id']);
                                        if (!$groupInfo || !isset($groupInfo['name'])) {
                                            $showConversation = false;
                                        }
                                    }
                                    
                                    if ($showConversation):
                                ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php if ($conversation['type'] === 'user'): ?>
                                                    <?php 
                                                    $otherUser = $user->getUserById($conversation['id']);
                                                    echo htmlspecialchars($otherUser['profile']['first_name'] . ' ' . $otherUser['profile']['last_name']);
                                                    ?>
                                                <?php else: ?>
                                                    <?php 
                                                    $groupInfo = $group->getGroupById($conversation['id']);
                                                    echo htmlspecialchars($groupInfo['name']);
                                                    ?>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php 
                                                if (isset($conversation['last_message']['content'])) {
                                                    echo htmlspecialchars(substr($conversation['last_message']['content'], 0, 50));
                                                    if (strlen($conversation['last_message']['content']) > 50) {
                                                        echo '...';
                                                    }
                                                } else {
                                                    echo htmlspecialchars('Aucun message');
                                                }
                                                ?>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <?php 
                                            if (isset($conversation['last_message']['timestamp'])) {
                                                echo date('d/m H:i', strtotime($conversation['last_message']['timestamp']));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php?page=messages" class="btn btn-outline-primary btn-sm">
                                    Voir toutes les conversations
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt"></i> Actions rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php?page=messages" class="btn btn-primary">
                                <i class="fas fa-comment"></i> Nouveau message
                            </a>
                            <a href="index.php?page=groups" class="btn btn-success">
                                <i class="fas fa-plus"></i> Créer un groupe
                            </a>
                            <a href="index.php?page=contacts" class="btn btn-info">
                                <i class="fas fa-user-plus"></i> Ajouter un contact
                            </a>
                            <a href="index.php?page=profile" class="btn btn-outline-secondary">
                                <i class="fas fa-user-edit"></i> Modifier le profil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Groups -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users"></i> Mes groupes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($userGroups)): ?>
                            <p class="text-muted text-center py-2">
                                Aucun groupe rejoint
                            </p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($userGroups, 0, 3) as $group): ?>
                                    <div class="list-group-item">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($group['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo count($group['members']); ?> membres
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-2">
                                <a href="index.php?page=groups" class="btn btn-outline-primary btn-sm">
                                    Voir tous les groupes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock"></i> Activité récente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6>Connexion réussie</h6>
                                    <p class="text-muted">Vous vous êtes connecté avec succès</p>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
                                </div>
                            </div>
                            <?php if ($currentUser['last_login']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Dernière connexion</h6>
                                        <p class="text-muted">Votre dernière connexion</p>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($currentUser['last_login'])); ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p class="text-muted">
                        Une plateforme de discussion moderne développée avec PHP et XML.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; 2024 <?php echo APP_NAME; ?>. Tous droits réservés.
                    </p>
                    <p class="text-muted mb-0">
                        Projet DSS XML - Institut de Formation
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html> 