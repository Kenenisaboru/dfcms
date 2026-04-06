/* assets/js/next-gen-ui.js */
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initVoiceCommands();
    initBadges();
    initInteractiveHelp();
});

/**
 * Theme Management (Dark/Light Toggle)
 */
function initTheme() {
    const body = document.body;
    const themeToggle = document.querySelector('.theme-toggle');
    const savedTheme = localStorage.getItem('dfcms-theme') || 'dark-mode';

    body.classList.add(savedTheme);
    updateThemeUI(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
            const newTheme = currentTheme === 'dark-mode' ? 'light-mode' : 'dark-mode';
            
            body.classList.remove(currentTheme);
            body.classList.add(newTheme);
            localStorage.setItem('dfcms-theme', newTheme);
            updateThemeUI(newTheme);
        });
    }
}

function updateThemeUI(theme) {
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.className = theme === 'dark-mode' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

/**
 * Voice Command Integration
 */
function initVoiceCommands() {
    if (!('webkitSpeechRecognition' in window)) {
        console.log('Speech recognition not supported');
        return;
    }

    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    const voiceIndicator = document.querySelector('.voice-indicator');

    document.addEventListener('keydown', (e) => {
        // Alt + V to start listening
        if (e.altKey && e.key === 'v') {
            recognition.start();
            if (voiceIndicator) voiceIndicator.style.display = 'flex';
        }
    });

    recognition.onresult = (event) => {
        const command = event.results[0][0].transcript.toLowerCase();
        handleVoiceCommand(command);
    };

    recognition.onend = () => {
        if (voiceIndicator) voiceIndicator.style.display = 'none';
    };
}

function handleVoiceCommand(command) {
    console.log('Voice Command:', command);
    if (command.includes('home') || command.includes('dashboard')) {
        window.location.href = 'dashboard.php';
    } else if (command.includes('new complaint') || command.includes('submit')) {
        window.location.href = 'student/submit_complaint.php';
    } else if (command.includes('tracker') || command.includes('my complaints')) {
        window.location.href = 'student/tracker.php';
    } else if (command.includes('theme') || command.includes('dark') || command.includes('light')) {
        document.querySelector('.theme-toggle')?.click();
    } else if (command.includes('help') || command.includes('guide')) {
        showInteractiveHelp();
    }
}

/**
 * Interactive Help System
 */
function initInteractiveHelp() {
    const helpBtn = document.querySelector('.help-trigger');
    if (helpBtn) {
        helpBtn.addEventListener('click', showInteractiveHelp);
    }
}

function showInteractiveHelp() {
    const helpSteps = [
        { target: '.new-complaint-btn', text: 'Click here to submit a new complaint.' },
        { target: '.stats-card', text: 'Monitor your complaint status statistics here.' },
        { target: '.badge-container', text: 'Earn badges as you participate in the system!' },
        { target: '.theme-toggle', text: 'Toggle between dark and light themes.' }
    ];
    
    // Simplistic implementation of a tour
    alert('Welcome to DFCMS Interactive Help! Follow the highlights to learn how to use the platform.');
}

/**
 * Badges UI
 */
function initBadges() {
    const badgeContainer = document.querySelector('.badge-container');
    if (badgeContainer) {
        // Fetch and render badges dynamically if needed
    }
}
