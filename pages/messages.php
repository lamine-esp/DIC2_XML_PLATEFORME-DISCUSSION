<?php
if (!$user->isLoggedIn()) {
    redirect('index.php?page=login');
}
$currentUser = $user->getCurrentUser();
$contacts = $contact->getUserContacts($currentUser['id']);
$recentConversations = $message->getRecentConversations($currentUser['id']);

// Filtrer les conversations avec utilisateur inconnu
$recentConversations = array_filter($recentConversations, function($conv) use ($user, $group) {
    if ($conv['type'] === 'user') {
        $other = $user->getUserById($conv['id']);
        return $other !== null && isset($other['profile']);
    }
    if ($conv['type'] === 'group') {
        $g = $group->getGroupById($conv['id']);
        return $g && isset($g['name']) && $g['name'] !== 'Équipe Projet DSS';
    }
    return true;
});
// Réindexer le tableau
$recentConversations = array_values($recentConversations);

// Gestion de l'envoi de message ou de fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $recipientType = $_POST['recipient_type'];
    $recipientId = $_POST['recipient_id'];
    $content = trim($_POST['content'] ?? '');
    $filePath = null;
    $fileName = null;
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = 'data/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            // Fichier uploadé avec succès
        } else {
            $filePath = null;
        }
    }
    if ($recipientType === 'user') {
        $message->sendMessage($currentUser['id'], $recipientId, $content, $filePath, $fileName);
    } else {
        $message->sendGroupMessage($currentUser['id'], $recipientId, $content, $filePath, $fileName);
    }
    // Rafraîchir la page pour éviter le renvoi du formulaire
    header('Location: index.php?page=messages&conversation=' . $recipientId . '&type=' . $recipientType);
    exit;
}
// Par défaut, afficher la première conversation si aucune sélectionnée
$activeConversationId = $_GET['conversation'] ?? null;
$activeConversationType = $_GET['type'] ?? 'user';

// Si aucune conversation n'est sélectionnée et qu'il y a des conversations récentes, sélectionner la première
if (!$activeConversationId && !empty($recentConversations)) {
    $activeConversationId = $recentConversations[0]['id'] ?? null;
    $activeConversationType = $recentConversations[0]['type'] ?? 'user';
}

$conversationMessages = [];
$conversationTitle = 'Aucune conversation';

if ($activeConversationId) {
    if ($activeConversationType === 'user') {
        $conversationMessages = $message->getConversation($currentUser['id'], $activeConversationId);
        // Marquer comme lus tous les messages reçus dans cette conversation
        $message->markConversationAsRead($currentUser['id'], $activeConversationId);
        $otherUser = $user->getUserById($activeConversationId);
        if ($otherUser) {
            $conversationTitle = $otherUser['profile']['first_name'] . ' ' . $otherUser['profile']['last_name'];
        } else {
            $conversationTitle = 'Utilisateur inconnu';
        }
    } else {
        $conversationMessages = $message->getGroupMessages($activeConversationId);
        // Marquer comme lus tous les messages de groupe reçus
        $message->markGroupMessagesAsRead($activeConversationId, $currentUser['id']);
        $groupInfo = $group->getGroupById($activeConversationId);
        if ($groupInfo) {
            $conversationTitle = $groupInfo['name'];
        } else {
            $conversationTitle = 'Groupe inconnu';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .messages-container { background: #ece5dd; }
        .message.sent { background: #dcf8c6; border-radius: 10px 10px 0 10px; }
        .message.received { background: #fff; border-radius: 10px 10px 10px 0; }
        .message { padding: 8px 12px; margin-bottom: 4px; }
        .conversation-list .active { background: #25d366 !important; color: #fff; }
        .input-group .btn-attach { background: #f0f0f0; border: none; }
        .input-group .btn-attach:hover { background: #e0e0e0; }
    </style>
</head>
<body>
<?php include 'pages/_navbar.php'; ?>
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Conversations</h5>
                </div>
                <div class="card-body p-0 conversation-list" style="max-height: 70vh; overflow-y: auto;">
                    <?php if (empty($recentConversations)): ?>
                        <p class="text-muted text-center py-4">Aucune conversation</p>
                    <?php else: ?>
                        <?php foreach ($recentConversations as $conv): ?>
                            <a href="index.php?page=messages&conversation=<?php echo $conv['id']; ?>&type=<?php echo $conv['type']; ?>"
                               class="conversation-item list-group-item list-group-item-action<?php if ($conv['id'] == $activeConversationId) echo ' active'; ?>"
                               data-conversation-id="<?php echo $conv['id']; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold conversation-name">
                                            <?php if ($conv['type'] === 'user'): ?>
                                                <?php $other = $user->getUserById($conv['id']);
                                                if ($other): ?>
                                                    <?php echo htmlspecialchars($other['profile']['first_name'] . ' ' . $other['profile']['last_name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Utilisateur inconnu</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php $g = $group->getGroupById($conv['id']);
                                                if ($g): ?>
                                                    <?php echo htmlspecialchars($g['name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Groupe inconnu</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small text-truncate-2">
                                            <?php if (isset($conv['last_message']) && isset($conv['last_message']['content'])): ?>
                                                <?php echo htmlspecialchars($conv['last_message']['content']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun message</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ms-2 text-end">
                                        <small class="text-muted">
                                            <?php if (isset($conv['last_message']) && isset($conv['last_message']['timestamp'])): ?>
                                                <?php echo date('d/m H:i', strtotime($conv['last_message']['timestamp'])); ?>
                                            <?php else: ?>
                                                --
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Zone de chat -->
        <div class="col-md-8 col-lg-9 mb-3">
            <div class="card h-100">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($conversationTitle); ?></h5>
                </div>
                <div class="card-body messages-container" style="height: 60vh; overflow-y: auto; background: #ece5dd;">
                    <?php if (empty($conversationMessages)): ?>
                        <p class="text-muted text-center py-4">Aucun message</p>
                    <?php else: ?>
                        <?php foreach (
                            $conversationMessages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $currentUser['id'] ? 'sent ms-auto' : 'received'; ?> mb-2" style="max-width: 70%;">
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                    <?php if (!empty($msg['attachments'])): ?>
                                        <?php foreach ($msg['attachments'] as $file): ?>
                                            <div class="mt-2">
                                                <a href="<?php echo htmlspecialchars($file['path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($file['name']); ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="message-time text-end small">
                                    <?php echo date('d/m/Y H:i', strtotime($msg['timestamp'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($activeConversationId): ?>
                <div class="card-footer bg-white">
                    <form method="post" action="index.php?page=messages&conversation=<?php echo $activeConversationId; ?>&type=<?php echo $activeConversationType; ?>" enctype="multipart/form-data">
                        <div class="input-group">
                            <button class="btn btn-attach" type="button" onclick="document.getElementById('fileInput').click();"><i class="fas fa-paperclip"></i></button>
                            <input type="file" name="file" id="fileInput" style="display:none;" onchange="document.getElementById('fileName').innerText = this.files[0] ? this.files[0].name : '';">
                            <span id="fileName" class="mx-2 text-muted small"></span>
                            <textarea name="content" class="form-control" placeholder="Votre message..." maxlength="<?php echo MAX_MESSAGE_LENGTH; ?>" rows="1"></textarea>
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="recipient_type" value="<?php echo $activeConversationType; ?>">
                            <input type="hidden" name="recipient_id" value="<?php echo $activeConversationId; ?>">
                            <button class="btn btn-primary send-message-btn" type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html> 