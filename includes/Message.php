<?php
/**
 * Classe Message pour gérer les messages
 * Gère l'envoi, la réception et la gestion des messages
 */

require_once 'config/config.php';
require_once 'includes/Database.php';

class Message {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Envoie un message
     */
    public function sendMessage($senderId, $recipientId, $content, $filePath = null, $fileName = null) {
        // Validation des données
        $errors = $this->validateMessageData($senderId, 'user', $recipientId, $content);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        $messages = $this->db->getMessagesXml();
        $newId = $this->db->generateId($messages);
        // Créer le nouveau message
        $message = $messages->addChild('message');
        $message->addAttribute('id', $newId);
        $message->addAttribute('type', 'text');
        $message->addAttribute('is_edited', 'false');
        $message->addAttribute('is_deleted', 'false');
        $message->addChild('sender_id', $senderId);
        $message->addChild('recipient_type', 'user');
        $message->addChild('recipient_id', $recipientId);
        $message->addChild('content', htmlspecialchars($content));
        $message->addChild('timestamp', date('Y-m-d\TH:i:s'));
        $message->addChild('is_read', 'false');
        // Ajouter la pièce jointe si présente
        if ($filePath && $fileName) {
            $attachmentsNode = $message->addChild('attachments');
            $file = $attachmentsNode->addChild('file', $fileName);
            $file->addAttribute('name', $fileName);
            $file->addAttribute('size', file_exists($filePath) ? filesize($filePath) : 0);
            $file->addAttribute('type', pathinfo($fileName, PATHINFO_EXTENSION));
            $file->addAttribute('path', $filePath);
        }
        // Sauvegarder
        if ($this->db->saveXml($messages, MESSAGES_XML)) {
            return ['success' => true, 'message_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => "Erreur lors de l'envoi du message"]];
        }
    }
    
    /**
     * Envoie un message à un groupe
     */
    public function sendGroupMessage($senderId, $groupId, $content, $filePath = null, $fileName = null) {
        // Validation des données
        $errors = $this->validateMessageData($senderId, 'group', $groupId, $content);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $messages = $this->db->getMessagesXml();
        $newId = $this->db->generateId($messages);

        // Créer le nouveau message
        $message = $messages->addChild('message');
        $message->addAttribute('id', $newId);
        $message->addAttribute('type', 'text');
        $message->addAttribute('is_edited', 'false');
        $message->addAttribute('is_deleted', 'false');

        $message->addChild('sender_id', $senderId);
        $message->addChild('recipient_type', 'group');
        $message->addChild('recipient_id', $groupId);
        $message->addChild('content', htmlspecialchars($content));
        $message->addChild('timestamp', date('Y-m-d\TH:i:s'));
        $message->addChild('is_read', 'false');

        // Ajouter la pièce jointe si présente
        if ($filePath && $fileName) {
            $attachmentsNode = $message->addChild('attachments');
            $file = $attachmentsNode->addChild('file', $fileName);
            $file->addAttribute('name', $fileName);
            $file->addAttribute('size', file_exists($filePath) ? filesize($filePath) : 0);
            $file->addAttribute('type', pathinfo($fileName, PATHINFO_EXTENSION));
            $file->addAttribute('path', $filePath);
        }

        // Sauvegarder
        if ($this->db->saveXml($messages, MESSAGES_XML)) {
            return ['success' => true, 'message_id' => $newId];
        } else {
            return ['success' => false, 'errors' => ['general' => "Erreur lors de l'envoi du message"]];
        }
    }
    
    /**
     * Obtient les messages d'une conversation
     */
    public function getConversation($userId1, $userId2, $limit = MESSAGES_PER_PAGE, $offset = 0) {
        $messages = $this->db->getMessagesXml();
        $conversation = [];
        
        foreach ($messages->message as $message) {
            $senderId = (string)$message->sender_id;
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            
            // Messages entre les deux utilisateurs
            if ($recipientType === 'user' && 
                (($senderId === $userId1 && $recipientId === $userId2) ||
                 ($senderId === $userId2 && $recipientId === $userId1))) {
                $conversation[] = $this->formatMessageData($message);
            }
        }
        
        // Trier par timestamp (plus récent en premier)
        usort($conversation, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Pagination
        $conversation = array_slice($conversation, $offset, $limit);
        
        return array_reverse($conversation); // Remettre dans l'ordre chronologique
    }
    
    /**
     * Obtient les messages d'un groupe
     */
    public function getGroupMessages($groupId, $limit = MESSAGES_PER_PAGE, $offset = 0) {
        $messages = $this->db->getMessagesXml();
        $groupMessages = [];
        
        foreach ($messages->message as $message) {
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            
            if ($recipientType === 'group' && $recipientId === $groupId) {
                $groupMessages[] = $this->formatMessageData($message);
            }
        }
        
        // Trier par timestamp (plus récent en premier)
        usort($groupMessages, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        // Pagination
        $groupMessages = array_slice($groupMessages, $offset, $limit);
        
        return array_reverse($groupMessages); // Remettre dans l'ordre chronologique
    }
    
    /**
     * Obtient les conversations récentes d'un utilisateur
     */
    public function getRecentConversations($userId) {
        $messages = $this->db->getMessagesXml();
        $conversations = [];
        
        foreach ($messages->message as $message) {
            $senderId = (string)$message->sender_id;
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            
            // Messages envoyés par l'utilisateur
            if ($senderId === $userId) {
                $conversationKey = $recipientType . '_' . $recipientId;
                if (!isset($conversations[$conversationKey])) {
                    $conversations[$conversationKey] = [
                        'type' => $recipientType,
                        'id' => $recipientId,
                        'last_message' => $this->formatMessageData($message)
                    ];
                }
            }
            
            // Messages reçus par l'utilisateur
            if ($recipientType === 'user' && $recipientId === $userId) {
                $conversationKey = 'user_' . $senderId;
                if (!isset($conversations[$conversationKey])) {
                    $conversations[$conversationKey] = [
                        'type' => 'user',
                        'id' => $senderId,
                        'last_message' => $this->formatMessageData($message)
                    ];
                }
            }
        }
        
        // Trier par timestamp du dernier message
        usort($conversations, function($a, $b) {
            return strtotime($b['last_message']['timestamp']) - strtotime($a['last_message']['timestamp']);
        });
        
        return $conversations;
    }
    
    /**
     * Marque un message comme lu
     */
    public function markAsRead($messageId) {
        $messages = $this->db->getMessagesXml();
        $message = $this->db->findById($messages, $messageId);
        
        if ($message) {
            $message->is_read = 'true';
            return $this->db->saveXml($messages, MESSAGES_XML);
        }
        
        return false;
    }
    
    /**
     * Marque tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead($userId1, $userId2) {
        $messages = $this->db->getMessagesXml();
        $updated = false;
        
        foreach ($messages->message as $message) {
            $senderId = (string)$message->sender_id;
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            
            if ($recipientType === 'user' && 
                (($senderId === $userId1 && $recipientId === $userId2) ||
                 ($senderId === $userId2 && $recipientId === $userId1))) {
                $message->is_read = 'true';
                $updated = true;
            }
        }
        
        if ($updated) {
            return $this->db->saveXml($messages, MESSAGES_XML);
        }
        
        return false;
    }
    
    /**
     * Marque tous les messages d'un groupe comme lus
     */
    public function markGroupMessagesAsRead($groupId, $userId) {
        $messages = $this->db->getMessagesXml();
        $updated = false;
        
        foreach ($messages->message as $message) {
            $senderId = (string)$message->sender_id;
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            
            if ($recipientType === 'group' && $recipientId === $groupId && $senderId !== $userId) {
                $message->is_read = 'true';
                $updated = true;
            }
        }
        
        if ($updated) {
            return $this->db->saveXml($messages, MESSAGES_XML);
        }
        
        return false;
    }
    
    /**
     * Supprime un message
     */
    public function deleteMessage($messageId, $userId) {
        $messages = $this->db->getMessagesXml();
        $message = $this->db->findById($messages, $messageId);
        
        if (!$message) {
            return ['success' => false, 'errors' => ['message' => getErrorMessage('message_not_found')]];
        }
        
        // Vérifier que l'utilisateur est l'expéditeur
        if ((string)$message->sender_id !== $userId) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        $message['is_deleted'] = 'true';
        
        if ($this->db->saveXml($messages, MESSAGES_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la suppression']];
        }
    }
    
    /**
     * Modifie un message
     */
    public function editMessage($messageId, $userId, $newContent) {
        $messages = $this->db->getMessagesXml();
        $message = $this->db->findById($messages, $messageId);
        
        if (!$message) {
            return ['success' => false, 'errors' => ['message' => getErrorMessage('message_not_found')]];
        }
        
        // Vérifier que l'utilisateur est l'expéditeur
        if ((string)$message->sender_id !== $userId) {
            return ['success' => false, 'errors' => ['permission' => getErrorMessage('unauthorized')]];
        }
        
        // Validation du contenu
        if (empty($newContent)) {
            return ['success' => false, 'errors' => ['content' => 'Le contenu ne peut pas être vide']];
        }
        
        if (strlen($newContent) > MAX_MESSAGE_LENGTH) {
            return ['success' => false, 'errors' => ['content' => 'Le message est trop long']];
        }
        
        $message->content = htmlspecialchars($newContent);
        $message['is_edited'] = 'true';
        
        if ($this->db->saveXml($messages, MESSAGES_XML)) {
            return ['success' => true];
        } else {
            return ['success' => false, 'errors' => ['general' => 'Erreur lors de la modification']];
        }
    }
    
    /**
     * Obtient le nombre de messages non lus pour un utilisateur
     */
    public function getUnreadCount($userId) {
        $messages = $this->db->getMessagesXml();
        $unreadCount = 0;
        
        foreach ($messages->message as $message) {
            $senderId = (string)$message->sender_id;
            $recipientType = (string)$message->recipient_type;
            $recipientId = (string)$message->recipient_id;
            $isRead = (string)$message->is_read;
            
            // Messages directs non lus reçus
            if ($recipientType === 'user' && $recipientId === $userId && $senderId !== $userId && $isRead === 'false') {
                $unreadCount++;
            }
            
            // Messages de groupe non lus reçus
            if ($recipientType === 'group' && $recipientId === $userId && $senderId !== $userId && $isRead === 'false') {
                $unreadCount++;
            }
        }
        
        return $unreadCount;
    }
    
    /**
     * Démarre une nouvelle conversation avec un contact
     * Crée un message de bienvenue automatique si c'est la première conversation
     */
    public function startConversation($userId, $contactUserId) {
        // Vérifier si une conversation existe déjà
        $existingMessages = $this->getConversation($userId, $contactUserId, 1);
        
        if (empty($existingMessages)) {
            // Créer un message de bienvenue automatique
            $welcomeMessage = "👋 Bonjour ! J'ai commencé une conversation avec vous.";
            return $this->sendMessage($userId, $contactUserId, $welcomeMessage);
        }
        
        return ['success' => true, 'conversation_exists' => true];
    }
    
    /**
     * Vérifie si une conversation existe entre deux utilisateurs
     */
    public function conversationExists($userId1, $userId2) {
        $messages = $this->getConversation($userId1, $userId2, 1);
        return !empty($messages);
    }
    
    /**
     * Valide les données du message
     */
    private function validateMessageData($senderId, $recipientType, $recipientId, $content) {
        $errors = [];
        
        if (empty($senderId)) {
            $errors['sender'] = 'Expéditeur requis';
        }
        
        if (empty($recipientType) || !in_array($recipientType, ['user', 'group'])) {
            $errors['recipient_type'] = 'Type de destinataire invalide';
        }
        
        if (empty($recipientId)) {
            $errors['recipient'] = 'Destinataire requis';
        }
        
        if (empty($content)) {
            $errors['content'] = 'Le contenu du message ne peut pas être vide';
        } elseif (strlen($content) > MAX_MESSAGE_LENGTH) {
            $errors['content'] = 'Le message est trop long (max ' . MAX_MESSAGE_LENGTH . ' caractères)';
        }
        
        return $errors;
    }
    
    /**
     * Formate les données du message
     */
    private function formatMessageData($message) {
        $attachments = [];
        if (isset($message->attachments)) {
            foreach ($message->attachments->file as $file) {
                $attachments[] = [
                    'name' => (string)$file['name'],
                    'size' => (string)$file['size'],
                    'type' => (string)$file['type'],
                    'filename' => (string)$file
                ];
            }
        }
        
        return [
            'id' => (string)$message['id'],
            'sender_id' => (string)$message->sender_id,
            'recipient_type' => (string)$message->recipient_type,
            'recipient_id' => (string)$message->recipient_id,
            'content' => (string)$message->content,
            'timestamp' => (string)$message->timestamp,
            'is_read' => (string)$message->is_read === 'true',
            'type' => (string)$message['type'],
            'is_edited' => (string)$message['is_edited'] === 'true',
            'is_deleted' => (string)$message['is_deleted'] === 'true',
            'attachments' => $attachments
        ];
    }
} 