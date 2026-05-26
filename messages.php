<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = (int)$_SESSION['user_id']; // Force l'entier
$user_role = $_SESSION['user_role'];

// Requête SIMPLE sans paramètres dynamiques dans les noms de colonnes
// On utilise $user_id directement dans la requête, car c'est un entier (safe)
$user_id_safe = (int)$user_id; // Double sécurité

$sql = "
    SELECT 
        c.id,
        c.last_message,
        c.last_message_time,
        c.participant1,
        c.participant2,
        u.username as other_username,
        u.role as other_role
    FROM conversations c
    JOIN users u ON (u.id = c.participant1 OR u.id = c.participant2)
    WHERE (c.participant1 = $user_id_safe OR c.participant2 = $user_id_safe)
      AND u.id != $user_id_safe
    ORDER BY c.last_message_time DESC
";

$conversationsRaw = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Ajouter le compteur de messages non lus
$conversations = [];
foreach ($conversationsRaw as $conv) {
    $unreadStmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0");
    $unreadStmt->execute([$conv['id'], $user_id]);
    $conv['unread_count'] = (int)$unreadStmt->fetchColumn();
    $conv['other_id'] = ($conv['participant1'] == $user_id) ? $conv['participant2'] : $conv['participant1'];
    $conversations[] = $conv;
}

// TOUS les utilisateurs peuvent communiquer entre eux
// Un agent peut parler à un autre agent ou à un responsable
// Un responsable peut parler à tout le monde
$users = $db->query("SELECT id, username, role, secteur FROM users WHERE id != $user_id_safe ORDER BY role DESC, username")->fetchAll();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Total messages non lus
$unreadMsgCount = (int)$db->query("SELECT COUNT(*) FROM messages WHERE receiver_id = $user_id_safe AND is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Gestion des Agents</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .messages-container { display: flex; gap: 20px; min-height: 500px; }
        .conversations-list { width: 35%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .conversation-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; }
        .conversation-item:hover { background: #f5f5f5; }
        .conversation-item.active { background: #e8f0fe; border-left: 4px solid #667eea; }
        .conversation-name { font-weight: bold; margin-bottom: 5px; }
        .conversation-last { font-size: 0.85em; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conversation-time { font-size: 0.7em; color: #999; float: right; }
        .unread-badge { display: inline-block; background: #F44336; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7em; margin-left: 8px; }
        .chat-area { flex: 1; background: white; border-radius: 10px; display: flex; flex-direction: column; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .chat-header { padding: 15px; border-bottom: 1px solid #eee; background: #f9f9f9; border-radius: 10px 10px 0 0; }
        .chat-messages { flex: 1; padding: 15px; overflow-y: auto; min-height: 400px; max-height: 400px; }
        .message { margin-bottom: 15px; display: flex; }
        .message.sent { justify-content: flex-end; }
        .message.received { justify-content: flex-start; }
        .message-bubble { max-width: 70%; padding: 10px 15px; border-radius: 18px; word-wrap: break-word; }
        .message.sent .message-bubble { background: #667eea; color: white; border-bottom-right-radius: 4px; }
        .message.received .message-bubble { background: #f1f1f1; color: #333; border-bottom-left-radius: 4px; }
        .message-meta { font-size: 0.7em; margin-top: 5px; color: #999; }
        .chat-input { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        .new-conversation-btn { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-bottom: 15px; width: 100%; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        .user-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.7em; background: #e0e0e0; margin-left: 8px; }
        .user-badge.responsable { background: #667eea; color: white; }
        .user-badge.agent { background: #4CAF50; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 25px; border-radius: 10px; max-width: 400px; width: 90%; }
        .close { float: right; font-size: 28px; cursor: pointer; }
        .nav-links a { margin-left: 15px; text-decoration: none; color: #333; }
        .nav-links a.active { font-weight: bold; color: #667eea; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>💬 Messagerie interne</h1>
        <div class="nav-links">
            <?php if ($user_role === 'responsable'): ?>
                <a href="responsable_dashboard.php">Dashboard</a>
                <a href="schedules.php">Horaires</a>
                <a href="tasks.php">Tâches</a>
            <?php else: ?>
                <a href="agent_dashboard.php">Dashboard</a>
                <a href="my_schedules.php">Mes horaires</a>
                <a href="my_tasks.php">Mes tâches</a>
            <?php endif; ?>
            <a href="messages.php" class="active">💬 Messages <?php if ($unreadMsgCount > 0): ?><span style="background:red; color:white; border-radius:50%; padding:2px 6px;"><?= $unreadMsgCount ?></span><?php endif; ?></a>
            <a href="notifications.php">🔔 Notifications</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <div class="messages-container">
            <div class="conversations-list">
                <button class="new-conversation-btn" onclick="openNewConvModal()">+ Nouvelle conversation</button>
                <button class="new-conversation-btn" onclick="openGroupMessageModal()">👥 Envoyer à plusieurs agents</button>
                <?php if (empty($conversations)): ?>
                    <div class="empty-state">Aucune conversation</div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item" data-conv-id="<?= $conv['id'] ?>" data-other-id="<?= $conv['other_id'] ?>" data-other-name="<?= htmlspecialchars($conv['other_username']) ?>" onclick="loadConversation(<?= $conv['id'] ?>, '<?= addslashes($conv['other_username']) ?>', <?= $conv['other_id'] ?>)">
                            <div>
                                <span class="conversation-name"><?= htmlspecialchars($conv['other_username']) ?></span>
                                <span class="user-badge <?= $conv['other_role'] ?>"><?= $conv['other_role'] === 'responsable' ? 'Responsable' : 'Agent' ?></span>
                                <?php if ($conv['unread_count'] > 0): ?><span class="unread-badge"><?= $conv['unread_count'] ?></span><?php endif; ?>
                                <span class="conversation-time"><?= $conv['last_message_time'] ? date('d/m H:i', strtotime($conv['last_message_time'])) : '' ?></span>
                            </div>
                            <div class="conversation-last"><?= htmlspecialchars(substr($conv['last_message'] ?? '', 0, 50)) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="chat-area">
                <div class="chat-header" id="chat-header"><strong>Sélectionnez une conversation</strong></div>
                <div class="chat-messages" id="chat-messages"><div class="empty-state">👈 Cliquez sur une conversation</div></div>
                <div class="chat-input" id="chat-input" style="display: none;">
                    <input type="text" id="message-input" placeholder="Écrivez votre message..." onkeypress="if(event.key==='Enter') sendMessage()">
                    <button class="btn-primary" onclick="sendMessage()">Envoyer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="newConvModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeNewConvModal()">&times;</span>
        <h2>➕ Nouvelle conversation</h2>
        <form method="POST" action="send_message.php">
            <div class="form-group">
                <label>Destinataire</label>
                <select name="receiver_id" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?> (<?= $user['role'] === 'responsable' ? 'Responsable' : 'Agent' ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="first_message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer</button>
        </form>
    </div>
</div>
<div id="groupMessageModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <span class="close" onclick="closeGroupMessageModal()">&times;</span>
        <h2>👥 Message groupé</h2>
        <form method="POST" action="send_group_message.php">
            <div class="form-group">
                <label>Sélectionner les destinataires</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                    <?php
                    $agents = $db->query("SELECT id, username, secteur FROM users WHERE role = 'agent' ORDER BY username")->fetchAll();
                    foreach ($agents as $agent):
                    ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" name="recipients[]" value="<?= $agent['id'] ?>">
                            <?= htmlspecialchars($agent['username']) ?> (<?= htmlspecialchars($agent['secteur'] ?? 'Secteur?') ?>)
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" rows="4" required placeholder="Votre message aux agents..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer à tous</button>
        </form>
    </div>
</div>

<script>
function openGroupMessageModal() {
    document.getElementById('groupMessageModal').style.display = 'flex';
}
function closeGroupMessageModal() {
    document.getElementById('groupMessageModal').style.display = 'none';
}
</script>
<script>
let currentConvId = null, autoRefresh = null;
let currentUserId = <?= $user_id ?>;

function loadConversation(convId, otherName, otherId) {
    currentConvId = convId;
    document.getElementById('chat-header').innerHTML = '<strong>💬 Conversation avec ' + otherName + '</strong>';
    document.getElementById('chat-input').style.display = 'flex';
    fetch('get_messages.php?conv_id=' + convId)
        .then(r => r.json())
        .then(data => {
            let div = document.getElementById('chat-messages');
            if (data.success && data.messages) {
                div.innerHTML = '';
                data.messages.forEach(msg => addMessageToChat(msg.message, msg.sender_id == currentUserId, msg.created_at));
                div.scrollTop = div.scrollHeight;
            } else div.innerHTML = '<div class="empty-state">Aucun message</div>';
        });
    markAsRead(convId);
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.convId == convId) {
            item.classList.add('active');
            let badge = item.querySelector('.unread-badge');
            if (badge) badge.remove();
        }
    });
    if (autoRefresh) clearInterval(autoRefresh);
    autoRefresh = setInterval(refreshMessages, 3000);
}

function refreshMessages() {
    if (!currentConvId) return;
    fetch('get_messages.php?conv_id=' + currentConvId)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.messages) {
                let div = document.getElementById('chat-messages');
                let currentCount = div.querySelectorAll('.message').length;
                if (data.messages.length > currentCount) {
                    div.innerHTML = '';
                    data.messages.forEach(msg => addMessageToChat(msg.message, msg.sender_id == currentUserId, msg.created_at));
                    div.scrollTop = div.scrollHeight;
                    if (data.messages[data.messages.length-1].sender_id != currentUserId) markAsRead(currentConvId);
                }
            }
        });
}

function addMessageToChat(message, isSent, time) {
    let div = document.getElementById('chat-messages');
    let msgDiv = document.createElement('div');
    msgDiv.className = 'message ' + (isSent ? 'sent' : 'received');
    let timeStr = time ? new Date(time).toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'}) : '';
    msgDiv.innerHTML = `<div class="message-bubble">${escapeHtml(message)}<div class="message-meta">${timeStr}</div></div>`;
    div.appendChild(msgDiv);
}

function sendMessage() {
    let input = document.getElementById('message-input');
    let message = input.value.trim();
    if (!message || !currentConvId) return;
    fetch('send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'conv_id=' + currentConvId + '&message=' + encodeURIComponent(message)
    }).then(r => r.json()).then(data => {
        if (data.success) {
            input.value = '';
            addMessageToChat(message, true, new Date());
            document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
            updateConversationLast(currentConvId, message);
        } else alert('Erreur: ' + data.error);
    });
}

function markAsRead(convId) { fetch('mark_messages_read.php?conv_id=' + convId); }
function updateConversationLast(convId, message) {
    let item = document.querySelector(`.conversation-item[data-conv-id="${convId}"]`);
    if (item) {
        let lastDiv = item.querySelector('.conversation-last');
        if (lastDiv) lastDiv.textContent = message.substring(0, 50);
        let timeSpan = item.querySelector('.conversation-time');
        if (timeSpan) timeSpan.textContent = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
    }
}
function escapeHtml(t) { let d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function openNewConvModal() { document.getElementById('newConvModal').style.display = 'flex'; }
function closeNewConvModal() { document.getElementById('newConvModal').style.display = 'none'; }
window.onclick = function(e) { let m = document.getElementById('newConvModal'); if (e.target == m) closeNewConvModal(); }
window.onbeforeunload = function() { if (autoRefresh) clearInterval(autoRefresh); }
</script>
</body>
</html>