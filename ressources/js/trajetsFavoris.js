const bouton = document.getElementById('mon-bouton');
const div = document.getElementById('ma-div');

if (bouton !== null) {
    bouton.addEventListener('click', () => {
        if (div.classList.contains('visible')) {
            div.classList.remove('visible');
            bouton.textContent = "▼";
        } else {
            div.classList.add('visible');
            bouton.textContent = "▲";
        }
    });
}
