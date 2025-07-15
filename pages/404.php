<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Page non trouvée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-comments"></i> <?php echo APP_NAME; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Accueil</a>
                <?php if ($user->isLoggedIn()): ?>
                    <a class="nav-link" href="index.php?page=dashboard">Tableau de bord</a>
                <?php else: ?>
                    <a class="nav-link" href="index.php?page=login">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 404 Content -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                        <h1 class="display-1 fw-bold text-muted">404</h1>
                        <h2 class="mb-4">Page non trouvée</h2>
                        <p class="lead text-muted mb-4">
                            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                        </p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Retour à l'accueil
                            </a>
                            <?php if ($user->isLoggedIn()): ?>
                                <a href="index.php?page=dashboard" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                                </a>
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
</body>
</html> 