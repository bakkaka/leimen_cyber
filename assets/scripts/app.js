// assets/app.js - Version pour fichier à la racine

// Import des styles
import '../styles/app.css';

// Import Bootstrap JS
import 'bootstrap';

// Import AOS animations
import AOS from 'aos';
import 'aos/dist/aos.css';

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    
    // AOS animations
    AOS.init({
        duration: 800,
        once: true,
        offset: 100,
        disable: window.innerWidth < 768
    });
    
    // Tooltips Bootstrap
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        if (typeof bootstrap !== 'undefined') {
            new bootstrap.Tooltip(tooltip);
        }
    });
    
    // Menu mobile
    initMobileMenu();
    
    // Lazy loading images
    initLazyImages();
    
    // Vidéos différées
    initLazyVideos();
    
    // Effet "Voir plus"
    initReadMoreButtons();
    
    // Marquer leçon comme terminée (bouton + vidéo)
    initLessonCompletion();
    
    console.log('✅ Cyber Formation prêt !');
});

// Menu mobile
function initMobileMenu() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    if (!navbarToggler) return;
    
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                navbarToggler.click();
            }
        });
    });
}

// Lazy loading images
function initLazyImages() {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.dataset.src;
                if (src) img.src = src;
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        });
    }, { rootMargin: '50px' });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Lazy loading vidéos
function initLazyVideos() {
    const placeholders = document.querySelectorAll('.video-placeholder');
    placeholders.forEach(placeholder => {
        placeholder.addEventListener('click', () => {
            const videoId = placeholder.dataset.videoId;
            const platform = placeholder.dataset.platform;
            
            let embedUrl = '';
            if (platform === 'youtube') {
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else if (platform === 'vimeo') {
                embedUrl = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
            }
            
            const iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.style.width = '100%';
            iframe.style.aspectRatio = '16/9';
            iframe.style.border = 'none';
            iframe.setAttribute('allow', 'autoplay; fullscreen');
            iframe.setAttribute('allowfullscreen', '');
            
            placeholder.innerHTML = '';
            placeholder.appendChild(iframe);
        });
    });
}

// Effet "Voir plus"
function initReadMoreButtons() {
    const buttons = document.querySelectorAll('.read-more-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const description = btn.closest('.course-content')?.querySelector('.course-description');
            if (description) {
                description.classList.toggle('expanded');
                btn.textContent = description.classList.contains('expanded') ? 'Voir moins ↑' : 'Voir plus ↓';
            }
        });
    });
}

// ============================================
// COMPLÉTION DES LEÇONS (NEW)
// ============================================
function initLessonCompletion() {
    // Bouton "Marquer comme terminé"
    const markCompleteBtn = document.getElementById('markCompleteBtn');
    if (!markCompleteBtn) return;
    
    const completeUrl = markCompleteBtn.dataset.url;
    if (!completeUrl) return;
    
    // Éviter les doubles clics
    let isProcessing = false;
    
    // Fonction pour marquer comme terminé
    function markAsCompleted() {
        if (isProcessing) return;
        isProcessing = true;
        
        fetch(completeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ completed: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                console.error('Erreur lors de la complétion');
                isProcessing = false;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            isProcessing = false;
        });
    }
    
    // Clic sur le bouton
    markCompleteBtn.addEventListener('click', markAsCompleted);
    
    // Détection automatique de la fin de la vidéo YouTube
    const videoIframe = document.querySelector('.video-container iframe');
    if (videoIframe && videoIframe.src && videoIframe.src.includes('youtube.com/embed/')) {
        // Attendre que l'API YouTube soit chargée
        let tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        let firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
        window.onYouTubeIframeAPIReady = function() {
            const player = new YT.Player(videoIframe, {
                events: {
                    'onStateChange': function(event) {
                        if (event.data === YT.PlayerState.ENDED) {
                            // Vidéo terminée, marquer comme terminé
                            if (!isProcessing) {
                                markAsCompleted();
                            }
                        }
                    }
                }
            });
        };
    }
}