const fireEvent = (name, data) => {
    const details = {detail: data};
    const event = new CustomEvent(name, details);

    dispatchEvent(event);
};

//https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/key
const handleEscape = (e, onEscape, force = false) => {
    if (e.defaultPrevented && !force) {
        return; // Do nothing if the event was already processed
    }

    switch (e.key) {
        case 'Esc': // IE/Edge specific value
        case 'Escape':
            onEscape();
            break;

        default:
            return;
    }

    // Cancel the default action to avoid it being handled twice
    e.preventDefault();
};

export {fireEvent, handleEscape};
