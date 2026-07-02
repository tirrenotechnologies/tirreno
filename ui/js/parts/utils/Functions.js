const debounce = callback => {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), 500);
    };
};

// old browser compatible; single child replacement
const replaceChildren = (el, child) => {
    if (!el) {
        return;
    }

    if (typeof el.replaceChildren === 'function') {
        el.replaceChildren(child);
        return;
    }

    while (el.firstChild) {
        el.removeChild(el.firstChild);
    }

    el.appendChild(child instanceof Node ? child : document.createTextNode(String(child)));
};

// .closest() replacement
const closest = (el, target) => {
    while (el) {
        if (matches(el, target)) {
            return el;
        }

        el = el.parentElement;
    }

    return null;
};

const matches = (el, selector) => {
    return (
        el.matches ||
        el.msMatchesSelector ||
        el.webkitMatchesSelector
    ).call(el, selector);
};

// .includes() replacement
const inArray = (haystack, needle) => {
    return haystack.indexOf(needle) !== -1;
};

// base for Object.entires() and Object.values()
const mapKeys = (obj) => {
    let result = [];

    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            result.push(key);
        }
    }

    return result;
};

const defined = (val) => {
    return val !== null && val !== undefined;
};

export {debounce, replaceChildren, closest, inArray, mapKeys, defined};
