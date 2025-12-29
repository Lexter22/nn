// Chat Widget Functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chatToggle');
    const chatBox = document.getElementById('chatBox');
    const startChatBtn = document.getElementById('startChatBtn');
    
    // Function to open chat
    function openChat() {
        chatBox.style.display = 'flex';
    }
    
    // Function to close chat
    function closeChat() {
        chatBox.style.display = 'none';
    }
    
    // Function to toggle chat
    function toggleChat() {
        if (chatBox.style.display === 'flex') {
            closeChat();
        } else {
            openChat();
        }
    }
    
    // Floating button toggle
    chatToggle.addEventListener('click', toggleChat);
    
    // CTA button opens chat
    if (startChatBtn) {
        startChatBtn.addEventListener('click', openChat);
    }
    
    // Close chat when clicking outside
    document.addEventListener('click', function(event) {
        if (!chatToggle.contains(event.target) && 
            !chatBox.contains(event.target) && 
            !startChatBtn.contains(event.target)) {
            closeChat();
        }
    });
    
    // Send message functionality (placeholder)
    const sendBtn = document.querySelector('.chat-send-btn');
    const chatInput = document.querySelector('.chat-input');
    
    if (sendBtn && chatInput) {
        sendBtn.addEventListener('click', function() {
            const message = chatInput.value.trim();
            if (message) {
                // Placeholder for sending message
                console.log('Message sent:', message);
                chatInput.value = '';
            }
        });
        
        // Send on Enter key
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendBtn.click();
            }
        });
    }
});