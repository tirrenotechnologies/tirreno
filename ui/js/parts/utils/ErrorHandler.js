import {notificationTime} from './Date.js?v=2';

const handleAjaxError = (xhr, status, error) => {
    // ignore abort, but not network issues
    if ((xhr.status === 0 && xhr.readyState === 0) || (status && status.status === 0)) {
        return;
    }

    if (xhr.status === 401 || xhr.status === 403) {
        window.location.href = escape('/login');
        return;
    }

    const time = notificationTime();
    const msg = 'An error occurred while requesting resource. Please try again later.';
    const notificationEl = document.getElementById('client-error');

    const frag = document.createDocumentFragment();

    const span = document.createElement('span');
    span.className = 'faded';
    span.textContent = time;

    const el = document.createTextNode('\u00A0\u00A0' + msg);

    const button = document.createElement('button');
    button.className = 'delete';

    frag.appendChild(span);
    frag.appendChild(el);
    frag.appendChild(button);

    notificationEl.replaceChildren(frag);

    const deleteButton = notificationEl.querySelector('.delete');
    deleteButton.addEventListener('click', function() {
        notificationEl.classList.add('is-hidden');
    });

    notificationEl.classList.remove('is-hidden');
};

export {handleAjaxError};
