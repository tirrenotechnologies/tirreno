(function() {
    const emulateButtonClick = () => {
        let button = document.getElementById('submit-button');

        button.classList.add('clicked');
        button.click();

        setTimeout(() => {
            button.classList.remove('clicked');
        }, 200);
    };

    const onEnterKeyDown = e => {
        if (e.keyCode === 13) {
            emulateButtonClick();
        }
    };

    document.addEventListener('keydown', onEnterKeyDown, false);
})();
