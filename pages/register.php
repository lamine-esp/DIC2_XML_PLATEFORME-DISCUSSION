<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Inscription</title>
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
                <a class="nav-link" href="index.php?page=login">Connexion</a>
            </div>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Inscription</h2>
                            <p class="text-muted">Créez votre compte pour commencer</p>
                        </div>

                        <?php if (isset($_POST['action']) && $_POST['action'] === 'register' && isset($response)): ?>
                            <?php if ($response['success']): ?>
                                <div class="alert alert-success">
                                    <?php echo getSuccessMessage('user_created'); ?>
                                    <br>
                                    <a href="index.php?page=login" class="alert-link">Se connecter maintenant</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <?php if (isset($response['errors'])): ?>
                                        <ul class="mb-0">
                                            <?php foreach ($response['errors'] as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <?php echo $response['message']; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <input type="hidden" name="action" value="register">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo $_POST['username'] ?? ''; ?>" required>
                                </div>
                                <div class="form-text">
                                    Entre <?php echo MIN_USERNAME_LENGTH; ?> et <?php echo MAX_USERNAME_LENGTH; ?> caractères, lettres et chiffres uniquement.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $_POST['email'] ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Au moins <?php echo MIN_PASSWORD_LENGTH; ?> caractères.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                        et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Créer mon compte
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Déjà un compte ? 
                                <a href="index.php?page=login" class="text-decoration-none">Se connecter</a>
                            </p>
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
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const firstName = document.getElementById('first_name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const terms = document.getElementById('terms').checked;

            // Validation du nom d'utilisateur
            if (username.length < <?php echo MIN_USERNAME_LENGTH; ?>) {
                e.preventDefault();
                alert('Le nom d\'utilisateur doit contenir au moins <?php echo MIN_USERNAME_LENGTH; ?> caractères');
                return;
            }

            if (username.length > <?php echo MAX_USERNAME_LENGTH; ?>) {
                e.preventDefault();
                alert('Le nom d\'utilisateur ne peut pas dépasser <?php echo MAX_USERNAME_LENGTH; ?> caractères');
                return;
            }

            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                e.preventDefault();
                alert('Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores');
                return;
            }

            // Validation de l'email
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Veuillez saisir une adresse email valide');
                return;
            }

            // Validation du mot de passe
            if (password.length < <?php echo MIN_PASSWORD_LENGTH; ?>) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins <?php echo MIN_PASSWORD_LENGTH; ?> caractères');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return;
            }

            // Validation des champs requis
            if (!firstName || !lastName) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }

            if (!terms) {
                e.preventDefault();
                alert('Veuillez accepter les conditions d\'utilisation');
                return;
            }
        });

        // Validation en temps réel
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const isValid = /^[a-zA-Z0-9_]+$/.test(username);
            
            if (username.length > 0) {
                if (isValid) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const isValid = email.includes('@') && email.includes('.');
            
            if (email.length > 0) {
                if (isValid) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    </script>
</body>
</html> 