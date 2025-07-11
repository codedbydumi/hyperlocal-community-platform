document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.getElementById('sendMessage');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    
    // Get the group_id from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const groupId = urlParams.get('group_id');
    
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', function() {
            const message = messageInput.value.trim();
            
            if (message) {
                // Send the message via AJAX
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'send_message.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Clear the input field
                        messageInput.value = '';
                        
                        // Refresh the messages
                        refreshMessages();
                    }
                };
                xhr.send('message=' + encodeURIComponent(message) + '&group_id=' + encodeURIComponent(groupId));
            }
        });
    }
    
    // Function to vote on messages
    window.voteMessage = function(messageId, voteType) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'vote_message.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Refresh the messages to update vote counts
                refreshMessages();
            }
        };
        xhr.send('message_id=' + encodeURIComponent(messageId) + '&vote_type=' + encodeURIComponent(voteType));
    };
    
    // Function to refresh messages
    function refreshMessages() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_messages.php?group_id=' + encodeURIComponent(groupId), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                chatMessages.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    
    // Periodically refresh messages
    setInterval(refreshMessages, 2000); // Refresh every 5 seconds
});