<?php
// Déconnexion de l'utilisateur
if ($user->isLoggedIn()) {
    $user->logout();
}
 
// Redirection vers la page d'accueil
redirect('index.php');
?> 