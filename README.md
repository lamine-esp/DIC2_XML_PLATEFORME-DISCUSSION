# Plateforme de Discussion en Ligne - Projet DSS XML

## 📋 Description du Projet

Une plateforme de discussion en ligne développée en PHP avec stockage XML, permettant aux utilisateurs de :
- Envoyer des messages et fichiers
- Gérer des contacts
- Créer et gérer des groupes de discussion
- Gérer leur profil utilisateur
- Configurer leurs paramètres

## 🏗️ Architecture du Projet

```
messaging-platform/
├── assets/                 # Ressources statiques (CSS, JS, images)
├── config/                 # Configuration de l'application
├── data/                   # Stockage XML des données
├── includes/               # Classes et fonctions PHP
├── pages/                  # Pages de l'interface utilisateur
├── uploads/                # Fichiers uploadés par les utilisateurs
├── index.php              # Point d'entrée principal
└── README.md              # Documentation
```

## 🛠️ Technologies Utilisées

- **Backend** : PHP 8.0+
- **Stockage** : XML avec SimpleXML
- **Frontend** : HTML5, CSS3, JavaScript
- **Validation** : DTD et XML Schema (XSD)

## 🚀 Installation et Utilisation

1. **Prérequis** :
   - PHP 8.0 ou supérieur
   - Serveur web (Apache/Nginx) ou serveur PHP intégré

2. **Installation** :
   ```bash
   # Cloner le projet
   git clone [url-du-projet]
   cd messaging-platform
   
   # Démarrer le serveur PHP
   php -S localhost:8000
   ```

3. **Accès** :
   - Ouvrir http://localhost:8000 dans votre navigateur
   - Créer un compte ou se connecter

## 📁 Structure des Données XML

### Utilisateurs (users.xml)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<users>
    <user id="1">
        <username>john_doe</username>
        <email>john@example.com</email>
        <password_hash>...</password_hash>
        <profile>
            <first_name>John</first_name>
            <last_name>Doe</last_name>
            <avatar>avatar1.jpg</avatar>
        </profile>
        <settings>
            <notifications>true</notifications>
            <theme>light</theme>
        </settings>
    </user>
</users>
```

### Messages (messages.xml)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<messages>
    <message id="1">
        <sender_id>1</sender_id>
        <recipient_type>user</recipient_type>
        <recipient_id>2</recipient_id>
        <content>Bonjour !</content>
        <timestamp>2024-01-15T10:30:00</timestamp>
        <attachments>
            <file>document.pdf</file>
        </attachments>
    </message>
</messages>
```

### Groupes (groups.xml)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<groups>
    <group id="1">
        <name>Équipe Projet</name>
        <description>Groupe pour le projet DSS</description>
        <created_by>1</created_by>
        <created_at>2024-01-15T09:00:00</created_at>
        <members>
            <member user_id="1" role="admin"/>
            <member user_id="2" role="member"/>
        </members>
    </group>
</groups>
```

## 👥 Fonctionnalités Principales

### 🔐 Authentification
- Inscription et connexion utilisateur
- Gestion des sessions sécurisées
- Récupération de mot de passe

### 💬 Messagerie
- Envoi de messages texte
- Envoi de fichiers (images, documents)
- Messages privés et de groupe
- Historique des conversations

### 👥 Gestion des Contacts
- Ajout/suppression de contacts
- Recherche d'utilisateurs
- Statut en ligne/hors ligne

### 👥 Groupes de Discussion
- Création de groupes
- Invitation de membres
- Gestion des rôles (admin/membre)
- Messages de groupe

### 👤 Profil Utilisateur
- Modification des informations personnelles
- Upload d'avatar
- Paramètres de confidentialité

### ⚙️ Paramètres
- Notifications
- Thème (clair/sombre)
- Langue
- Paramètres de sécurité

## 🔒 Sécurité

- Hachage des mots de passe avec `password_hash()`
- Validation des données XML
- Protection contre les injections
- Gestion des sessions sécurisées

## 📝 Validation XML

Le projet utilise des schémas DTD et XSD pour valider la structure des données XML :

- `schemas/users.dtd` - Structure des utilisateurs
- `schemas/messages.dtd` - Structure des messages
- `schemas/groups.dtd` - Structure des groupes

## 🧪 Tests

Pour exécuter les tests :
```bash
php tests/run_tests.php
```

## 📄 Licence

Ce projet est développé dans le cadre du cours DSS XML.

## 👨‍💻 Équipe de Développement

- [Nom du membre 1]
- [Nom du membre 2]
- [Nom du membre 3]

---

**Date de présentation** : 16 juillet 2025
**Version** : 1.0.0 