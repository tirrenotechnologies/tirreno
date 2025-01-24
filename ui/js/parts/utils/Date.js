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
const formatIntTimeUtc = (ts, useTime, offsetInSeconds = 0, utcAlready = false) => {
    const dt = new Date(ts + ((new Date()).getTimezoneOffset() * 60 + offsetInSeconds) * 1000);

    let m = dt.getMonth() + 1;
    let d = dt.getDate();
    let y = dt.getFullYear();
    m = padZero(m);
    d = padZero(d);
    y = padZero(y, 4);

    if (!useTime) {
        return `${d}/${m}/${y}`;
    }

    let h = dt.getHours();
    let i = dt.getMinutes();
    let s = dt.getSeconds();
    h = padZero(h);
    i = padZero(i);
    s = padZero(s);

    return `${d}/${m}/${y} ${h}:${i}:${s}`;
};

const formatStringTime = (dt) => {

    let m = dt.getMonth() + 1;
    let d = dt.getDate();
    let y = dt.getFullYear();
    m = padZero(m);
    d = padZero(d);
    y = padZero(y, 4);

    let h = dt.getHours();
    let i = dt.getMinutes();
    let s = dt.getSeconds();
    h = padZero(h);
    i = padZero(i);
    s = padZero(s);

    return `${y}-${m}-${d}T${h}:${i}:${s}`;
};

export {formatIntTimeUtc, formatStringTime, notificationTime, padZero, addDays, addHours};
