window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (pageYOffset >= sectionTop - sectionHeight / 3) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === current) {
            link.classList.add('active');
        }
    });
});

// Add animation to cards on scroll
const cards = document.querySelectorAll('.card');
const animateCards = () => {
    cards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        const cardBottom = card.getBoundingClientRect().bottom;
        if (cardTop < window.innerHeight && cardBottom > 0) {
            card.classList.add('animate');
        } else {
            card.classList.remove('animate');
        }
    });
};

window.addEventListener('scroll', animateCards);
animateCards(); // Initial check on page load