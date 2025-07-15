<?php
if (!isset($user)) {
    require_once __DIR__ . '/../includes/User.php';
    $user = new User();
}
$currentUser = $user->isLoggedIn() ? $user->getCurrentUser() : null;
?>
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
                    <a class="nav-link<?php if ($_GET['page'] ?? 'dashboard' === 'dashboard') echo ' active'; ?>" href="index.php?page=dashboard">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if ($_GET['page'] ?? '' === 'messages') echo ' active'; ?>" href="index.php?page=messages">
                        <i class="fas fa-comments"></i> Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if ($_GET['page'] ?? '' === 'groups') echo ' active'; ?>" href="index.php?page=groups">
                        <i class="fas fa-users"></i> Groupes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?php if ($_GET['page'] ?? '' === 'contacts') echo ' active'; ?>" href="index.php?page=contacts">
                        <i class="fas fa-address-book"></i> Contacts
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($user->isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['profile']['first_name'] ?? ''); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user-edit"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="index.php?page=settings"><i class="fas fa-cog"></i> Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=login">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=register">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 