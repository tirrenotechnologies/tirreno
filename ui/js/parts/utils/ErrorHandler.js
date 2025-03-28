import {notificationTime} from './Date.js?v=2';

const handleAjaxError = (xhr, status, error) => {
    // ignore abort, but not network issues
    if (xhr.status === 0 && xhr.readyState === 0) {
        return;
    }

    if (xhr.status === 401 || xhr.status === 403) {
        window.location.href = escape('/login');
        return;
    }

    const time = notificationTime();
    const msg = 'An error occurred while requesting resource. Please try again later.';
    const notificationEl = document.getElementById('client-error');
    notificationEl.innerHTML = `<span class="faded">${time}</span>&nbsp;&nbsp;${msg}<button class="delete"></button>`;

    const deleteButton = notificationEl.querySelector('.delete');
    deleteButton.addEventListener('click', function() {
        notificationEl.classList.add('is-hidden');
    });

    notificationEl.classList.remove('is-hidden');
};

export {handleAjaxError};
