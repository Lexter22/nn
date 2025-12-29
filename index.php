<?php
$page_title = "Ayisha's Clinic - Your Health, Our Priority";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php'; // XAMPP MySQL connection available via getDB()
$pdo = getDB(); // Ready-to-use PDO for any queries on this page
?>

<style>
/* Magnify effects */
.magnify-nav, .magnify-cta {
    display: inline-block;
    transition: transform 160ms ease;
    transform-origin: center;
    will-change: transform;
}
.magnify-nav:hover, .magnify-nav:focus-visible {
    transform: scale(1.15); /* subtle for navbar */
}
.magnify-cta:hover, .magnify-cta:focus-visible, .magnify-cta:active {
    transform: scale(1.0) !important; /* no magnification */
}
.magnify-btn {
    display: inline-block;
    transition: transform 160ms ease;
    transform-origin: center;
    will-change: transform;
}
.magnify-btn:hover, .magnify-btn:focus-visible {
    transform: scale(1.35); /* moderate magnify for regular buttons */
}
/* Removed floating chat button magnify */
@media (prefers-reduced-motion: reduce) {
    .magnify-nav, .magnify-cta, .magnify-btn { transition: none; }
}

/* Futuristic moving gradient for CTA */
.gradient-cta {
    background: linear-gradient(90deg, #0d6efd, #20c997, #6610f2, #0dcaf0);
    background-size: 300% 300%;
    animation: gradientShift 6s ease infinite;
    color: #fff !important;
    border: 0;
    box-shadow: 0 0.5rem 1rem rgba(13,110,253,0.25);
}
.gradient-cta:hover,
.gradient-cta:focus-visible {
    box-shadow: 0 0.75rem 1.25rem rgba(13,110,253,0.35);
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@media (prefers-reduced-motion: reduce) {
    .gradient-cta { animation: none; }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Your Health, Our Priority</h1>
        <p class="lead">Professional healthcare services for students and staff</p>
        <a href="#services" class="btn btn-light btn-lg magnify-btn">Our Services</a>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="services-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Services</h2>
            <p class="text-muted">Comprehensive healthcare services for our school community</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card service-card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-clipboard2-pulse service-icon"></i>
                        <h5 class="card-title">General Consultation</h5>
                        <p class="card-text">Comprehensive health checkups and medical consultations for students and staff.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card service-card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-heart-pulse service-icon"></i>
                        <h5 class="card-title">Emergency First Aid</h5>
                        <p class="card-text">Immediate medical attention and first aid services for emergencies and injuries.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card service-card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-chat-heart service-icon"></i>
                        <h5 class="card-title">Health Counseling</h5>
                        <p class="card-text">Professional health counseling and wellness guidance for mental and physical wellbeing.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nurse fAIth CTA Section -->
<section class="nurse-faith-cta py-5 bg-primary-subtle">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="fw-bold mb-3">Unsure about your symptoms?</h3>
                <p class="lead mb-3">Nurse f<span class="text-primary fw-bold">AI</span>th is here to help. Get instant answers about clinic hours, first aid, and wellness.</p>
                <button class="btn btn-lg rounded-pill px-4 py-3 gradient-cta" id="startChatBtn">
                    <i class="fas fa-comment-medical me-2"></i>
                    Start Chat with Nurse fAIth
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Chat Widget -->
<div class="chat-widget" id="ai-chatbot" style="position: fixed; bottom: 1rem; right: 1rem; z-index: 1050;">
    <div class="chat-box" id="chatBox">
        <div class="chat-header">
            <strong>Nurse fAIth</strong>
        </div>
        <div class="chat-body">
            <div class="system-message">
                <div class="message-bubble system">
                    Hello. I am Nurse fAIth. How can I assist you today?
                </div>
            </div>
            <div class="chat-suggestions mt-3">
                <p class="mb-2 fw-semibold">Try asking:</p>
                <ul class="list-unstyled mb-0">
                    <li>• What time does the clinic open?</li>
                    <li>• How do I contact clinic staff?</li>
                    <li>• What should I do for a minor sprain?</li>
                </ul>
            </div>
        </div>
        <div class="chat-footer">
            <div class="input-group">
                <input type="text" class="chat-input" id="chatInput" placeholder="Type your message...">
                <button class="btn btn-primary chat-send-btn" type="button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    <button class="chat-toggle" id="chatToggle" aria-controls="chatBox" aria-expanded="true">
        <i class="fas fa-comment-medical"></i>
    </button>
</div>

<script>
// Open/close chat and focus input
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('chatBox');
    const chatToggle = document.getElementById('chatToggle');
    const startChatBtn = document.getElementById('startChatBtn');
    const chatInput = document.getElementById('chatInput');

    function openChat() {
        chatBox.classList.remove('d-none');
        chatToggle.setAttribute('aria-expanded', 'true');
        // Harden focus to handle rendering delays
        setTimeout(() => { chatInput && chatInput.focus(); }, 0);
    }

    // Keep toggleChat if needed elsewhere, but use openChat for clicks
    function toggleChat() {
        const isHidden = chatBox.classList.toggle('d-none');
        chatToggle.setAttribute('aria-expanded', String(!isHidden));
        if (!isHidden) setTimeout(() => { chatInput && chatInput.focus(); }, 0);
    }

    startChatBtn && startChatBtn.addEventListener('click', openChat);
    // Always open on floating button click
    chatToggle && chatToggle.addEventListener('click', openChat);

    // Apply magnify to all navbar links
    document.querySelectorAll('ul.navbar-nav a.nav-link').forEach(a => a.classList.add('magnify-nav'));

    // Ensure all navbar links (including Services) have magnify
    document.querySelectorAll('ul.navbar-nav a.nav-link').forEach(a => a.classList.add('magnify-nav'));

    // Add "Meet Nurse fAIth" to navbar and wire click to open chat
    const navbars = document.querySelectorAll('ul.navbar-nav');
    const targetUl = navbars[navbars.length - 1];
    if (targetUl && !document.getElementById('navChatLink')) {
        const li = document.createElement('li');
        li.className = 'nav-item';
        const a = document.createElement('a');
        a.className = 'nav-link magnify-nav';
        a.id = 'navChatLink';
        a.href = '#ai-chatbot';
        a.innerHTML = 'Meet Nurse f<span class="text-primary fw-bold">AI</span>th';
        a.addEventListener('click', function (e) {
            e.preventDefault();
            openChat();
            document.getElementById('ai-chatbot')?.scrollIntoView({ behavior: 'smooth', block: 'end' });
        });
        li.appendChild(a);
        targetUl.appendChild(li);
    }

    // Remove "Contact" from the navbar
    document.querySelectorAll('ul.navbar-nav a.nav-link').forEach(link => {
        const text = (link.textContent || '').trim().toLowerCase();
        const href = (link.getAttribute('href') || '').toLowerCase();
        if (text.includes('contact') || href.includes('#contact') || href.includes('contact')) {
            const li = link.closest('li') || link.parentElement;
            if (li) li.remove();
        }
    });

    // Remove any broken login links
    document.querySelectorAll('ul.navbar-nav a.nav-link').forEach(link => {
        const txt = (link.textContent || '').toLowerCase();
        if (txt.includes('login')) { (link.closest('li') || link.parentElement)?.remove(); }
    });

    // Add Staff Login pointing to the login page
    const navbars = document.querySelectorAll('ul.navbar-nav');
    const targetUl = navbars[navbars.length - 1];
    if (targetUl && !document.getElementById('navStaffLogin')) {
        const li = document.createElement('li');
        li.className = 'nav-item';
        const a = document.createElement('a');
        a.className = 'nav-link magnify-nav';
        a.id = 'navStaffLogin';
        a.href = 'admin/login.php';
        a.innerHTML = '<i class="bi bi-shield-lock me-1"></i>Staff Login';
        li.appendChild(a);
        targetUl.appendChild(li);
    }

    // Removed footer overwrite with sample contact data; footer will use existing DB-backed content/templates
});
</script>

<?php require_once 'includes/footer.php'; ?>