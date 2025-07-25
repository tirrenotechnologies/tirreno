import {padZero} from './utils/Date.js?v=2';
import {
    //truncateWithHellip,
    getRuleClass,
    formatTime,
    openJson,
} from './utils/String.js?v=2';

import {
    MAX_STRING_LONG_NETNAME_IN_TABLE,
    MAX_STRING_SHORT_NETNAME_IN_TABLE,
    MAX_STRING_LENGTH_IN_TABLE,
    MAX_STRING_USER_SHORT_LENGTH_IN_TABLE,
    MAX_STRING_USER_MEDIUM_LENGTH_IN_TABLE,
    MAX_STRING_USER_LONG_LENGTH_IN_TABLE,
    MAX_STRING_USER_LONG_LENGTH_IN_TILE,
    MAX_STRING_LENGTH_IN_TABLE_ON_DASHBOARD,
    MAX_STRING_LENGTH_FOR_EMAIL,
    MAX_STRING_LENGTH_FOR_PHONE,
    MAX_STRING_LENGTH_FULL_COUNTRY,
    MAX_STRING_LENGTH_FOR_TILE,
    MAX_STRING_USERID_LENGTH_IN_TABLE,
    MAX_STRING_USER_NAME_IN_TABLE,
    MAX_TOOLTIP_URL_LENGTH,
    MAX_TOOLTIP_LENGTH,

    USER_LOW_TRUST_SCORE_INF,
    USER_LOW_TRUST_SCORE_SUP,
    USER_MEDIUM_TRUST_SCORE_INF,
    USER_MEDIUM_TRUST_SCORE_SUP,
    USER_HIGH_TRUST_SCORE_INF,

    ASN_OVERRIDE,
    COUNTRIES_EXCEPTIONS,
    NORMAL_DEVICES,
    NO_RULES_MSG,
    UNDEFINED_RULES_MSG,
    MIDLINE_HELLIP,
    HELLIP,
    HYPHEN,
} from './utils/Constants.js?v=2';

const isDashboardPage    = () => !!document.getElementById('mostActiveUsers');
const getNumberOfSymbols = (length = 'default') => {
    if (isDashboardPage()) {
        return MAX_STRING_LENGTH_IN_TABLE_ON_DASHBOARD;
    } else {
        if (length === 'long') {
            return MAX_STRING_USER_LONG_LENGTH_IN_TABLE;
        } else if (length === 'short') {
            return MAX_STRING_USER_SHORT_LENGTH_IN_TABLE;
        } else if (length == 'medium') {
            return MAX_STRING_USER_MEDIUM_LENGTH_IN_TABLE;
        } else if (length === 'tile') {
            return MAX_STRING_USER_LONG_LENGTH_IN_TILE;
        } else {
            return MAX_STRING_LENGTH_IN_TABLE;
        }
    }
};

const tooltipWrap = (tooltip, value, wrap = true) => {
    let node = (typeof value === 'string') ? document.createTextNode(value) : value;

    let result = (wrap) ? document.createElement('span') : node;

    if (wrap) {
        result.appendChild(node);
    }

    if (tooltip !== null && tooltip !== undefined && tooltip !== '') {
        result.classList.add('tooltip');
        result.title = tooltip;
    }

    return result;
};

const truncateWithHellip = (value, n) => {
    let tooltip = value;

    if (value && value.length > (n + 2)) {
        value = value.slice(0, n) + HELLIP;
    }

    if (tooltip && tooltip.length > MAX_TOOLTIP_LENGTH) {
        tooltip = tooltip.slice(0, MAX_TOOLTIP_LENGTH) + HELLIP;
    }

    return tooltipWrap(tooltip, renderDefaultIfEmpty(value), true);
};

const wrapWithCountryDiv = html => {
    let node = document.createElement('div');
    node.className = 'flag';
    node.appendChild(html);

    return node;
};

const wrapWithImportantSpan = (span, record) => {
    if (!record.is_important) {
        return span;
    }

    const el = document.createElement('span');
    el.className = 'important-user';
    el.appendChild(span);

    return el;
};

const wrapWithUserLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/id/${record.accountid}`;
    el.appendChild(span);

    return el;
};

const wrapWithResourceLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/resource/${record.url_id}`;
    el.appendChild(span);

    return el;

};

const wrapWithIpLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/ip/${record.ipid}`;
    el.appendChild(span);

    return el;
};

const wrapWithIspLink = (span, record) => {
    if (!record.ispid) {
        return span;
    }

    const el = document.createElement('a');
    el.href = `/isp/${record.ispid}`;
    el.appendChild(span);

    return el;
};

const wrapWithCountryLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/country/${record.country_id}`;
    el.appendChild(span);

    return el;
};

const wrapWithBotLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/bot/${record.id}`;
    el.appendChild(span);

    return el;
};

const wrapWithPhoneLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/phones/${record.id}`;
    el.appendChild(span);

    return el;
};

const wrapWithDomainLink = (span, record) => {
    const el = document.createElement('a');
    el.href = `/domain/${record.id}`;
    el.appendChild(span);

    return el;
};

const wrapWithRuleLink = (span, ruleUid) => {
    const el = document.createElement('a');
    el.href = `/id?ruleUid=${ruleUid}`;
    el.appendChild(span);

    return el;
};

/*const wrapWithFraudSpan = (html, record) => {
    if (record.fraud_detected) {
        html = `<span class="fraud">${html}</span>`;
    }

    return html;
};*/

const normalizeTimestamp = (ts) => {
    //Fix for ie and safari: https://www.linkedin.com/pulse/fix-invalid-date-safari-ie-hatem-ahmad
    ts = ts.replace(/-/g, '/');
    ts = ts.split('.');

    return ts[0];
};

const renderTimeString = (data) => {
    if (data) {
        data = normalizeTimestamp(data);
    }

    const dt = new Date(data);

    if (dt instanceof Date && !isNaN(dt)) {
        let [month, day, year] = [
            dt.getMonth() + 1,
            dt.getDate(),
            dt.getFullYear(),
        ];

        let [hours, minutes, seconds] = [
            dt.getHours(),
            dt.getMinutes(),
            dt.getSeconds(),
        ];

        day     = padZero(day);
        month   = padZero(month);
        year    = padZero(year, 4);
        hours   = padZero(hours);
        minutes = padZero(minutes);
        seconds = padZero(seconds);

        data = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }

    return data;
};

const renderTime = (data) => {
    const span = document.createElement('span');
    span.textContent = renderTimeString(data);

    return span;
};

const renderTimeMsString = (data) => {
    let milliseconds = 0;
    if (data) {
        //Fix for ie and safari: https://www.linkedin.com/pulse/fix-invalid-date-safari-ie-hatem-ahmad
        data = data.replace(/-/g, '/');
        const s = data.split('.');
        data = s[0];
        // safari issue
        // not equivalent to dt.getMilliseconds(); in case of 01:01:01.7 getMilliseconds() returns 700 and split returns 7
        milliseconds = (s.length > 1) ? s[1] : 0;
    }

    const dt = new Date(data);

    if (dt instanceof Date && !isNaN(dt)) {
        let [month, day, year] = [
            dt.getMonth() + 1,
            dt.getDate(),
            dt.getFullYear(),
        ];

        let [hours, minutes, seconds] = [
            dt.getHours(),
            dt.getMinutes(),
            dt.getSeconds(),
        ];

        day             = padZero(day);
        month           = padZero(month);
        year            = padZero(year, 4);
        hours           = padZero(hours);
        minutes         = padZero(minutes);
        seconds         = padZero(seconds);
        milliseconds    = padZero(milliseconds, -3);

        data = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}.${milliseconds}`;
    }

    return data;
};

const renderTimeMs = (data) => {
    const span = document.createElement('span');
    span.textContent = renderTimeMsString(data);

    return span;
};

const renderDateString = (data) => {
    if (data) {
        data = normalizeTimestamp(data);
    } else {
        data = renderDefaultIfEmpty(data);
        return data;
    }

    const dt = new Date(data);

    if (dt instanceof Date && !isNaN(dt)) {
        let [month, day, year] = [
            dt.getMonth() + 1,
            dt.getDate(),
            dt.getFullYear(),
        ];

        day   = padZero(day);
        month = padZero(month);
        year  = padZero(year, 4);

        data = `${day}/${month}/${year}`;
    }

    return data;
};

const renderDate = (data) => {
    const span = document.createElement('span');
    span.textContent = renderDateString(data);

    return span;
};

const renderChoicesSelectorItem = (classNames, data, innerHtml) => {
    const itemClass = data.highlighted ? classNames.highlightedState : classNames.itemSelectable;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = classNames.button;
    button.textContent = 'Remove item';
    button.setAttribute('aria-label', 'Remove item');
    button.setAttribute('data-button', '');

    const div = document.createElement('div');
    div.className = `${classNames.item} ${itemClass}`;

    div.setAttribute('data-item', true);
    div.setAttribute('data-id', data.id);
    div.setAttribute('data-value', data.value);

    if (data.active) {
        div.setAttribute('aria-selected', 'true');
    }
    if (data.disabled) {
        div.setAttribute('aria-disabled', 'true');
    }

    div.appendChild(innerHtml);
    div.appendChild(button);

    return div.outerHTML;
};

const renderChoicesSelectorChoice = (classNames, data, itemSelectText, innerHtml) => {
    const choiceClass = data.disabled ? classNames.itemDisabled : classNames.itemSelectable;

    const div = document.createElement('div');
    div.className = `${classNames.item} ${classNames.itemChoice} ${choiceClass}`;
    div.role = (data.groupId > 0) ? 'treeitem' : 'option';

    div.setAttribute('data-select-text', itemSelectText);
    div.setAttribute('data-choice', true);
    div.setAttribute('data-id', data.id);
    div.setAttribute('data-value', data.value);

    if (data.disabled) {
        div.setAttribute('data-choice-disabled', true);
        div.setAttribute('aria-disabled', 'true');
    } else {
        div.setAttribute('data-choice-selectable', true);
    }

    div.appendChild(innerHtml);

    return div.outerHTML;
};

const splitLabel = (label) => {
    const node = document.createElement('div');
    node.innerHTML = label;

    return node.textContent.split('|');
};

const renderRuleSelectorItem = (classNames, data) => {
    const [uid, className, title] = splitLabel(data.label);

    const innerHtml = document.createDocumentFragment();
    const span = document.createElement('span');
    span.className = `ruleHighlight ${className}`;
    span.textContent = uid;
    innerHtml.appendChild(span);
    const el = document.createTextNode(title);
    innerHtml.appendChild(el);

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderRuleSelectorChoice = (classNames, data, itemSelectText) => {
    const [uid, className, title] = splitLabel(data.label);

    const innerHtml = document.createDocumentFragment();
    const span = document.createElement('span');
    span.className = `ruleHighlight ${className}`;
    span.textContent = uid;
    innerHtml.appendChild(span);
    const el = document.createTextNode(title);
    innerHtml.appendChild(el);

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderEventTypeSelectorItem = (classNames, data) => {
    const [value, name] = splitLabel(data.label);

    const innerHtml = document.createDocumentFragment();
    const node = document.createElement('p');
    node.className = `bullet ${value}`;
    innerHtml.appendChild(node);
    const el = document.createTextNode(name);
    innerHtml.appendChild(el);

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderEventTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const [value, name] = splitLabel(data.label);

    const innerHtml = document.createDocumentFragment();
    const node = document.createElement('p');
    node.className = `bullet ${value}`;
    innerHtml.appendChild(node);
    const el = document.createTextNode(name);
    innerHtml.appendChild(el);

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderIpTypeSelectorItem = (classNames, data) => {
    const [value] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = value;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderIpTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const [value] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = value;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderEntityTypeSelectorItem = (classNames, data) => {
    const [value] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = value;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderEntityTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const [value] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = value;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderScoresRangeSelectorItem = (classNames, data) => {
    const [bottom, top] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = `${bottom} - ${top}`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderScoresRangeSelectorChoice = (classNames, data, itemSelectText) => {
    const [bottom, top] = splitLabel(data.label);
    const innerHtml = document.createElement('span');
    innerHtml.textContent = `${bottom} - ${top}`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderHttpCode = record => {
    let span = null;
    const code = record.http_code;

    if (code) {
        let tooltip = '';

        switch (Math.floor(code / 100)) {
            case 1:
                tooltip = 'Informational responses (100 – 199)';
                break;
            case 2:
                tooltip = 'Successful responses (200 – 299)';
                break;
            case 3:
                tooltip = 'Redirection messages (300 – 399)';
                break;
            case 4:
                tooltip = 'Client error responses (400 – 499)';
                break;
            case 5:
                tooltip = 'Server error responses (500 – 599)';
                break;
            default:
                tooltip = 'Unexpected status code';
                break;
        }

        let style = (code < 400) ? 'nolight' : 'highlight';

        span = document.createElement('span');
        span.className = `${style} ${record.http_code}`;
        span.textContent = record.http_code;

        span = tooltipWrap(tooltip, span, false);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderHttpMethod = record => {
    let span = null;

    const type = record.http_method;
    if (type) {
        let style = (type === 'POST' || type === 'GET') ? 'nolight' : 'highlight';
        span = document.createElement('span');
        span.className = style;
        span.textContent = type;
    }

    return renderDefaultIfEmptySpan(span);
};

const renderTotalFrame = (base, val) => {
    const frag = document.createDocumentFragment();

    if (parseInt(base, 10) > parseInt(val, 10)) {
        const rest = (base !== null && base !== undefined && base > 0 && base >= val) ? (base - val) : MIDLINE_HELLIP;
        const span = document.createElement('span');
        span.className = 'addlight';
        span.textContent = val + '/';

        frag.appendChild(span);
        frag.appendChild(document.createTextNode(rest));
    } else {
        frag.appendChild(document.createTextNode(base));
    }

    return frag;
};

const renderUserCounter = (data, critical = 1) => {
    let span = null;

    if (Number.isInteger(data) && data >= 0) {
        let style = (data >= critical) ? 'highlight' : 'nolight';
        span = document.createElement('span');
        span.className = style;
        span.textContent = data;
    }

    return renderDefaultIfEmptySpan(span);
};

const renderBoolean = (data) => {
    let node = document.createElement('span');

    if (data === false) {
        node.className = 'nolight';
        node.textContent = 'No';
    } else if (data === true) {
        node.className = 'highlight';
        node.textContent = 'Yes';
    } else {
        node.textContent = HYPHEN;
    }

    return node;
};

const renderProportion = (n, t) => {
    const number = (typeof n === 'number' && Number.isFinite(n) && n >= 0 && n <= 100)
        ? (n > 0.0 && n < 1.0 ? '<1%' : `${Math.floor(n)}%`)
        : '&minus;';
    const tooltip = t ? `Last updated: ${renderDateString(t)}` : '\u2212';

    return tooltipWrap(tooltip, number, true);
};

const renderUserScore = record => {
    let score = (record.score !== null && record.score !== undefined) ? record.score : '\u2212';
    let cls = 'empty';

    if (record.fraud !== undefined && record.fraud !== null) {
        score = (record.fraud) ? 'X' : 'OK';
        cls = (record.fraud) ? 'low' : 'high';
    } else {
        if (score >= USER_HIGH_TRUST_SCORE_INF) {
            cls = 'high';
        }

        if (score >= USER_MEDIUM_TRUST_SCORE_INF && score < USER_MEDIUM_TRUST_SCORE_SUP) {
            cls = 'medium';
        }

        if (score >= USER_LOW_TRUST_SCORE_INF && score < USER_LOW_TRUST_SCORE_SUP) {
            cls = 'low';
        }
    }

    const span = document.createElement('span');
    span.className = `ignore-select score ${cls}`;
    span.textContent = score;

    const lastUpdate = `Last updated: ${renderDateString(record.score_updated_at)}`;

    return tooltipWrap(lastUpdate, span, true);
};

//User
const renderUserId = (value) => {
    let span = null;

    if (value) {
        span = truncateWithHellip(value, MAX_STRING_USERID_LENGTH_IN_TABLE);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderUser = (record, length = 'default') => {
    let span = null;
    const n = getNumberOfSymbols(length);
    const email = record.email;

    if (email) {
        span = truncateWithHellip(email, n);
    } else if (record.accounttitle) {
        span = renderUserId(record.accounttitle);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderImportantUser = (record, length = 'default') => {
    const user = renderUser(record, length);
    const html = wrapWithImportantSpan(user, record);

    return html;
};

const renderClickableUser = (record, length = 'long') => {
    const user = renderUser(record, length);
    const html = wrapWithUserLink(user, record);

    return html;
};

const renderClickableImportantUser = (record, length = 'default') => {
    const user = renderClickableUser(record, length);
    const html = wrapWithImportantSpan(user, record);

    return html;
};

const renderUserWithScore = (record, length = 'default') => {
    const frag = document.createDocumentFragment();
    frag.appendChild(renderUserScore(record));
    frag.appendChild(renderUser(record, length));

    return frag;
};

const renderClickableImportantUserWithScore = (record, length = 'default') => {
    const frag = document.createDocumentFragment();
    frag.appendChild(renderUserScore(record));
    frag.appendChild(renderClickableImportantUser(record, length));

    return frag;
};

const renderClickableImportantUserWithScoreTile = (record) => {
    return renderClickableImportantUserWithScore(record, 'tile');
};

const renderSession = (record) => {
    let result = null;

    if (record.session_cnt) {
        const cnt = record.session_cnt;
        const max_t = renderTimeString(record.session_max_t).split(' ');
        const min_t = renderTimeString(record.session_min_t).split(' ');
        let value = `${cnt} action`;
        value += (cnt > 1) ? `s (${formatTime(record.session_duration)})` : '';
        const tooltip = (cnt > 1) ? `${min_t[0]} ${min_t[1] || ''} - ${max_t[1] || ''}` : `${max_t[0]} ${max_t[1] || ''}`;
        const el = document.createTextNode(value);
        result = tooltipWrap(tooltip, el, true);
    } else {
        result = renderAngrtSpan();
    }

    return result;
};

const renderAngrtSpan = () => {
    const span = document.createElement('span');
    span.className = 'angrt';
    span.textContent = '\u221F';

    return span;
};

const renderUserForEvent = (record, length, sessionGroup, singleUser) => {
    if (!sessionGroup) return renderUserWithScore(record, length);  // regular events
    if (singleUser) return renderSession(record);                   // events on /user/abc page

    if (record.session_cnt) return renderUserWithScore(record, length); // events on /event page

    return renderAngrtSpan();
};

const renderTimestampForEvent = (record, sessionGroup, singleUser) => {
    if (sessionGroup && !singleUser && record.session_cnt && record.session_cnt > 1) return renderSession(record);  // events on /event page on session-end rows

    return renderTime(record.time); // events on other pages and on /event page in a middle of the session
};

const renderUserFirstname = record => {
    let span = null;
    let name = record.firstname;

    if (name) {
        name = name.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        span = truncateWithHellip(name, MAX_STRING_USER_NAME_IN_TABLE);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderUserLastname = record => {
    let span = null;
    let name = record.lastname;

    if (name) {
        name = name.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        span = truncateWithHellip(name, MAX_STRING_USER_NAME_IN_TABLE);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderUserReviewedStatus = record => {
    let span = document.createElement('span');

    if (record.fraud !== null) {
        const reviewStatus = (record.fraud) ? 'Blacklisted' : 'Whitelisted';
        const latestDecision = renderDateString(record.latest_decision);

        span.className = `reviewstatus ${reviewStatus}`;
        span.textContent = reviewStatus;
        span = tooltipWrap(latestDecision, span, false);
    } else if (record.added_to_review !== null) {
        span.className = 'reviewstatus in-review';
        span.textContent = 'In review';
    } else {
        span.className = 'reviewstatus';
        span.textContent = 'Normal';
    }

    return span;
};

const renderUserActionButtons = (record, small = true) => {
    let html;
    if (record.reviewed) {
        html = getFraudLegitButtons(record, small);
    } else {
        html = getToBeReviewedButton(record, small);
    }

    return html;
};

const renderBlacklistButtons = record => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button is-small dark-loader';
    button.textContent = 'Remove';
    button.setAttribute('data-item-id', record.entity_id);
    button.setAttribute('data-item-type', record.type);
    button.setAttribute('data-button-type', 'deleteButton');

    return button;
};

const getFraudLegitButtons = (record, small = true) => {
    let fraudBtnCls = 'is-neutral';
    let legitBtnCls = 'is-neutral';

    if (true === record.fraud) {
        fraudBtnCls = 'is-highlighted';
        legitBtnCls = 'is-neutral';
    }

    if (false === record.fraud) {
        fraudBtnCls = 'is-neutral';
        legitBtnCls = 'is-highlighted';
    }

    const whitelistButton = document.createElement('button');
    whitelistButton.type = 'button';
    whitelistButton.className = `button light-loader ${legitBtnCls}` + ((small) ? ' is-small' : '');
    whitelistButton.textContent = 'Whitelist';
    if (record.fraud === false) {
        whitelistButton.disabled = true;
    }
    whitelistButton.setAttribute('data-type', 'legit');
    whitelistButton.setAttribute('data-user-id', record.accountid);
    whitelistButton.setAttribute('data-button-type', 'fraudButton');

    const blacklistButton = document.createElement('button');
    blacklistButton.type = 'button';
    blacklistButton.className = `button light-loader ${fraudBtnCls}` + ((small) ? ' is-small' : '');
    blacklistButton.textContent = 'Blacklist';
    if (record.fraud === true) {
        blacklistButton.disabled = true;
    }
    blacklistButton.setAttribute('data-type', 'fraud');
    blacklistButton.setAttribute('data-user-id', record.accountid);
    blacklistButton.setAttribute('data-button-type', 'fraudButton');

    const div = document.createElement('div');
    div.className = 'legitfraud';
    div.appendChild(whitelistButton);
    div.appendChild(blacklistButton);

    return div;
};

const getToBeReviewedButton = (record, small = true) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'reviewed button dark-loader' + ((small) ? ' is-small' : '');
    button.textContent = 'Not reviewed';

    button.setAttribute('data-type', 'reviewed');
    button.setAttribute('data-user-id', record.accountid);
    button.setAttribute('data-button-type', 'reviewedButton');

    return button;
};

const renderScoreDetails = record => {
    if (!record.score_calculated) {
        const span = document.createElement('span');
        span.className = 'no-rules-tile';
        span.textContent = UNDEFINED_RULES_MSG.value;

        return tooltipWrap(UNDEFINED_RULES_MSG.tooltip, span, true);
    }

    const frag = document.createDocumentFragment();
    const details = record.score_details;

    if (Array.isArray(details)) {
        let uid = '';
        let descr = '';
        let name = '';
        //let part = '';
        for (let i = 0; i < details.length; i++) {
            uid = (details[i].uid !== null && details[i].uid !== undefined) ? details[i].uid : '';
            descr = (details[i].descr !== null && details[i].descr !== undefined) ? details[i].descr : '';
            name = (details[i].name!== null && details[i].name !== undefined) ? details[i].name : '';

            const span = document.createElement('span');
            span.className = `ruleHighlight ${getRuleClass(details[i].score)}`;
            span.textContent = uid;

            const el = document.createTextNode('\u00A0');

            let t = document.createElement('span');
            t.className = 'ruleName';
            t.textContent = name;
            t = tooltipWrap(descr, t, false);

            const part = document.createElement('p');
            part.appendChild(span);
            part.appendChild(el);
            part.appendChild(t);

            frag.appendChild(wrapWithRuleLink(part, uid));
        }
    }

    if (!frag.childNodes.length) {
        const span = document.createElement('span');
        span.className = 'no-rules-tile';
        span.textContent = NO_RULES_MSG.value;

        return tooltipWrap(NO_RULES_MSG.tooltip, span, false);
    }

    return frag;
};

//Email
const renderEmail = (record, length = MAX_STRING_LENGTH_FOR_EMAIL) => {
    let span = null;
    const email = record.email;

    if (email) {
        const n = (length === MAX_STRING_LENGTH_FOR_EMAIL) ? length : getNumberOfSymbols(length);

        span = truncateWithHellip(email, n);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderClickableEmail = record => {
    const email = renderEmail(record);

    //Overwrite ID attribute, because we are going to wrap it with domain link, not email
    record.id = record.domain_id;

    return wrapWithDomainLink(email, record);
};

const renderReputation = record => {
    record = (record !== null && record !== undefined) ? record : {};

    let icon = 'reputation-none';
    let reputation = record.reputation;
    let text = reputation.charAt(0).toUpperCase() + reputation.slice(1);

    if ('low' === reputation)    icon = 'reputation-low';
    if ('medium' === reputation) icon = 'reputation-medium';
    if ('high' === reputation)   icon = 'reputation-high';

    const frag = document.createDocumentFragment();
    const img = document.createElement('img');
    img.src = `/ui/images/icons/${icon}.svg`;
    img.alt = reputation;
    frag.appendChild(tooltipWrap(reputation, img, false));

    if (reputation !== 'none') {
        const el = document.createTextNode(reputation.charAt(0).toUpperCase() + reputation.slice(1));
        frag.appendChild(el);
    }

    return frag;
};

//Phone
const renderPhone = (record) => {
    let result;
    const phone = record.phonenumber;

    if (phone) {
        const code = !COUNTRIES_EXCEPTIONS.includes(record.country_iso) ? record.country_iso : 'lh';
        const tooltip = (record.full_country !== null && record.full_country !== undefined) ? record.full_country : '';

        const n       = MAX_STRING_LENGTH_FOR_PHONE;
        const number  = truncateWithHellip(phone, n);

        const frag = document.createDocumentFragment();

        const img = document.createElement('img');
        img.src = `/ui/images/flags/${code.toLowerCase()}.svg`;
        img.alt = tooltip;

        frag.appendChild(tooltipWrap(tooltip, img, true));
        frag.appendChild(number);

        result = wrapWithCountryDiv(frag);
    } else {
        const div = document.createElement('div');
        div.textContent = HYPHEN;

        result = div;
    }

    return result;
};

const renderClickablePhone = record => {
    const phone = renderPhone(record);
    const html  = wrapWithPhoneLink(phone, record);

    return html;
};

const renderFullCountry = value => {
    return renderDefaultIfEmptySpan(truncateWithHellip(value, MAX_STRING_LENGTH_FULL_COUNTRY));
};

const renderPhoneCarrierName = (record, length = 'medium') => {
    let span = null;
    let carrierName = record.carrier_name;

    if (carrierName) {
        const n = getNumberOfSymbols(length);
        carrierName = carrierName.replace(',', '');
        span = truncateWithHellip(carrierName, n);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderPhoneType = record => {
    const type = record.type;
    let span = null;

    if (type) {
        let src = 'smartphone.svg';
        if (['landline', 'FIXED_LINE', 'FIXED_LINE_OR_MOBILE', 'TOLL_FREE', 'SHARED_COST'].includes(type)) src = 'landline.svg';
        if (['nonFixedVoip', 'VOIP'].includes(type)) src = 'voip.svg';

        const tooltip = type.toLowerCase().replace(/_/g, ' ');

        const img = document.createElement('img');
        img.src = `/ui/images/icons/${src}`;

        span = tooltipWrap(tooltip, img, true);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderUsersList = (data) => {
    // data should be an array
    //let html = '';

    const frag = document.createDocumentFragment();

    if (Array.isArray(data)) {;
        let user = null;
        for (let i = 0; i < data.length; i++) {
            user = renderClickableImportantUserWithScore(
                {
                    accountid:          data[i].accountid,
                    accounttitle:       data[i].accounttitle,
                    email:              data[i].email,
                    score_updated_at:   data[i].score_updated_at,
                    score:              data[i].score,
                },
                'long');
            const div = document.createElement('div');
            div.appendChild(user);
            frag.append(div);
        }
    }

    if (frag.childNodes.length) {
        return frag;
    }

    return renderDefaultIfEmptySpan(null);
};

//Resource
const renderResource = (value, tooltip) => {
    const n = getNumberOfSymbols();
    value = value ? value : '/';
    tooltip = tooltip ? tooltip : '/';

    const el = truncateWithHellip(value, n);
    el.title = tooltip;

    return el;
};

const renderResourceWithoutQuery = record => {
    let value = record.url;
    if (record.title) {
        value = record.title;
    }

    const tooltip  = record.url;

    return renderResource(value, tooltip);
};

const renderResourceWithQueryAndEventType = record => {
    let url = record.url;
    if (record.query) {
        url += record.query;
    }

    let tooltip = url;
    if (url && url.length > MAX_TOOLTIP_URL_LENGTH) {
        tooltip = url.slice(0, MAX_TOOLTIP_URL_LENGTH) + HELLIP;
    }

    const frag = document.createDocumentFragment();

    const el = document.createElement('p');
    el.className = `bullet ${record.event_type}`;
    frag.appendChild(tooltipWrap(tooltip, el, false));

    const text = document.createTextNode(record.event_type_name);
    frag.appendChild(tooltipWrap(tooltip, text, true));

    return frag;
};

const renderClickableResourceWithoutQuery = record => {
    const url  = renderResourceWithoutQuery(record);
    const html = wrapWithResourceLink(url, record);

    return html;
};

//IP
const renderIp = record => {
    const n = getNumberOfSymbols();

    let span = truncateWithHellip(record.ip, n);

    if (record.isp_name) {
        span.title = record.isp_name;
    }

    //html = wrapWithFraudSpan(html, record);

    return span;
};

const renderClickableIp = record => {
    const ip  = renderIp(record);
    const html = wrapWithIpLink(ip, record);

    return html;
};

const renderIpAndFlag = (ip, record) => {
    const countryCode = record.country_iso;
    const code = !COUNTRIES_EXCEPTIONS.includes(countryCode) ? countryCode.toLowerCase() : 'lh';
    const iso = (countryCode !== null && countryCode !== undefined) ? countryCode : '';

    const net = record.isp_name;
    const tooltip = (net !== null && net !== undefined && net !== '') ? `${iso} - ${net}` : `${iso}`;
    const alternative = (record.full_country !== null && record.full_country !== undefined) ? record.full_country : '';

    const frag = document.createDocumentFragment();

    const img = document.createElement('img');
    img.src = `/ui/images/flags/${code}.svg`;
    img.alt = alternative;
    frag.appendChild(img);

    const span = document.createElement('span');
    span.appendChild(ip);
    frag.appendChild(span);

    const result = tooltipWrap(tooltip, frag, true);

    return wrapWithCountryDiv(result);
};

const renderIpWithCountry = record => {
    let ip = record.ip;
    const n = getNumberOfSymbols();

    if (ip && ip.length > n) {
        ip = ip.slice(0, n) + HELLIP;
    }

    const el = document.createTextNode(ip);

    return renderIpAndFlag(el, record);
};

const renderClickableIpWithCountry = record => {
    let ip = record.ip;
    const n = getNumberOfSymbols();

    if (ip && ip.length > n) {
        ip = ip.slice(0, n) + HELLIP;
    }

    const el = document.createTextNode(ip);

    return renderIpAndFlag(wrapWithIpLink(el, record), record);
};

const renderIpType = record => {
    const tooltipMap = {
        'blacklisted'   : 'Is on a blacklist.',
        'localhost'     : 'Belongs to a local network.',
        'residential'   : 'Is assigned by an ISP to a homeowner.',
        'datacenter'    : 'Belongs to a datacenter.',
        'applerelay'    : 'Belongs to the iCloud Private Relay.',
        'starlink'      : 'Belongs to SpaceX satellites.',
        'spam_list'     : 'Is on a spam list.',
        'tor'           : 'Belongs to The Onion Router network.',
        'vpn'           : 'Belongs to a virtual private network.'
    };

    const ipType  = record.ip_type.toLowerCase().replace(' ', '_');
    const tooltip = (tooltipMap[ipType] !== null && tooltipMap[ipType] !== undefined) ? tooltipMap[ipType] : record.ip_type;

    const span = document.createElement('span');
    span.className = `iptype ${ipType}`;
    span.textContent = record.ip_type;

    return tooltipWrap(tooltip, span, false);
};

//Net
const renderNetName = (record, length = 'default') => {
    let name = record.netname || record.description || record.asn || '';
    let span = null;

    if (name) {
        const regex = /-|_/ig;
        name = name.replace(regex, ' ');

        name = name.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        //TODO: move to constants
        span = truncateWithHellip(name, length == 'long' ? MAX_STRING_LONG_NETNAME_IN_TABLE : MAX_STRING_SHORT_NETNAME_IN_TABLE);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderCidr = record => {
    const span = document.createElement('span');
    span.textContent = renderDefaultIfEmpty(record.cidr);

    return span;
};

const renderAsn = record => {
    const asn = (ASN_OVERRIDE[record.asn] !== undefined) ? ASN_OVERRIDE[record.asn] : record.asn;
    const span = document.createElement('span');
    span.textContent = renderDefaultIfEmpty(asn);

    return span;
};

const renderClickableAsn = record => {
    const asn  = renderAsn(record);
    const html = wrapWithIspLink(asn, record);

    return html;
};

//Country
const renderCountry = (code, value, tooltip) => {
    code = !COUNTRIES_EXCEPTIONS.includes(code) ? code : 'lh';
    value = (value !== null && value !== undefined) ? value : '';

    const frag = document.createDocumentFragment();

    const img = document.createElement('img');
    img.src = `/ui/images/flags/${code.toLowerCase()}.svg`;
    img.alt = tooltip ? tooltip : value;
    frag.appendChild(img);

    const span = tooltipWrap(tooltip, value, true);
    frag.appendChild(span);

    return wrapWithCountryDiv(frag);
};

const renderCountryFull = (record, applyTooltip = true) => {
    const code    = record.country_iso;
    const value   = record.full_country;
    const tooltip = applyTooltip ? record.full_country : null;

    return renderCountry(code, value, tooltip);
};

const renderCountryIso = record => {
    const code    = record.country_iso;
    const value   = record.country_iso;
    const tooltip = record.full_country;

    return renderCountry(code, value, tooltip);
};

const renderClickableCountry = (record, applyTooltip = true) => {
    const country = renderCountryFull(record, applyTooltip);

    return wrapWithCountryLink(country, record);
};

const renderClickableCountryName = record => {
    const value   = record.full_country;
    const country = (value !== null && value !== undefined) ? value : '';

    const el = document.createTextNode(country);

    return wrapWithCountryLink(el, record);
};

const renderClickableCountryTruncated = record => {
    const fullValue = record.full_country;
    let value   = record.full_country;
    value = (value !== null && value !== undefined) ? value : '';
    value = value.length <= MAX_STRING_LENGTH_FOR_TILE ? value : record.country_iso;

    const span = tooltipWrap(fullValue, value, true);

    return wrapWithCountryLink(span, record);
};

//Device
const renderClickableBotId = record => {
    const device = document.createTextNode(record.id);

    return wrapWithBotLink(device, record);
};

const renderDevice = record => {
    const deviceIsNormal = NORMAL_DEVICES.includes(record.device_name);

    const deviceTypeTooltip = record.device_name ? record.device_name : 'unknown';
    const deviceTypeImg = deviceIsNormal ? record.device_name : 'unknown';

    let deviceTypeName = 'unknown';

    if (record.device_name && record.device_name !== 'unknown') {
        deviceTypeName = deviceIsNormal ? record.device_name : 'other device';
    }

    const frag = document.createDocumentFragment();

    const img = document.createElement('img');
    img.src = `/ui/images/icons/${deviceTypeImg}.svg`;
    frag.appendChild(tooltipWrap(deviceTypeTooltip, img, true));

    const el = document.createTextNode(deviceTypeName);
    frag.appendChild(tooltipWrap(record.ua, el, true));

    return frag;
};

const renderDeviceWithOs = record => {
    const deviceTypeTooltip = record.device_name ? record.device_name : 'unknown';
    const deviceTypeImg = NORMAL_DEVICES.includes(record.device_name) ? record.device_name : 'unknown';

    let os = record.os_name ? record.os_name : 'N/A';
    os += record.os_version ? ' ' + record.os_version : '';

    const frag = document.createDocumentFragment();

    const img = document.createElement('img');
    img.src = `/ui/images/icons/${deviceTypeImg}.svg`;
    frag.appendChild(tooltipWrap(deviceTypeTooltip, img, true));

    const el = document.createTextNode(os);
    frag.appendChild(tooltipWrap(record.ua, el, true));

    return frag;
};

const renderLanguage = record => {
    const language  = record.lang;
    const languages = parse(language);

    const rec1 = languages.find(rec => rec.code);
    const rec2 = languages.find(rec => rec.region);

    let codeAndRegion = [];
    let el = null;

    if (rec1) {
        codeAndRegion.push(rec1.code.toUpperCase());
    }

    if (rec2) {
        codeAndRegion.push(rec2.region.toUpperCase());
    }

    codeAndRegion = codeAndRegion.join('-');
    if (codeAndRegion) {
        el = document.createElement('span');
        el.className = 'nolight';
        el.textContent = codeAndRegion;
    }

    return renderDefaultIfEmptySpan(el);
};

const renderOs = record => {
    let os = record.os;

    if ('string' == typeof os) {
        os = os.trim();
    }

    if (os) {
        os = truncateWithHellip(os, MAX_STRING_LENGTH_FOR_TILE);
    }

    os = renderDefaultIfEmptySpan(os);

    return os;
};

const renderClickableOs = record => {
    const os   = renderOs(record);
    const el   = wrapWithBotLink(os, record);

    return el;
};

const renderDomain = (record, length = 'short') => {
    let domain = record.domain;

    if (domain) {
        domain = truncateWithHellip(domain, getNumberOfSymbols(length));
    }

    return renderDefaultIfEmptySpan(domain);
};

const renderClickableDomain = (record, length = 'short') => {
    let span = renderDomain(record, length);
    const el = (record.id !== null && record.id !== undefined) ? wrapWithDomainLink(span, record) : span;

    return el;
};

const renderBrowser = record => {
    let browser = record.browser;

    if ('string' == typeof browser) {
        browser = browser.trim();
    }

    if (browser) {
        browser = browser.split('.');
        browser = browser[0].trim();

        browser = truncateWithHellip(browser, MAX_STRING_LENGTH_FOR_TILE);
    }

    return renderDefaultIfEmptySpan(browser);
};

const renderQuery = record => {
    const textarea = document.createElement('textarea');
    textarea.readonly = true;
    textarea.rows = 4;
    textarea.cols = 37;
    textarea.textContent = record.query;

    return textarea;

};

const renderReferer = record => {
    const textarea = document.createElement('textarea');
    textarea.readonly = true;
    textarea.rows = 4;
    textarea.cols = 37;
    textarea.textContent = record.referer;

    return textarea;

};

const renderUserAgent = record => {
    const textarea = document.createElement('textarea');
    textarea.readonly = true;
    textarea.rows = 5;
    textarea.cols = 37;
    textarea.textContent = record.ua;

    return textarea;
};

const renderDefaultIfEmptyElement = (value) => {
    const span = document.createElement('span');
    span.textContent = (value) ? value : HYPHEN;

    return span;
};

const renderDefaultIfEmpty = (value) => {
    if (value) {
        return value;
    }

    return HYPHEN;
};

const renderDefaultIfEmptySpan = (span) => {
    if (!span) {
        span = document.createElement('span');
    }

    if (!span.childNodes.length) {
        span.textContent = renderDefaultIfEmpty(span.textContent);
    }

    return span;
};

const renderBlacklistItem = record => {
    let span = null;
    let rec = {};

    const type = record.type;

    if (type === 'ip') {
        rec.ip = record.value;
        rec.ipid = record.entity_id;
        span = renderClickableIp(rec);
    }
    if (type === 'email' || type === 'phone') {
        const el = document.createTextNode(renderDefaultIfEmpty(record.value));
        rec.accountid = record.accountid;
        span = wrapWithUserLink(el, rec);
    }

    return renderDefaultIfEmptySpan(span);
};

const renderBlacklistType = record => {
    const span = document.createElement('span');
    let type = record.type;

    if (type) {
        if (type.toUpperCase() === 'IP') {
            type = 'IP';
        } else {
            type = type.replace(
                /\b\w+/g,
                function(txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                }
            );
        }

        span.textContent = type;
        span.className = 'typestatus';
    }

    return renderDefaultIfEmptySpan(span);
};

const renderSensorErrorColumn = record => {
    const obj = openJson(record.error_text);
    const s = (obj !== null) ? obj.join('; ') : null;
    return truncateWithHellip(s, MAX_STRING_LONG_NETNAME_IN_TABLE);
};

const renderSensorError = record => {
    const obj = openJson(record.error_text);
    const s = (obj !== null) ? obj.join(';\n') : null;

    const textarea = document.createElement('textarea');
    textarea.readonly = true;
    textarea.rows = 4;
    textarea.cols = 37;
    textarea.textContent = renderDefaultIfEmpty(s);

    return textarea;
};

const renderRawRequest = record => {
    const obj = openJson(record.raw);
    const s = (obj !== null) ? JSON.stringify(obj, null, 2) : null;

    const textarea = document.createElement('textarea');
    textarea.readonly = true;
    textarea.rows = 24;
    textarea.cols = 37;
    textarea.textContent = renderDefaultIfEmpty(s);

    return textarea;
};

const renderErrorType = record => {
    const frag = document.createDocumentFragment();

    const el = document.createElement('p');
    el.className = `bullet ${record.error_value}`;
    frag.appendChild(el);

    const span = document.createElement('span');
    span.textContent = record.error_name;
    frag.appendChild(span);

    return frag;
};

const renderMailto = record => {
    const subject = 'Request body';
    const body = record.raw;

    const href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

    const el = document.createElement('a');
    el.href = href;
    el.textContent = 'Email request data';

    return el;
};

const currentPlanRender = (data, type, record, _meta) => {
    const value = record.sub_plan_api_calls;
    const text = (value !== null && value !== undefined) ? value + ' API calls' : MIDLINE_HELLIP;

    const span = document.createElement('span');
    span.textContent = text;

    return span;
};

const currentStatusRender = (data, type, record, meta) => {
    const value = record.sub_status;
    const text = (value !== null && value !== undefined) ? value : MIDLINE_HELLIP;

    const span = document.createElement('span');
    span.textContent = text;

    return span;
};

const currentUsageRender = (data, type, record, meta) => {
    let value = record.sub_calls_used;
    const used = (value !== null && value !== undefined) ? value : MIDLINE_HELLIP;
    value = record.sub_calls_limit;
    const limit = (value !== null && value !== undefined) ? value : MIDLINE_HELLIP;

    const span = document.createElement('span');
    span.textContent = used + '/' + limit;

    return span;
};

const currentBillingEndRender = (data, type, record, meta) => {
    const value = record.sub_next_billed;
    const text = (value !== null && value !== undefined) ? renderDateString(value.replace('T', ' ')) : MIDLINE_HELLIP;

    const span = document.createElement('span');
    span.textContent = text;

    return span;
};

const updateCardButtonRender = (data, type, record, meta) => {
    const url = record.sub_update_url;
    const token = record.apiToken;
    const disabled = (url !== null && url !== undefined && token !== null && token !== undefined) ? '' : 'disabled';

    const button = document.createElement('button');
    button.className = 'button is-primary';
    button.type = 'submit';
    button.textContent = 'Update';
    button.onclick = (e) => {
        window.open(url, '_blank');
    };

    if (disabled) {
        button.disabled = true;
    }

    return button;
};

const renderEnrichmentCalculation = data => {
    const keys = {
        ip: 'IP',
        //ua: 'Devices',
        phone: 'Phones',
        email: 'Emails',
        domain: 'Domains',
    };
    let result = [];
    let sum = 0;

    for (const key in keys) {
        const c = (data[key] === undefined || data[key] === null) ? 0 : data[key];
        sum += c;
        result.push(keys[key].padEnd(16, '.') + String(c));
    }

    result.push(''.padEnd(16, '='));
    result.push('Total: ' + String(sum));
    const text = result.join('\n');

    const textarea = document.createElement('textarea');
    textarea.readOnly = true;
    textarea.rows = 6;
    textarea.cols = 37;
    textarea.value = text;

    return textarea;
};

const renderRulePlayResult = (users, count, uid) => {
    if (!count) {
        return document.createTextNode(`There are no users that match ${uid} rule.`);
    }

    const result = document.createDocumentFragment();
    const txt = (count === 1) ? `One user matching ${uid} rule: ` : `${count} users matching ${uid} rule: `;
    result.appendChild(document.createTextNode(txt));

    const list = document.createDocumentFragment();
    users.forEach((record, idx) => {
        if (idx > 0) list.appendChild(document.createTextNode(', '));
        list.appendChild(renderClickableUser(record));
    });

    result.appendChild(list);

    return result;
};

const renderChartTooltipPart = (color, label, val) => {
    const span = document.createElement('span');
    span.className = 'chart-tooltip';
    span.style.backgroundColor = color;
    span.textContent = `${label}: ${val}`;

    return span;
};

export {
    //Primitive
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderProportion,

    //Event
    renderHttpCode,
    renderHttpMethod,
    renderTotalFrame,

    //Time
    renderTime,
    renderDate,
    renderTimeMs,

    //Choices selector
    renderRuleSelectorItem,
    renderRuleSelectorChoice,
    renderEventTypeSelectorItem,
    renderEventTypeSelectorChoice,
    renderIpTypeSelectorItem,
    renderIpTypeSelectorChoice,
    renderEntityTypeSelectorItem,
    renderEntityTypeSelectorChoice,
    renderScoresRangeSelectorItem,
    renderScoresRangeSelectorChoice,

    //User
    renderUser,                                 //! only internal usage
    renderUserId,
    renderUserScore,                            //! only internal usage
    renderUserWithScore,                        //! only internal usage
    renderClickableImportantUserWithScore,
    renderClickableImportantUserWithScoreTile,
    renderUserForEvent,
    renderTimestampForEvent,
    renderUserFirstname,
    renderUserLastname,
    renderClickableUser,
    renderImportantUser,                        //! not used
    renderClickableImportantUser,               //! only internal usage
    renderUserActionButtons,
    renderUserReviewedStatus,
    renderBlacklistButtons,
    renderScoreDetails,
    renderUserCounter,

    //Email
    renderEmail,
    renderReputation,
    renderClickableEmail,                       //! not used

    //Phone
    renderPhone,
    renderFullCountry,
    renderPhoneType,
    renderClickablePhone,                       //! not used
    renderPhoneCarrierName,
    renderUsersList,

    //Country
    renderCountryIso,
    renderCountryFull,                          //! only internal usage
    renderClickableCountry,
    renderClickableCountryName,
    renderClickableCountryTruncated,

    //Resource
    renderResourceWithQueryAndEventType,
    renderResourceWithoutQuery,                 //! only internal usage
    renderClickableResourceWithoutQuery,

    //IP
    renderIp,
    renderIpType,
    renderClickableIp,
    renderIpWithCountry,
    renderClickableIpWithCountry,

    //Net
    renderAsn,
    renderClickableAsn,
    renderNetName,
    renderCidr,

    //Device
    renderDevice,
    renderDeviceWithOs,
    renderClickableBotId,

    //OS
    renderOs,
    renderClickableOs,                          //! not used

    //Domain
    renderDomain,
    renderClickableDomain,

    //Browser
    renderBrowser,

    //Language
    renderLanguage,

    //Details panel
    renderQuery,
    renderUserAgent,
    renderReferer,

    //Blacklist item
    renderBlacklistType,
    renderBlacklistItem,

    //Logbook
    renderSensorErrorColumn,
    renderSensorError,
    renderRawRequest,
    renderErrorType,
    renderMailto,

    //UsageStats
    currentPlanRender,
    currentStatusRender,
    currentUsageRender,
    currentBillingEndRender,
    updateCardButtonRender,

    //Enrichment
    renderEnrichmentCalculation,

    //Rule
    renderRulePlayResult,

    //Chart
    renderChartTooltipPart,
};
