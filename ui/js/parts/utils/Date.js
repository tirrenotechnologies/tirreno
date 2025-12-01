const addDays = (date, days) => {
    const dateCopy = new Date(date);
    dateCopy.setDate(date.getDate() + days);

    return dateCopy;
};

const addHours = (date, hours) => {
    const ms = hours * 60 * 60 * 1000;

    const dateCopy = new Date(date);
    dateCopy.setTime(date.getTime() + ms);

    return dateCopy;
};

//https://stackoverflow.com/a/12550320
const padZero = (n, s = 2) => {
    return (s > 0) ? ('000'+n).slice(-s) : (n+'000').slice(0, -s);
};

const notificationTime = () => {
    const dt        = new Date();
    const day       = padZero(dt.getDate());
    const month     = padZero(dt.getMonth() + 1);
    const year      = padZero(dt.getFullYear(), 4);
    const hours     = padZero(dt.getHours());
    const minutes   = padZero(dt.getMinutes());
    const seconds   = padZero(dt.getSeconds());

    return `[${day}/${month}/${year} ${hours}:${minutes}:${seconds}]`;
};

// offsetInSeconds is not inverted as .getTimezoneOffset() result
const formatIntTimeUtc = (ts, useTime, offsetInSeconds = 0) => {
    const dt = new Date(ts + ((new Date()).getTimezoneOffset() * 60 + offsetInSeconds) * 1000);

    const m = padZero(dt.getMonth() + 1);
    const d = padZero(dt.getDate());
    const y = padZero(dt.getFullYear(), 4);

    if (!useTime) {
        return `${d}/${m}/${y}`;
    }

    const h = padZero(dt.getHours());
    const i = padZero(dt.getMinutes());
    const s = padZero(dt.getSeconds());

    return `${d}/${m}/${y} ${h}:${i}:${s}`;
};

const formatStringTime = (dt) => {
    const m = padZero(dt.getMonth() + 1);
    const d = padZero(dt.getDate());
    const y = padZero(dt.getFullYear(), 4);

    const h = padZero(dt.getHours());
    const i = padZero(dt.getMinutes());
    const s = padZero(dt.getSeconds());

    return `${y}-${m}-${d}T${h}:${i}:${s}`;
};

export {
    formatIntTimeUtc,
    formatStringTime,
    notificationTime,
    padZero,
    addDays,
    addHours,
};
