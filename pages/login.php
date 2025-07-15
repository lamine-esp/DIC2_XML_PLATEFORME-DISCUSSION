<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Connexion</title>
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
                <a class="nav-link" href="index.php?page=register">Inscription</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-sign-in-alt fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Connexion</h2>
                            <p class="text-muted">Connectez-vous à votre compte</p>
                        </div>

                        <div id="loginAlert" class="alert" style="display: none;"></div>

                        <form method="POST" action="" id="loginForm">
                            <input type="hidden" name="action" value="login">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo $_POST['username'] ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
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
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                    <i class="fas fa-sign-in-alt"></i> <span id="loginBtnText">Se connecter</span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Pas encore de compte ? 
                                <a href="index.php?page=register" class="text-decoration-none">S'inscrire</a>
                            </p>
                        </div>

                        <hr class="my-4">
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
                        &copy; 2025 <?php echo APP_NAME; ?>. Tous droits réservés.
                    </p>
                    <p class="text-muted mb-0">
                        Projet DSS XML 
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

        // Fill demo account
        function fillDemoAccount(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
        }

        // Form validation and AJAX submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const alertDiv = document.getElementById('loginAlert');

            // Validation
            if (!username) {
                showAlert('Veuillez saisir votre nom d\'utilisateur', 'danger');
                return;
            }

            if (!password) {
                showAlert('Veuillez saisir votre mot de passe', 'danger');
                return;
            }

            // Disable button and show loading
            loginBtn.disabled = true;
            loginBtnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('username', username);
            formData.append('password', password);
            formData.append('ajax', '1');

            // Send AJAX request
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Connexion réussie ! Redirection...', 'success');
                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php?page=dashboard';
                    }, 1000);
                } else {
                    showAlert(data.message || 'Erreur de connexion', 'danger');
                    loginBtn.disabled = false;
                    loginBtnText.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('Erreur de connexion au serveur', 'danger');
                loginBtn.disabled = false;
                loginBtnText.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
            });
        });

        // Function to show alerts
        function showAlert(message, type) {
            const alertDiv = document.getElementById('loginAlert');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
        }
    </script>
</body>
</html> 