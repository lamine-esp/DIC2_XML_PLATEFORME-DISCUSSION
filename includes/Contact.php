<?php
/**
 * Classe Contact pour gérer les contacts des utilisateurs
 * Gère l'ajout, la suppression et la gestion des contacts
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

class Contact {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Ajoute un contact utilisateur existant
     */
    public function addContact($userId, $contactUserId, $nickname = null) {
        // Vérifier que l'utilisateur n'ajoute pas lui-même
        if ($userId === $contactUserId) {
            return ['success' => false, 'errors' => ['contact' => 'Vous ne pouvez pas vous ajouter vous-même']];
        }
        
        $contacts = $this->db->getContactsXml();
        
        // Vérifier si le contact existe déjà
        $existingContact = $this->findContact($userId, $contactUserId);
        if ($existingContact) {
            return ['success' => false, 'errors' => ['contact' => 'Ce contact existe déjà']];
        }
        
        $newId = $this->db->generateId($contacts);
        
        // Créer le nouveau contact
        $contact = $contacts->addChild('contact');
        $contact->addAttribute('id', $newId);
        
        $contact->addChild('user_id', $userId);
        $contact->addChild('contact_user_id', $contactUserId);
        $contact->addChild('nickname', $nickname ? htmlspecialchars($nickname) : '');
        $contact->addChild('created_at', date('Y-m-d\TH:i:s'));
        $contact->addChild('is_favorite', 'false');
        $contact->addChild('type', 'user');
        
        // Sauvegarder
        if ($this->db->saveXml($contacts, CONTACTS_XML)) {
            return ['success' => true, 'contact_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de l\'ajout du contact']];
        }
    }
    
    /**
     * Ajoute un contact externe avec numéro de téléphone
     */
    public function addExternalContact($userId, $firstName, $lastName, $phoneNumber, $nickname = null) {
        $contacts = $this->db->getContactsXml();
        
        // Vérifier si le contact avec ce numéro existe déjà
        $existingContact = $this->findExternalContact($userId, $phoneNumber);
        if ($existingContact) {
            return ['success' => false, 'errors' => ['contact' => 'Un contact avec ce numéro existe déjà']];
        }
        
        $newId = $this->db->generateId($contacts);
        
        // Créer le nouveau contact externe
        $contact = $contacts->addChild('contact');
        $contact->addAttribute('id', $newId);
        
        $contact->addChild('user_id', $userId);
        $contact->addChild('contact_user_id', ''); // Vide pour les contacts externes
        $contact->addChild('first_name', htmlspecialchars($firstName));
        $contact->addChild('last_name', htmlspecialchars($lastName));
        $contact->addChild('phone_number', htmlspecialchars($phoneNumber));
        $contact->addChild('nickname', $nickname ? htmlspecialchars($nickname) : '');
        $contact->addChild('created_at', date('Y-m-d\TH:i:s'));
        $contact->addChild('is_favorite', 'false');
        $contact->addChild('type', 'external');
        
        // Sauvegarder
        if ($this->db->saveXml($contacts, CONTACTS_XML)) {
            return ['success' => true, 'contact_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de l\'ajout du contact']];
        }
    }
    
    /**
     * Supprime un contact
     */
    public function removeContact($userId, $contactUserId) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId && 
                (string)$contact->contact_user_id === $contactUserId) {
                unset($contact[0]);
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Supprime un contact par son ID
     */
    public function removeContactById($contactId) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact['id'] === $contactId) {
                unset($contact[0]);
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Obtient tous les contacts d'un utilisateur
     */
    public function getUserContacts($userId) {
        $contacts = $this->db->getContactsXml();
        $userContacts = [];
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId) {
                $userContacts[] = $this->formatContactData($contact);
            }
        }
        
        // Trier par favoris d'abord, puis par nom
        usort($userContacts, function($a, $b) {
            if ($a['is_favorite'] !== $b['is_favorite']) {
                return $b['is_favorite'] ? 1 : -1;
            }
            return strcasecmp($a['nickname'], $b['nickname']);
        });
        
        return $userContacts;
    }
    
    /**
     * Obtient les contacts favoris d'un utilisateur
     */
    public function getFavoriteContacts($userId) {
        $contacts = $this->getUserContacts($userId);
        return array_filter($contacts, function($contact) {
            return $contact['is_favorite'];
        });
    }
    
    /**
     * Marque un contact comme favori
     */
    public function toggleFavorite($userId, $contactUserId) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId && 
                (string)$contact->contact_user_id === $contactUserId) {
                $currentFavorite = (string)$contact->is_favorite === 'true';
                $contact->is_favorite = $currentFavorite ? 'false' : 'true';
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Marque un contact comme favori par son ID
     */
    public function toggleFavoriteById($contactId) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact['id'] === $contactId) {
                $currentFavorite = (string)$contact->is_favorite === 'true';
                $contact->is_favorite = $currentFavorite ? 'false' : 'true';
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Met à jour le surnom d'un contact
     */
    public function updateNickname($userId, $contactUserId, $nickname) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId && 
                (string)$contact->contact_user_id === $contactUserId) {
                $contact->nickname = htmlspecialchars($nickname);
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Met à jour le surnom d'un contact par son ID
     */
    public function updateNicknameById($contactId, $nickname) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact['id'] === $contactId) {
                $contact->nickname = htmlspecialchars($nickname);
                return $this->db->saveXml($contacts, CONTACTS_XML);
            }
        }
        
        return false;
    }
    
    /**
     * Recherche un contact spécifique
     */
    private function findContact($userId, $contactUserId) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId && 
                (string)$contact->contact_user_id === $contactUserId) {
                return $this->formatContactData($contact);
            }
        }
        
        return null;
    }
    
    /**
     * Recherche un contact externe par numéro de téléphone
     */
    private function findExternalContact($userId, $phoneNumber) {
        $contacts = $this->db->getContactsXml();
        
        foreach ($contacts->contact as $contact) {
            if ((string)$contact->user_id === $userId && 
                (string)$contact->type === 'external' &&
                (string)$contact->phone_number === $phoneNumber) {
                return $this->formatContactData($contact);
            }
        }
        
        return null;
    }
    
    /**
     * Vérifie si un utilisateur est dans les contacts d'un autre
     */
    public function isContact($userId, $contactUserId) {
        return $this->findContact($userId, $contactUserId) !== null;
    }
    
    /**
     * Obtient les contacts mutuels
     */
    public function getMutualContacts($userId) {
        $userContacts = $this->getUserContacts($userId);
        $mutualContacts = [];
        
        foreach ($userContacts as $contact) {
            if ($this->isContact($contact['contact_user_id'], $userId)) {
                $mutualContacts[] = $contact;
            }
        }
        
        return $mutualContacts;
    }
    
    /**
     * Recherche dans les contacts
     */
    public function searchContacts($userId, $query) {
        $contacts = $this->getUserContacts($userId);
        $results = [];
        
        foreach ($contacts as $contact) {
            $nickname = strtolower($contact['nickname']);
            $searchQuery = strtolower($query);
            
            if (strpos($nickname, $searchQuery) !== false) {
                $results[] = $contact;
            }
        }
        
        return $results;
    }
    
    /**
     * Obtient les statistiques des contacts
     */
    public function getContactStats($userId) {
        $contacts = $this->getUserContacts($userId);
        $favorites = $this->getFavoriteContacts($userId);
        $mutual = $this->getMutualContacts($userId);
        
        return [
            'total_contacts' => count($contacts),
            'favorite_contacts' => count($favorites),
            'mutual_contacts' => count($mutual)
        ];
    }
    
    /**
     * Formate les données du contact
     */
    private function formatContactData($contact) {
        $data = [
            'id' => (string)$contact['id'],
            'user_id' => (string)$contact->user_id,
            'contact_user_id' => (string)$contact->contact_user_id,
            'nickname' => (string)$contact->nickname,
            'created_at' => (string)$contact->created_at,
            'is_favorite' => (string)$contact->is_favorite === 'true',
            'type' => (string)$contact->type ?: 'user'
        ];
        
        // Ajouter les données spécifiques aux contacts externes
        if ((string)$contact->type === 'external') {
            $data['first_name'] = (string)$contact->first_name;
            $data['last_name'] = (string)$contact->last_name;
            $data['phone_number'] = (string)$contact->phone_number;
        }
        
        return $data;
    }
} 