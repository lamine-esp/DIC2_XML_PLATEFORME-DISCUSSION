/**
 * JavaScript principal pour la Plateforme de Discussion
 * Gère les interactions utilisateur et les fonctionnalités AJAX
 */

// Application JavaScript principale
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants
    initializeComponents();
    setupEventListeners();
    setupAutoScroll();
});

// Initialisation des composants
function initializeComponents() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialiser les popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Ajouter des classes d'animation
    addAnimationClasses();
}

// Configuration des écouteurs d'événements
function setupEventListeners() {
    // Auto-resize des textareas
    setupTextareaAutoResize();
    
    // Validation des formulaires
    setupFormValidation();
    
    // Gestion des messages
    setupMessageHandling();
    
    // Gestion des fichiers
    setupFileHandling();
}

// Configuration du défilement automatique
function setupAutoScroll() {
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Auto-resize des textareas
function setupTextareaAutoResize() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Déclencher l'événement input pour ajuster la hauteur initiale
        textarea.dispatchEvent(new Event('input'));
    });
}

// Validation des formulaires
function setupFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showAlert('Veuillez corriger les erreurs dans le formulaire.', 'danger');
            }
        });
    });
}

// Validation d'un formulaire
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Gestion des messages
function setupMessageHandling() {
    // Envoi de message avec Entrée
    const messageInputs = document.querySelectorAll('textarea[name="content"]');
    messageInputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    });
}

// Gestion des fichiers
function setupFileHandling() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            const fileNameDisplay = document.getElementById('fileName');
            if (fileNameDisplay && fileName) {
                fileNameDisplay.textContent = fileName;
                fileNameDisplay.style.display = 'inline';
            }
        });
    });
}

// Ajouter des classes d'animation
function addAnimationClasses() {
    const elements = document.querySelectorAll('.card, .btn, .alert');
    elements.forEach(element => {
        element.classList.add('fade-in');
    });
}

// Afficher une alerte
function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid, .container');
    if (container) {
        container.insertBefore(alertContainer, container.firstChild);
        
        // Auto-dismiss après 5 secondes
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }
}

// Fonction pour confirmer une action
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Fonction pour charger des données via AJAX
function loadData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
            showAlert('Erreur lors du chargement des données', 'danger');
        });
}

// Fonction pour envoyer des données via AJAX
function sendData(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => callback(result))
    .catch(error => {
        console.error('Erreur lors de l\'envoi des données:', error);
        showAlert('Erreur lors de l\'envoi des données', 'danger');
    });
}

// Fonction pour mettre à jour l'interface utilisateur
function updateUI(element, content) {
    if (element) {
        element.innerHTML = content;
        element.classList.add('fade-in');
    }
}

// Fonction pour afficher un indicateur de chargement
function showLoading(element) {
    if (element) {
        element.classList.add('loading');
        element.innerHTML = '<div class="spinner"></div> Chargement...';
    }
}

// Fonction pour masquer un indicateur de chargement
function hideLoading(element, content) {
    if (element) {
        element.classList.remove('loading');
        element.innerHTML = content;
    }
}

// Fonction pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) {
        return 'Hier';
    } else if (diffDays === 0) {
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    } else {
        return date.toLocaleDateString('fr-FR');
    }
}

// Fonction pour tronquer le texte
function truncateText(text, maxLength) {
    if (text.length <= maxLength) {
        return text;
    }
    return text.substring(0, maxLength) + '...';
}

// Fonction pour valider un email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Fonction pour valider un mot de passe
function validatePassword(password) {
    return password.length >= 6;
}

// Fonction pour générer un ID unique
function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Fonction pour copier du texte dans le presse-papiers
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Texte copié dans le presse-papiers', 'success');
    }).catch(err => {
        console.error('Erreur lors de la copie:', err);
        showAlert('Erreur lors de la copie', 'danger');
    });
}

// Fonction pour télécharger un fichier
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Fonction pour prévisualiser une image
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Fonction pour redimensionner une image
function resizeImage(file, maxWidth, maxHeight, callback) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = function() {
        let width = img.width;
        let height = img.height;
        
        if (width > maxWidth) {
            height = (height * maxWidth) / width;
            width = maxWidth;
        }
        
        if (height > maxHeight) {
            width = (width * maxHeight) / height;
            height = maxHeight;
        }
        
        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(img, 0, 0, width, height);
        
        canvas.toBlob(callback, 'image/jpeg', 0.8);
    };
    
    img.src = URL.createObjectURL(file);
}

// Fonction pour gérer les erreurs
function handleError(error, context = '') {
    console.error(`Erreur ${context}:`, error);
    showAlert(`Une erreur s'est produite: ${error.message}`, 'danger');
}

// Fonction pour débouncer les appels de fonction
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Fonction pour throttler les appels de fonction
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export des fonctions pour utilisation globale
window.AppUtils = {
    showAlert,
    confirmAction,
    loadData,
    sendData,
    updateUI,
    showLoading,
    hideLoading,
    formatDate,
    truncateText,
    validateEmail,
    validatePassword,
    generateId,
    copyToClipboard,
    downloadFile,
    previewImage,
    resizeImage,
    handleError,
    debounce,
    throttle
}; 