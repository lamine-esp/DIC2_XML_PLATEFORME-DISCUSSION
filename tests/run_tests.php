<?php
/**
 * Tests pour la Plateforme de Discussion
 * Valide les fonctionnalités principales de l'application
 */

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/User.php';
require_once '../includes/Message.php';
require_once '../includes/Group.php';
require_once '../includes/Contact.php';

class TestSuite {
    private $db;
    private $user;
    private $message;
    private $group;
    private $contact;
    private $testResults = [];
    
    public function __construct() {
        $this->db = new Database();
        $this->user = new User();
        $this->message = new Message();
        $this->group = new Group();
        $this->contact = new Contact();
    }
    
    public function runAllTests() {
        echo "=== Tests de la Plateforme de Discussion ===\n\n";
        
        $this->testDatabaseConnection();
        $this->testUserCreation();
        $this->testUserAuthentication();
        $this->testMessageSending();
        $this->testGroupCreation();
        $this->testContactManagement();
        $this->testXmlValidation();
        
        $this->displayResults();
    }
    
    private function testDatabaseConnection() {
        echo "Test de connexion à la base de données...\n";
        
        try {
            $users = $this->db->getUsersXml();
            $this->testResults['database'] = true;
            echo "✓ Connexion réussie\n";
        } catch (Exception $e) {
            $this->testResults['database'] = false;
            echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
        }
    }
    
    private function testUserCreation() {
        echo "\nTest de création d'utilisateur...\n";
        
        $testUser = [
            'username' => 'test_user_' . time(),
            'email' => 'test@example.com',
            'password' => 'testpassword123',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];
        
        $result = $this->user->createUser($testUser);
        
        if ($result['success']) {
            $this->testResults['user_creation'] = true;
            echo "✓ Utilisateur créé avec succès (ID: {$result['user_id']})\n";
        } else {
            $this->testResults['user_creation'] = false;
            echo "✗ Erreur lors de la création: " . implode(', ', $result['errors']) . "\n";
        }
    }
    
    private function testUserAuthentication() {
        echo "\nTest d'authentification...\n";
        
        // Test avec un utilisateur existant
        $result = $this->user->authenticate('admin', 'password');
        
        if ($result) {
            $this->testResults['authentication'] = true;
            echo "✓ Authentification réussie\n";
        } else {
            $this->testResults['authentication'] = false;
            echo "✗ Échec de l'authentification\n";
        }
    }
    
    private function testMessageSending() {
        echo "\nTest d'envoi de message...\n";
        
        $result = $this->message->sendMessage('1', 'user', '2', 'Test message');
        
        if ($result['success']) {
            $this->testResults['message_sending'] = true;
            echo "✓ Message envoyé avec succès (ID: {$result['message_id']})\n";
        } else {
            $this->testResults['message_sending'] = false;
            echo "✗ Erreur lors de l'envoi: " . implode(', ', $result['errors']) . "\n";
        }
    }
    
    private function testGroupCreation() {
        echo "\nTest de création de groupe...\n";
        
        $groupData = [
            'name' => 'Test Group',
            'description' => 'Groupe de test',
            'created_by' => '1',
            'privacy' => 'public'
        ];
        
        $result = $this->group->createGroup($groupData);
        
        if ($result['success']) {
            $this->testResults['group_creation'] = true;
            echo "✓ Groupe créé avec succès (ID: {$result['group_id']})\n";
        } else {
            $this->testResults['group_creation'] = false;
            echo "✗ Erreur lors de la création: " . implode(', ', $result['errors']) . "\n";
        }
    }
    
    private function testContactManagement() {
        echo "\nTest de gestion des contacts...\n";
        
        $result = $this->contact->addContact('1', '2', 'Test Contact');
        
        if ($result['success']) {
            $this->testResults['contact_management'] = true;
            echo "✓ Contact ajouté avec succès (ID: {$result['contact_id']})\n";
        } else {
            $this->testResults['contact_management'] = false;
            echo "✗ Erreur lors de l'ajout: " . implode(', ', $result['errors']) . "\n";
        }
    }
    
    private function testXmlValidation() {
        echo "\nTest de validation XML...\n";
        
        $validationResults = $this->db->validateAll();
        
        $allValid = true;
        foreach ($validationResults as $file => $isValid) {
            if ($isValid) {
                echo "✓ $file: Valide\n";
            } else {
                echo "✗ $file: Invalide\n";
                $allValid = false;
            }
        }
        
        $this->testResults['xml_validation'] = $allValid;
    }
    
    private function displayResults() {
        echo "\n=== Résultats des tests ===\n";
        
        $passed = 0;
        $total = count($this->testResults);
        
        foreach ($this->testResults as $test => $result) {
            $status = $result ? "PASS" : "FAIL";
            $icon = $result ? "✓" : "✗";
            echo "$icon $test: $status\n";
            
            if ($result) $passed++;
        }
        
        echo "\nRésumé: $passed/$total tests réussis\n";
        
        if ($passed === $total) {
            echo "🎉 Tous les tests sont passés avec succès !\n";
        } else {
            echo "⚠️  Certains tests ont échoué. Vérifiez les erreurs ci-dessus.\n";
        }
    }
}

// Exécuter les tests si le script est appelé directement
if (php_sapi_name() === 'cli' || isset($_GET['run_tests'])) {
    $testSuite = new TestSuite();
    $testSuite->runAllTests();
}

?> 