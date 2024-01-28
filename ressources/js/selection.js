const button = document.getElementById('menu-button');
const modal = document.getElementById('modal-content');
const modalBack = document.getElementById('modal');
const close = document.getElementById('close');
const favoris = document.getElementById('favoris');


button.addEventListener('click', () => {
    modal.classList.add('appear');
    modalBack.style.display = 'block';
    favoris.style.left = "24%";
});

close.addEventListener('click', () => {
    if (modal.classList.contains('appear')) {
        modal.classList.remove('appear');
        favoris.style.left = "0%";
        //  modal.classList.add('disapear');
        setTimeout(() => {
            modalBack.style.display = 'none';
        }, 500);
    }
});