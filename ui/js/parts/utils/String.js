import {MAX_TOOLTIP_LENGTH} from './Constants.js?v=2';

const truncateWithHellip = (value, n) => {
    let tooltip = value;

    if (value) {
        if (value.length > (n + 2)) {
            value = `${escapeForHTMLAttribute(value.slice(0, n))}&hellip;`;
        } else {
            value = escapeForHTMLAttribute(value);
        }
    }

    if (tooltip.length > MAX_TOOLTIP_LENGTH) {
        tooltip = `${escapeForHTMLAttribute(tooltip.slice(0, MAX_TOOLTIP_LENGTH))}&hellip;`;
    } else {
        tooltip = escapeForHTMLAttribute(tooltip);
    }

    value = `<span class="tooltip" title="${tooltip}">${value}</span>`;

    return value;
};

const replaceAll = (str, search, replacement) => {
    return str.split(search).join(replacement);
};

const escapeForHTMLAttribute = (str) => {
    if (str === '&#65293;') {
        return str;
    }

    return (str === null || str === undefined) ? '' :
        str.replace(/["'&<>]/g, function(match) {
            switch (match) {
                case '"':
                    return '&quot;';
                case '\'':
                    return '&apos;';
                case '&':
                    return '&amp;';
                case '<':
                    return '&lt;';
                case '>':
                    return '&gt;';
                default:
                    return match;
            };
        });
};

const replaceUnicodeWithEntities = (str) => {
    return str.split('').map(char => {
        const code = char.charCodeAt(0);
        if (code > 127) {
            return `&#${code};`;
        } else {
            return char;
        }
    }).join('');
};

const getRuleClass = (value) => {
    switch (value) {
        case -20:
            return 'positive';
        case 10:
            return 'medium';
        case 20:
            return 'high';
        case 70:
            return 'extreme';
        default:
            return 'none';
    }
};

const formatTime = (str) => {
    const dayPattern = /(\d+)\s+days?/;

    let days = 0;
    const dayMatch = str.match(dayPattern);
    if (dayMatch) {
        days = parseInt(dayMatch[1]);
        str = str.replace(dayPattern, '').trim();
    }

    // remove milliseconds part if exists
    str = str.split('.')[0];

    const timePattern = /^\d{2}:\d{2}:\d{2}$/;
    if (!timePattern.test(str)) {
        return '';
    }

    const parts = str.split(':');
    const hours = parseInt(parts[0]);
    let minutes = parseInt(parts[1]);
    const seconds = parseInt(parts[2]);

    let humanTime = '';
    if (days > 0) {
        humanTime += `${days} d ${hours} h `;
    } else {
        minutes += 60 * hours;
    }
    if (minutes > 0) humanTime += `${minutes} min `;
    if (seconds > 0) humanTime += `${seconds} s`;

    if (humanTime === '') humanTime = '1 s';

    return humanTime.trim();
};

const openJson = (str) => {
    try {
        return JSON.parse(str);
    } catch (error) {
        return null;
    }
};

export {truncateWithHellip, replaceAll, escapeForHTMLAttribute, replaceUnicodeWithEntities, getRuleClass, formatTime, openJson};
