<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-comments"></i> <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">À propos</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($user->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=dashboard">Tableau de bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=logout">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Communiquez en toute simplicité
                    </h1>
                    <p class="lead mb-4">
                        Une plateforme de discussion moderne et sécurisée pour échanger avec vos contacts 
                        et participer à des groupes de discussion.
                    </p>
                    <div class="d-flex gap-3">
                        <?php if (!$user->isLoggedIn()): ?>
                            <a href="index.php?page=register" class="btn btn-light btn-lg">
                                <i class="fas fa-user-plus"></i> Commencer
                            </a>
                            <a href="index.php?page=login" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                        <?php else: ?>
                            <a href="index.php?page=dashboard" class="btn btn-light btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-comments display-1 text-light opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold">Fonctionnalités principales</h2>
                    <p class="lead text-muted">Découvrez ce que notre plateforme vous offre</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-comment-dots fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Messagerie instantanée</h5>
                            <p class="card-text">
                                Envoyez des messages texte et des fichiers à vos contacts en temps réel.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-users fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Groupes de discussion</h5>
                            <p class="card-text">
                                Créez et rejoignez des groupes pour discuter avec plusieurs personnes.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-user-friends fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Gestion des contacts</h5>
                            <p class="card-text">
                                Organisez vos contacts avec des surnoms et des favoris.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Sécurité avancée</h5>
                            <p class="card-text">
                                Vos données sont protégées avec un hachage sécurisé des mots de passe.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-file-upload fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Partage de fichiers</h5>
                            <p class="card-text">
                                Partagez des images, documents et autres fichiers avec vos contacts.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-cog fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Personnalisation</h5>
                            <p class="card-text">
                                Personnalisez votre profil et vos paramètres selon vos préférences.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">À propos du projet</h2>
                    <p class="lead mb-4">
                        Cette plateforme de discussion a été développée dans le cadre du cours  XML 
                        
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Technologies utilisées</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> PHP 8.0+</li>
                                <li><i class="fas fa-check text-success"></i> SimpleXML</li>
                                <li><i class="fas fa-check text-success"></i> HTML5/CSS3</li>
                                <li><i class="fas fa-check text-success"></i> JavaScript</li>
                        
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Fonctionnalités</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Stockage XML</li>
                                <li><i class="fas fa-check text-success"></i> Validation DTD</li>
                                <li><i class="fas fa-check text-success"></i> Messagerie</li>
                                <li><i class="fas fa-check text-success"></i> Groupes</li>
                                <li><i class="fas fa-check text-success"></i> Contacts</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
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
                        Projet DSS XML
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html> 