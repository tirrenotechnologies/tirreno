import {Constants} from './Constants.js?v=0.10.0';

const replaceAll = (str, search, replacement) => {
    return str.split(search).join(replacement);
};

const getRuleClass = (value) => {
    const className = Constants.RULE_WEIGHTS[parseInt(value, 10)];

    return className ? className : Constants.RULE_WEIGHTS['0'];
};

const formatTime = (str) => {
    const dayPattern = /(\d+)\s+days?/;

    let days = 0;
    const dayMatch = str.match(dayPattern);
    if (dayMatch) {
        days = parseInt(dayMatch[1], 10);
        str = str.replace(dayPattern, '').trim();
    }

    // remove milliseconds part if exists
    str = str.split('.')[0];

    const timePattern = /^\d{2}:\d{2}:\d{2}$/;
    if (!timePattern.test(str)) {
        return '';
    }

    const parts = str.split(':');
    const hours = parseInt(parts[0], 10);
    let minutes = parseInt(parts[1], 10);
    const seconds = parseInt(parts[2], 10);

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
    } catch (e) {
        return null;
    }
};

const formatKiloValue = (value) => {
    if (value >= 1000000) {
        return Math.floor(value / 1000000) + 'M';
    }
    if (value >= 1000) {
        return Math.floor(value / 1000) + 'k';
    }

    return value;
};

export {
    replaceAll,
    getRuleClass,
    formatTime,
    openJson,
    formatKiloValue,
};
