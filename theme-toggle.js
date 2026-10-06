// ============================================
// THEME TOGGLE - DARK/LIGHT MODE
// ============================================

function getPreferredTheme() {
    const savedTheme = localStorage.getItem('schoolhub-theme');
    if (savedTheme) return savedTheme;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }
    return 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('schoolhub-theme', theme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.checked = (theme === 'dark');
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        if (theme === 'dark') {
            btn.textContent = '🌙';
            btn.title = 'Switch to Light Mode';
        } else {
            btn.textContent = '☀️';
            btn.title = 'Switch to Dark Mode';
        }
    });
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

document.addEventListener('DOMContentLoaded', function() {
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            setTheme(theme);
        });
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });
    });
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('schoolhub-theme')) {
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
}// ============================================
// THEME TOGGLE - DARK/LIGHT MODE
// ============================================

function getPreferredTheme() {
    const savedTheme = localStorage.getItem('schoolhub-theme');
    if (savedTheme) return savedTheme;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }
    return 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('schoolhub-theme', theme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.checked = (theme === 'dark');
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        if (theme === 'dark') {
            btn.textContent = '🌙';
            btn.title = 'Switch to Light Mode';
        } else {
            btn.textContent = '☀️';
            btn.title = 'Switch to Dark Mode';
        }
    });
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

document.addEventListener('DOMContentLoaded', function() {
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            setTheme(theme);
        });
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });
    });
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('schoolhub-theme')) {
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
}// ============================================
//// ============================================
// THEME TOGGLE - DARK/LIGHT MODE
// ============================================

function getPreferredTheme() {
    const savedTheme = localStorage.getItem('schoolhub-theme');
    if (savedTheme) return savedTheme;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }
    return 'light';
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('schoolhub-theme', theme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.checked = (theme === 'dark');
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        if (theme === 'dark') {
            btn.textContent = '🌙';
            btn.title = 'Switch to Light Mode';
        } else {
            btn.textContent = '☀️';
            btn.title = 'Switch to Dark Mode';
        }
    });
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
}

document.addEventListener('DOMContentLoaded', function() {
    const preferredTheme = getPreferredTheme();
    setTheme(preferredTheme);
    
    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            setTheme(theme);
        });
    });
    
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });
    });
});

if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('schoolhub-theme')) {
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
}