function toggleUserMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('show');
    
    const outsideClickListener = (e) => {
        if (!menu.contains(e.target) && !event.target.contains(e.target)) {
            menu.classList.remove('show');
            document.removeEventListener('click', outsideClickListener);
        }
    };

    setTimeout(() => {
        document.addEventListener('click', outsideClickListener);
    }, 0);
}