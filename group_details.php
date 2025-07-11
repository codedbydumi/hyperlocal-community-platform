<?php
session_start(); // Start the session to access user data

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "final";  // Update with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get the user's email from the session
$userEmail = $_SESSION['email'];

// Check if group_id is passed in the URL
if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    // Fetch group information from the database - FIXED: Added backticks around 'groups'
    $sql = "SELECT * FROM `groups` WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch group data
        $group = $result->fetch_assoc();
    } else {
        die("Group not found.");
    }

    // Check if the user is already a member of the group
    $checkMembershipQuery = "SELECT * FROM group_memberships WHERE user_email = ? AND group_id = ?";
    $stmt = $conn->prepare($checkMembershipQuery);
    $stmt->bind_param("si", $userEmail, $group_id);
    $stmt->execute();
    $membershipResult = $stmt->get_result();
    $isMember = $membershipResult->num_rows > 0;

    // Count total members
    $memberCountQuery = "SELECT COUNT(*) as member_count FROM group_memberships WHERE group_id = ?";
    $stmt = $conn->prepare($memberCountQuery);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $memberCountResult = $stmt->get_result();
    $memberCount = $memberCountResult->fetch_assoc()['member_count'];
} else {
    die("Group ID not provided.");
}

// Fetch all messages for the group
$getMessagesQuery = "SELECT * FROM group_chat WHERE group_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($getMessagesQuery);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$messagesResult = $stmt->get_result();

// Check if the user just joined the group (via query parameter)
$joined = isset($_GET['joined']) ? $_GET['joined'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Chat - <?php echo htmlspecialchars($group['name'] ?? 'Chat'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 90vh;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

  .nav-buttons {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.nav-button {
    padding: 10px 18px;
    background: rgba(255, 255, 255, 0.9);
    color: #4a5568;
    text-decoration: none;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.nav-button:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    color: #2d3748;
}

.nav-button:active {
    transform: translateY(0px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.nav-button i {
    font-size: 0.9rem;
    color: #667eea;
}

.nav-button:hover i {
    color: #5a67d8;
}

/* Alternative style option - if you prefer colored buttons */
.nav-button.colored {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.nav-button.colored:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    border-color: rgba(255, 255, 255, 0.5);
}

.nav-button.colored i {
    color: rgba(255, 255, 255, 0.9);
}
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .chat-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .chat-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 10px;
        }

        .connection-status {
            font-size: 0.9rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .connection-status.connected {
            color: #4ade80;
        }

        .connection-status.disconnected {
            color: #f87171;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
        }

        .message {
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            animation: messageSlideIn 0.5s ease-out;
            position: relative;
        }

        .message.own-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: 20%;
            border-bottom-right-radius: 5px;
        }

        .message.other-message {
            background: white;
            border-left: 4px solid #667eea;
            margin-right: 20%;
            border-bottom-left-radius: 5px;
        }

        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .own-message .message-avatar {
            background: rgba(255, 255, 255, 0.2);
        }

        .other-message .message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .message-user {
            font-weight: 600;
            flex: 1;
        }

        .own-message .message-user {
            color: rgba(255, 255, 255, 0.9);
        }

        .other-message .message-user {
            color: #334155;
        }

        .message-time {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .message-content {
            line-height: 1.5;
            word-wrap: break-word;
        }

        .own-message .message-content {
            color: rgba(255, 255, 255, 0.95);
        }

        .other-message .message-content {
            color: #475569;
        }

        .chat-input {
            display: flex;
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .message-input:focus {
            border-color: #667eea;
        }

        .send-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }

        .send-button:hover {
            transform: scale(1.05);
        }

        .send-button:active {
            transform: scale(0.95);
        }

        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .typing-indicator {
            display: none;
            padding: 15px 20px;
            color: #64748b;
            font-style: italic;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            margin: 0 20px 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            color: #334155;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease-out;
        }

        .notification.success {
            border-left: 4px solid #22c55e;
        }

        .notification.error {
            border-left: 4px solid #ef4444;
        }

        .online-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 12px;
            height: 12px;
            background: #4ade80;
            border-radius: 50%;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .chat-container {
                width: 95%;
                height: 95vh;
                border-radius: 15px;
            }

            .nav-buttons {
                padding: 10px 15px;
                gap: 8px;
            }

            .nav-button {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .chat-header {
                padding: 15px;
            }

            .chat-title {
                font-size: 1.3rem;
            }

            .chat-messages {
                padding: 15px;
            }

            .message {
                padding: 12px;
                margin-bottom: 12px;
            }

            .message.own-message {
                margin-left: 10%;
            }

            .message.other-message {
                margin-right: 10%;
            }

            .message-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }

            .chat-input {
                padding: 15px;
            }

            .message-input {
                padding: 10px 14px;
                font-size: 0.9rem;
            }

            .send-button {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="nav-buttons">
            <a href="javascript:history.back()" class="nav-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="index.php" class="nav-button">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="creategroup.php" class="nav-button">
                <i class="fas fa-plus"></i> New Community
            </a>
        </div>

        <div class="chat-header">
            <div class="chat-title"><?php echo htmlspecialchars($group['name'] ?? 'Group Chat'); ?></div>
            <div class="chat-subtitle"><?php echo $memberCount; ?> members</div>
            <div id="connectionStatus" class="connection-status connected">
                <i class="fas fa-wifi"></i> Connected
            </div>
            <div class="online-indicator"></div>
        </div>

        <div id="chatMessages" class="chat-messages">
            <!-- Messages will be loaded here -->
        </div>

        <div id="typingIndicator" class="typing-indicator">
            <i class="fas fa-ellipsis-h"></i> Someone is typing...
        </div>

        <div class="chat-input">
            <input type="text" id="messageInput" class="message-input" placeholder="Type your message..." maxlength="1000">
            <button id="sendButton" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        // Global variables
        let lastMessageId = 0;
        let refreshInterval;
        let isConnected = true;
        let isTyping = false;
        let typingTimeout;
        const groupId = <?php echo json_encode($group_id); ?>;
        const currentUserEmail = <?php echo json_encode($userEmail); ?>;
        const REFRESH_INTERVAL = 1000; // 1 second for real-time feel

        // Initialize chat when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeChat();
            setupEventListeners();
            loadInitialMessages();
            startRealTimeRefresh();
            
            // Show join notification if user just joined
            <?php if ($joined): ?>
            showNotification('Welcome to the group! 🎉', 'success');
            <?php endif; ?>
        });

        function initializeChat() {
            // Get user initials for avatar
            const email = currentUserEmail;
            const initials = email.split('@')[0].substring(0, 2).toUpperCase();
            window.userInitials = initials;
            
            // Set page title with group name
            document.title = `<?php echo htmlspecialchars($group['name'] ?? 'Group Chat'); ?> - Chat`;
        }

        function setupEventListeners() {
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');

            // Send message on button click
            sendButton.addEventListener('click', sendMessage);

            // Send message on Enter key press
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Handle typing indicators
            messageInput.addEventListener('input', function() {
                sendButton.disabled = this.value.trim() === '';
                handleTyping();
            });

            // Handle focus and blur for better UX
            messageInput.addEventListener('focus', function() {
                scrollToBottom();
            });
        }

        function handleTyping() {
            if (!isTyping) {
                isTyping = true;
                // In a real implementation, you'd send a typing indicator to other users
                // sendTypingIndicator(true);
            }
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
                // sendTypingIndicator(false);
            }, 1000);
        }

        function loadInitialMessages() {
            fetch(`get_messages.php?group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        const chatMessages = document.getElementById('chatMessages');
                        chatMessages.innerHTML = '';
                        
                        data.messages.forEach(message => {
                            addMessageToChat(message);
                        });
                        
                        if (data.messages.length > 0) {
                            lastMessageId = Math.max(...data.messages.map(m => m.id));
                        }
                        
                        scrollToBottom();
                        updateConnectionStatus(true);
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    showNotification('Failed to load messages', 'error');
                    updateConnectionStatus(false);
                });
        }

        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendButton');
            const message = messageInput.value.trim();

            if (!message) return;

            // Show optimistic message immediately
            const tempMessage = {
                id: 'temp-' + Date.now(),
                user_email: currentUserEmail,
                message: message,
                created_at: new Date().toISOString(),
                sending: true
            };
            
            addMessageToChat(tempMessage);
            messageInput.value = '';
            sendButton.disabled = true;

            const formData = new FormData();
            formData.append('group_id', groupId);
            formData.append('message', message);

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove temporary message
                const tempElement = document.querySelector(`[data-message-id="${tempMessage.id}"]`);
                if (tempElement) {
                    tempElement.remove();
                }
                
                if (data.success) {
                    // Real message will be loaded by refresh
                    refreshMessages();
                } else {
                    showNotification(data.error || 'Failed to send message', 'error');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                showNotification('Failed to send message', 'error');
                
                // Remove temporary message on error
                const tempElement = document.querySelector(`[data-message-id="${tempMessage.id}"]`);
                if (tempElement) {
                    tempElement.remove();
                }
            })
            .finally(() => {
                sendButton.disabled = false;
                messageInput.focus();
            });
        }

        function addMessageToChat(messageData) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Check if message already exists
            const existingMessage = document.querySelector(`[data-message-id="${messageData.id}"]`);
            if (existingMessage && !messageData.id.toString().startsWith('temp-')) {
                return;
            }
            
            const userEmail = messageData.user_email || 'Unknown User';
            const userInitials = userEmail.split('@')[0].substring(0, 2).toUpperCase();
            const isOwnMessage = userEmail === currentUserEmail;
            
            const messageTime = new Date(messageData.created_at).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            const messageClass = isOwnMessage ? 'own-message' : 'other-message';
            const sendingClass = messageData.sending ? 'sending' : '';

            const messageHtml = `
                <div class="message ${messageClass} ${sendingClass}" data-message-id="${messageData.id}">
                    <div class="message-header">
                        <div class="message-avatar">${userInitials}</div>
                        <span class="message-user">${isOwnMessage ? 'You' : escapeHtml(userEmail.split('@')[0])}</span>
                        <span class="message-time">${messageTime}</span>
                        ${messageData.sending ? '<i class="fas fa-clock" style="opacity: 0.5;"></i>' : ''}
                    </div>
                    <div class="message-content">${escapeHtml(messageData.message)}</div>
                </div>
            `;

            chatMessages.insertAdjacentHTML('beforeend', messageHtml);
            scrollToBottom();
            
            if (messageData.id && !messageData.id.toString().startsWith('temp-') && messageData.id > lastMessageId) {
                lastMessageId = messageData.id;
            }
        }

        function refreshMessages() {
            fetch(`get_messages.php?group_id=${groupId}&last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            addMessageToChat(message);
                        });
                        updateConnectionStatus(true);
                        
                        // Play notification sound for new messages from others
                        const hasNewMessages = data.messages.some(msg => msg.user_email !== currentUserEmail);
                        if (hasNewMessages && document.hidden) {
                            showNotification('New message received', 'success');
                        }
                    }
                    updateConnectionStatus(true);
                })
                .catch(error => {
                    console.error('Error refreshing messages:', error);
                    updateConnectionStatus(false);
                });
        }

        function startRealTimeRefresh() {
            // Clear any existing interval
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            
            // Refresh messages every second for real-time experience
            refreshInterval = setInterval(refreshMessages, REFRESH_INTERVAL);
            
            // Check connection status periodically
            setInterval(checkConnection, 5000);
        }

        function checkConnection() {
            fetch('check_connection.php')
                .then(response => response.ok)
                .then(isOnline => updateConnectionStatus(isOnline))
                .catch(() => updateConnectionStatus(false));
        }

        function updateConnectionStatus(connected) {
            const statusElement = document.getElementById('connectionStatus');
            const onlineIndicator = document.querySelector('.online-indicator');
            isConnected = connected;
            
            if (connected) {
                statusElement.className = 'connection-status connected';
                statusElement.innerHTML = '<i class="fas fa-wifi"></i> Connected';
                if (onlineIndicator) onlineIndicator.style.display = 'block';
            } else {
                statusElement.className = 'connection-status disconnected';
                statusElement.innerHTML = '<i class="fas fa-wifi-slash"></i> Disconnected';
                if (onlineIndicator) onlineIndicator.style.display = 'none';
            }
        }

        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            notification.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-out forwards';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Handle page visibility change for better performance
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Reduce refresh rate when tab is not visible
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = setInterval(refreshMessages, 5000);
                }
            } else {
                // Resume normal refresh rate when tab becomes visible
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
                startRealTimeRefresh();
                refreshMessages();
            }
        });

        // Handle window focus for immediate updates
        window.addEventListener('focus', function() {
            refreshMessages();
        });

        // Cleanup intervals when page is unloaded
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        // Handle online/offline events
        window.addEventListener('online', function() {
            updateConnectionStatus(true);
            refreshMessages();
            showNotification('Back online! 🌐', 'success');
        });

        window.addEventListener('offline', function() {
            updateConnectionStatus(false);
            showNotification('You are offline', 'error');
        });
    </script>
</body>
</html>