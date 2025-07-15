<?php
/**
 * Classe Database pour gérer les opérations XML
 * Utilise SimpleXML pour manipuler les données
 */

require_once 'config/config.php';

class Database {
    private $usersXml;
    private $messagesXml;
    private $groupsXml;
    private $contactsXml;
    
    public function __construct() {
        $this->initializeXmlFiles();
    }
    
    /**
     * Initialise les fichiers XML s'ils n'existent pas
     */
    private function initializeXmlFiles() {
        // Créer le dossier data s'il n'existe pas
        if (!is_dir(DATA_PATH)) {
            mkdir(DATA_PATH, 0755, true);
        }
        
        // Initialiser users.xml
        if (!file_exists(USERS_XML)) {
            $this->createUsersXml();
        }
        
        // Initialiser messages.xml
        if (!file_exists(MESSAGES_XML)) {
            $this->createMessagesXml();
        }
        
        // Initialiser groups.xml
        if (!file_exists(GROUPS_XML)) {
            $this->createGroupsXml();
        }
        
        // Initialiser contacts.xml
        if (!file_exists(CONTACTS_XML)) {
            $this->createContactsXml();
        }
        
        // Charger les fichiers XML
        $this->loadXmlFiles();
    }
    
    /**
     * Charge les fichiers XML en mémoire
     */
    private function loadXmlFiles() {
        try {
            $this->usersXml = simplexml_load_file(USERS_XML);
            $this->messagesXml = simplexml_load_file(MESSAGES_XML);
            $this->groupsXml = simplexml_load_file(GROUPS_XML);
            $this->contactsXml = simplexml_load_file(CONTACTS_XML);
        } catch (Exception $e) {
            error_log("Erreur lors du chargement des fichiers XML: " . $e->getMessage());
            throw new Exception("Impossible de charger les données");
        }
    }
    
    /**
     * Crée le fichier users.xml initial
     */
    private function createUsersXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE users SYSTEM "../schemas/users.dtd">' . "\n";
        $xml .= '<users></users>';
        file_put_contents(USERS_XML, $xml);
    }
    
    /**
     * Crée le fichier messages.xml initial
     */
    private function createMessagesXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE messages SYSTEM "../schemas/messages.dtd">' . "\n";
        $xml .= '<messages></messages>';
        file_put_contents(MESSAGES_XML, $xml);
    }
    
    /**
     * Crée le fichier groups.xml initial
     */
    private function createGroupsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE groups SYSTEM "../schemas/groups.dtd">' . "\n";
        $xml .= '<groups></groups>';
        file_put_contents(GROUPS_XML, $xml);
    }
    
    /**
     * Crée le fichier contacts.xml initial
     */
    private function createContactsXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<contacts></contacts>';
        file_put_contents(CONTACTS_XML, $xml);
    }
    
    /**
     * Sauvegarde les modifications dans le fichier XML
     */
    public function saveXml($xmlObject, $filePath) {
        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            $dom->loadXML($xmlObject->asXML());
            $dom->save($filePath);
            
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la sauvegarde XML: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valide un fichier XML contre son DTD
     */
    public function validateXml($xmlFile, $dtdFile) {
        try {
            $dom = new DOMDocument();
            $dom->load($xmlFile);
            
            if ($dom->validate()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Erreur de validation XML: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Génère un nouvel ID unique
     */
    public function generateId($xmlObject, $idAttribute = 'id') {
        $maxId = 0;
        foreach ($xmlObject->children() as $child) {
            $id = (int)$child[$idAttribute];
            if ($id > $maxId) {
                $maxId = $id;
            }
        }
        return $maxId + 1;
    }
    
    /**
     * Recherche un élément par ID
     */
    public function findById($xmlObject, $id, $idAttribute = 'id') {
        foreach ($xmlObject->children() as $child) {
            if ((string)$child[$idAttribute] === (string)$id) {
                return $child;
            }
        }
        return null;
    }
    
    /**
     * Recherche des éléments par critères
     */
    public function findBy($xmlObject, $criteria) {
        $results = [];
        foreach ($xmlObject->children() as $child) {
            $match = true;
            foreach ($criteria as $key => $value) {
                if ($key === 'attribute') {
                    foreach ($value as $attrName => $attrValue) {
                        if ((string)$child[$attrName] !== (string)$attrValue) {
                            $match = false;
                            break;
                        }
                    }
                } else {
                    if ((string)$child->$key !== (string)$value) {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $results[] = $child;
            }
        }
        return $results;
    }
    
    /**
     * Supprime un élément par ID
     */
    public function deleteById($xmlObject, $id, $filePath, $idAttribute = 'id') {
        foreach ($xmlObject->children() as $child) {
            if ((string)$child[$idAttribute] === (string)$id) {
                unset($child[0]);
                return $this->saveXml($xmlObject, $filePath);
            }
        }
        return false;
    }
    
    /**
     * Met à jour un élément
     */
    public function updateElement($xmlObject, $id, $data, $filePath, $idAttribute = 'id') {
        $element = $this->findById($xmlObject, $id, $idAttribute);
        if ($element) {
            foreach ($data as $key => $value) {
                if ($key === 'attributes') {
                    foreach ($value as $attrName => $attrValue) {
                        $element[$attrName] = $attrValue;
                    }
                } else {
                    $element->$key = $value;
                }
            }
            return $this->saveXml($xmlObject, $filePath);
        }
        return false;
    }
    
    // Getters pour les objets XML
    public function getUsersXml() {
        return $this->usersXml;
    }
    
    public function getMessagesXml() {
        return $this->messagesXml;
    }
    
    public function getGroupsXml() {
        return $this->groupsXml;
    }
    
    public function getContactsXml() {
        return $this->contactsXml;
    }
    
    /**
     * Recharge les fichiers XML depuis le disque
     */
    public function reload() {
        $this->loadXmlFiles();
    }
    
    /**
     * Sauvegarde tous les fichiers XML
     */
    public function saveAll() {
        $this->saveXml($this->usersXml, USERS_XML);
        $this->saveXml($this->messagesXml, MESSAGES_XML);
        $this->saveXml($this->groupsXml, GROUPS_XML);
        $this->saveXml($this->contactsXml, CONTACTS_XML);
    }
    
    /**
     * Valide tous les fichiers XML
     */
    public function validateAll() {
        $results = [];
        $results['users'] = $this->validateXml(USERS_XML, 'schemas/users.dtd');
        $results['messages'] = $this->validateXml(MESSAGES_XML, 'schemas/messages.dtd');
        $results['groups'] = $this->validateXml(GROUPS_XML, 'schemas/groups.dtd');
        return $results;
    }
} 