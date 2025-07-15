<?php
/**
 * Classe Group pour gérer les groupes de discussion
 * Gère la création, la gestion des membres et les paramètres des groupes
 */

require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php'; // Added for getGroupMembers

class Group {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crée un nouveau groupe
     */
    public function createGroup($groupData) {
        // Validation des données
        $errors = $this->validateGroupData($groupData);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $groups = $this->db->getGroupsXml();
        
        // Empêcher les doublons de groupe (même nom, même créateur, groupe actif)
        foreach ($groups->group as $existingGroup) {
            if (
                strtolower(trim((string)$existingGroup->name)) === strtolower(trim($groupData['name'])) &&
                (string)$existingGroup->created_by === (string)$groupData['created_by'] &&
                (string)$existingGroup['is_active'] === 'true'
            ) {
                return ['success' => false, 'errors' => ['name' => 'Un groupe avec ce nom existe déjà']];
            }
        }
        
        $newId = $this->db->generateId($groups);
        
        // Créer le nouveau groupe
        $group = $groups->addChild('group');
        $group->addAttribute('id', $newId);
        $group->addAttribute('is_active', 'true');
        
        $group->addChild('name', htmlspecialchars($groupData['name']));
        $group->addChild('description', htmlspecialchars($groupData['description']));
        $group->addChild('created_by', $groupData['created_by']);
        $group->addChild('created_at', date('Y-m-d\TH:i:s'));
        
        // Ajouter le créateur comme admin
        $members = $group->addChild('members');
        $member = $members->addChild('member');
        $member->addAttribute('user_id', $groupData['created_by']);
        $member->addAttribute('role', 'admin');
        $member->addAttribute('joined_at', date('Y-m-d\TH:i:s'));
        
        // Paramètres du groupe
        $settings = $group->addChild('settings');
        $settings->addChild('privacy', $groupData['privacy'] ?? 'public');
        $settings->addChild('notifications', 'true');
        $settings->addChild('max_members', MAX_GROUP_MEMBERS);
        
        // Sauvegarder
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true, 'group_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la création du groupe']];
        }
    }
    
    /**
     * Obtient un groupe par ID
     */
    public function getGroupById($groupId) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if ($group) {
            return $this->formatGroupData($group);
        }
        
        return null;
    }
    
    /**
     * Obtient tous les groupes actifs
     */
    public function getAllGroups() {
        $groups = $this->db->getGroupsXml();
        $groupList = [];
        
        foreach ($groups->group as $group) {
            if ((string)$group['is_active'] === 'true') {
                $groupList[] = $this->formatGroupData($group);
            }
        }
        
        return $groupList;
    }
    
    /**
     * Obtient les groupes d'un utilisateur
     */
    public function getUserGroups($userId) {
        $groups = $this->db->getGroupsXml();
        $userGroups = [];
        
        foreach ($groups->group as $group) {
            if ((string)$group['is_active'] === 'true') {
                foreach ($group->members->member as $member) {
                    if ((string)$member['user_id'] === $userId) {
                        $userGroups[] = $this->formatGroupData($group);
                        break;
                    }
                }
            }
        }
        
        return $userGroups;
    }
    
    /**
     * Obtient les groupes publics
     */
    public function getPublicGroups() {
        $groups = $this->db->getGroupsXml();
        $publicGroups = [];
        
        foreach ($groups->group as $group) {
            if ((string)$group['is_active'] === 'true' && 
                (string)$group->settings->privacy === 'public') {
                $publicGroups[] = $this->formatGroupData($group);
            }
        }
        
        return $publicGroups;
    }
    
    /**
     * Vérifie si un utilisateur est membre d'un groupe
     */
    public function isMember($groupId, $userId) {
        $group = $this->getGroupById($groupId);
        
        if (!$group) {
            return false;
        }
        
        foreach ($group['members'] as $member) {
            if ($member['user_id'] === $userId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si un utilisateur est admin d'un groupe
     */
    public function isAdmin($groupId, $userId) {
        $group = $this->getGroupById($groupId);
        
        if (!$group) {
            return false;
        }
        
        foreach ($group['members'] as $member) {
            if ($member['user_id'] === $userId && $member['role'] === 'admin') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Ajoute un membre à un groupe
     */
    public function addMember($groupId, $userId, $addedBy = null) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if (!$group) {
            return ['success' => false, 'errors' => ['group' => getErrorMessage('group_not_found')]];
        }
        
        // Vérifier si l'utilisateur est déjà membre
        if ($this->isMember($groupId, $userId)) {
            return ['success' => false, 'errors' => ['member' => 'L\'utilisateur est déjà membre du groupe']];
        }
        
        // Vérifier le nombre maximum de membres
        $memberCount = count($group->members->member);
        $maxMembers = (int)$group->settings->max_members;
        
        if ($memberCount >= $maxMembers) {
            return ['success' => false, 'errors' => ['member' => 'Le groupe a atteint le nombre maximum de membres']];
        }
        
        // Ajouter le membre
        $member = $group->members->addChild('member');
        $member->addAttribute('user_id', $userId);
        $member->addAttribute('role', 'member');
        $member->addAttribute('joined_at', date('Y-m-d\TH:i:s'));
        
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de l\'ajout du membre']];
        }
    }
    
    /**
     * Retire un membre d'un groupe
     */
    public function removeMember($groupId, $userId, $removedBy) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if (!$group) {
            return ['success' => false, 'errors' => ['group' => getErrorMessage('group_not_found')]];
        }
        
        // Vérifier les permissions
        if (!$this->isAdmin($groupId, $removedBy) && $removedBy !== $userId) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        // Ne pas permettre de retirer le dernier admin
        if ($this->isAdmin($groupId, $userId)) {
            $adminCount = 0;
            foreach ($group->members->member as $member) {
                if ((string)$member['role'] === 'admin') {
                    $adminCount++;
                }
            }
            
            if ($adminCount <= 1) {
                return ['success' => false, 'errors' => ['member' => 'Impossible de retirer le dernier administrateur']];
            }
        }
        
        // Retirer le membre
        foreach ($group->members->member as $member) {
            if ((string)$member['user_id'] === $userId) {
                unset($member[0]);
                break;
            }
        }
        
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors du retrait du membre']];
        }
    }
    
    /**
     * Change le rôle d'un membre
     */
    public function changeMemberRole($groupId, $userId, $newRole, $changedBy) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if (!$group) {
            return ['success' => false, 'errors' => ['group' => getErrorMessage('group_not_found')]];
        }
        
        // Vérifier les permissions
        if (!$this->isAdmin($groupId, $changedBy)) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        // Valider le rôle
        if (!in_array($newRole, ['admin', 'moderator', 'member'])) {
            return ['success' => false, 'errors' => ['role' => 'Rôle invalide']];
        }
        
        // Changer le rôle
        foreach ($group->members->member as $member) {
            if ((string)$member['user_id'] === $userId) {
                $member['role'] = $newRole;
                break;
            }
        }
        
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors du changement de rôle']];
        }
    }
    
    /**
     * Met à jour les paramètres du groupe
     */
    public function updateGroupSettings($groupId, $settings, $updatedBy) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if (!$group) {
            return ['success' => false, 'errors' => ['group' => getErrorMessage('group_not_found')]];
        }
        
        // Vérifier les permissions
        if (!$this->isAdmin($groupId, $updatedBy)) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        // Mettre à jour les paramètres
        if (isset($settings['name'])) {
            $group->name = htmlspecialchars($settings['name']);
        }
        
        if (isset($settings['description'])) {
            $group->description = htmlspecialchars($settings['description']);
        }
        
        if (isset($settings['privacy'])) {
            $group->settings->privacy = htmlspecialchars($settings['privacy']);
        }
        
        if (isset($settings['notifications'])) {
            $group->settings->notifications = $settings['notifications'] ? 'true' : 'false';
        }
        
        if (isset($settings['max_members'])) {
            $group->settings->max_members = (int)$settings['max_members'];
        }
        
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la mise à jour']];
        }
    }
    
    /**
     * Supprime un groupe
     */
    public function deleteGroup($groupId, $deletedBy) {
        $groups = $this->db->getGroupsXml();
        $group = $this->db->findById($groups, $groupId);
        
        if (!$group) {
            return ['success' => false, 'errors' => ['group' => getErrorMessage('group_not_found')]];
        }
        
        // Vérifier les permissions
        if (!$this->isAdmin($groupId, $deletedBy)) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        // Marquer comme inactif au lieu de supprimer
        $group['is_active'] = 'false';
        
        if ($this->db->saveXml($groups, GROUPS_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la suppression']];
        }
    }
    
    /**
     * Recherche des groupes
     */
    public function searchGroups($query) {
        $groups = $this->db->getGroupsXml();
        $results = [];
        
        foreach ($groups->group as $group) {
            if ((string)$group['is_active'] === 'true') {
                $name = strtolower((string)$group->name);
                $description = strtolower((string)$group->description);
                $searchQuery = strtolower($query);
                
                if (strpos($name, $searchQuery) !== false ||
                    strpos($description, $searchQuery) !== false) {
                    $results[] = $this->formatGroupData($group);
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Obtient les statistiques d'un groupe
     */
    public function getGroupStats($groupId) {
        $group = $this->getGroupById($groupId);
        
        if (!$group) {
            return null;
        }
        
        $stats = [
            'total_members' => count($group['members']),
            'admins' => 0,
            'moderators' => 0,
            'members' => 0
        ];
        
        foreach ($group['members'] as $member) {
            switch ($member['role']) {
                case 'admin':
                    $stats['admins']++;
                    break;
                case 'moderator':
                    $stats['moderators']++;
                    break;
                case 'member':
                    $stats['members']++;
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Obtient les membres d'un groupe avec leurs informations
     */
    public function getGroupMembers($groupId) {
        $group = $this->getGroupById($groupId);
        
        if (!$group) {
            return [];
        }
        
        $members = [];
        $user = new User(); // Pour récupérer les informations des utilisateurs
        
        foreach ($group['members'] as $member) {
            $userData = $user->getUserById($member['user_id']);
            if ($userData) {
                $members[] = [
                    'user_id' => $member['user_id'],
                    'role' => $member['role'],
                    'joined_at' => $member['joined_at'],
                    'user_info' => $userData
                ];
            }
        }
        
        return $members;
    }

    /**
     * Ajoute plusieurs membres à un groupe
     */
    public function addMultipleMembers($groupId, $userIds, $addedBy) {
        $results = [];
        $successCount = 0;
        
        foreach ($userIds as $userId) {
            $result = $this->addMember($groupId, $userId, $addedBy);
            if ($result['success']) {
                $successCount++;
            }
            $results[] = $result;
        }
        
        return [
            'success' => $successCount > 0,
            'total_requested' => count($userIds),
            'success_count' => $successCount,
            'results' => $results
        ];
    }
    
    /**
     * Valide les données du groupe
     */
    private function validateGroupData($groupData) {
        $errors = [];
        
        if (empty($groupData['name'])) {
            $errors['name'] = 'Le nom du groupe est requis';
        } elseif (strlen($groupData['name']) > MAX_GROUP_NAME_LENGTH) {
            $errors['name'] = 'Le nom du groupe est trop long';
        }
        
        if (empty($groupData['description'])) {
            $errors['description'] = 'La description du groupe est requise';
        }
        
        if (empty($groupData['created_by'])) {
            $errors['created_by'] = 'Créateur requis';
        }
        
        if (isset($groupData['privacy']) && !in_array($groupData['privacy'], ['public', 'private', 'secret'])) {
            $errors['privacy'] = 'Type de confidentialité invalide';
        }
        
        return $errors;
    }
    
    /**
     * Formate les données du groupe
     */
    private function formatGroupData($group) {
        $members = [];
        if (isset($group->members)) {
            foreach ($group->members->member as $member) {
                $members[] = [
                    'user_id' => (string)$member['user_id'],
                    'role' => (string)$member['role'],
                    'joined_at' => (string)$member['joined_at']
                ];
            }
        }
        
        return [
            'id' => (string)$group['id'],
            'name' => (string)$group->name,
            'description' => (string)$group->description,
            'created_by' => (string)$group->created_by,
            'created_at' => (string)$group->created_at,
            'is_active' => (string)$group['is_active'] === 'true',
            'members' => $members,
            'settings' => [
                'privacy' => (string)$group->settings->privacy,
                'notifications' => (string)$group->settings->notifications === 'true',
                'max_members' => (int)$group->settings->max_members
            ]
        ];
    }
} 