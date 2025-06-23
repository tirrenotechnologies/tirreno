import {padZero} from './utils/Date.js?v=2';
import {
    truncateWithHellip,
    escapeForHTMLAttribute,
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
    HORIZONTAL_ELLIPSIS,
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

const wrapWithCountryDiv = html => {
    html = `<div class="flag">${html}</div>`;
    return html;
};

const wrapWithImportantSpan = (html, record) => {
    if (record.is_important) {
        html = `<span class="important-user">${html}</span>`;
    }

    return html;
};

const wrapWithUserLink = (html, record) => {
    html = `<a href="/id/${record.accountid}">${html}</a>`;
    return html;
};

const wrapWithResourceLink = (html, record) => {
    html = `<a href="/resource/${record.url_id}">${html}</a>`;
    return html;
};

const wrapWithIpLink = (html, record) => {
    html = `<a href="/ip/${record.ipid}">${html}</a>`;
    return html;
};

const wrapWithIspLink = (html, record) => {
    if (record.ispid) {
        html = `<a href="/isp/${record.ispid}">${html}</a>`;
    }
    return html;
};

const wrapWithCountryLink = (html, record) => {
    html = `<a href="/country/${record.serial}">${html}</a>`;
    return html;
};

const wrapWithBotLink = (html, record) => {
    html = `<a href="/bot/${record.id}">${html}</a>`;
    return html;
};

const wrapWithPhoneLink = (html, record) => {
    html = `<a href="/phones/${record.id}">${html}</a>`;
    return html;
};

const wrapWithDomainLink = (html, record) => {
    html = `<a href="/domain/${record.id}">${html}</a>`;
    return html;
};

const wrapWithRuleLink = (html, ruleUid) => {
    html = `<a href="/id?ruleUid=${ruleUid}">${html}</a>`;
    return html;
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

const renderTime = (data) => {
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

    return escapeForHTMLAttribute(data);
};

const renderTimeMs = (data) => {
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

const renderDate = (data) => {
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

    return escapeForHTMLAttribute(data);
};

const renderChoicesSelectorItem = (classNames, data, innerHtml) => {
    const itemClass = data.highlighted ? classNames.highlightedState : classNames.itemSelectable;
    const button = `<button type="button" class="${classNames.button}" aria-label="Remove item" data-button="">Remove item</button>`;
    const html = `<div
            class="${classNames.item} ${itemClass}"
            data-item
            data-id="${data.id}"
            data-value="${data.value}"
            ${data.active ? 'aria-selected="true"' : ''}
            ${data.disabled ? 'aria-disabled="true"' : ''}
        >${innerHtml}${button}</div>`;

    return html;
};

const renderChoicesSelectorChoice = (classNames, data, itemSelectText, innerHtml) => {
    const choiceClass = data.disabled ? classNames.itemDisabled : classNames.itemSelectable;
    const html = `<div
            class="${classNames.item} ${classNames.itemChoice} ${choiceClass}"
            data-select-text="${itemSelectText}"
            data-choice
            ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'}
            data-id="${data.id}"
            data-value="${data.value}"
            ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}
        >${innerHtml}</div>`;

    return html;
};

const renderRuleSelectorItem = (classNames, data) => {
    const [uid, className, title] = data.label.split('|');
    const innerHtml = `<span class="ruleHighlight ${className}">${uid}</span>${title}`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderRuleSelectorChoice = (classNames, data, itemSelectText) => {
    const [uid, className, title] = data.label.split('|');
    const innerHtml = `<span class="ruleHighlight ${className}">${uid}</span>${title}`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderEventTypeSelectorItem = (classNames, data) => {
    const [value, name] = data.label.split('|');
    const innerHtml = `<p class="bullet ${value}"></p>${name}`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderEventTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const [value, name] = data.label.split('|');
    const innerHtml = `<p class="bullet ${value}"></p>${name}`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderIpTypeSelectorItem = (classNames, data) => {
    const value = data.label;
    const innerHtml = `<span>${value}</span>`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderIpTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const value = data.label;
    const innerHtml = `<span>${value}</span>`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderEntityTypeSelectorItem = (classNames, data) => {
    const value = data.label;
    const innerHtml = `<span>${value}</span>`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderEntityTypeSelectorChoice = (classNames, data, itemSelectText) => {
    const value = data.label;
    const innerHtml = `<span>${value}</span>`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderScoresRangeSelectorItem = (classNames, data) => {
    const [bottom, top] = data.label.split('|');
    const innerHtml = `<span>${bottom} - ${top}</span>`;

    return renderChoicesSelectorItem(classNames, data, innerHtml);
};

const renderScoresRangeSelectorChoice = (classNames, data, itemSelectText) => {
    const [bottom, top] = data.label.split('|');
    const innerHtml = `<span>${bottom} - ${top}</span>`;

    return renderChoicesSelectorChoice(classNames, data, itemSelectText, innerHtml);
};

const renderHttpCode = record => {
    let html;
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
        html = `<span class="tooltip ${style} ${record.http_code}" title="${tooltip}">${record.http_code}</span>`;
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderHttpMethod = record => {
    let html;

    const type = record.http_method;
    if (type) {
        let style = (type === 'POST' || type === 'GET') ? 'nolight' : 'highlight';
        html = `<span class="${style}">${escapeForHTMLAttribute(type)}</span>`;
    }

    html = renderDefaultIfEmpty(html);
    return html;
};

const renderTotalFrame = (base, val) => {
    const rest = (base !== null && base !== undefined && base > 0 && base >= val) ? (base - val) : HORIZONTAL_ELLIPSIS;
    return (parseInt(base, 10) > parseInt(val, 10)) ? `<span class="addlight">${val}/</span>${rest}` : base;
};

const renderUserCounter = (data, critical = 1) => {
    let html;

    if (Number.isInteger(data) && data >= 0) {
        let style = (data >= critical) ? 'highlight' : 'nolight';
        html = `<span class="${style}">${data}</span>`;
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const checkErrorEventType = record => {
    return {
        event_type_name: (!record.http_code || record.http_code < 400) ? record.event_type_name : `Error ${record.http_code}`,
        event_type: (!record.http_code || record.http_code < 400) ? record.event_type: 'error'
    };
};

const renderBoolean = (data) => {
    let value = null;

    if (false === data) {
        value = '<span class="nolight">No</span>';
    }

    if (true === data) {
        value = '<span class="highlight">Yes</span>';
    }

    value = renderDefaultIfEmpty(value);

    return value;
};

const renderProportion = (n, t) => {
    const number = (typeof n === 'number' && Number.isFinite(n) && n >= 0 && n <= 100)
        ? (n > 0.0 && n < 1.0 ? '<1%' : `${Math.floor(n)}%`)
        : '&minus;';
    const tooltip = t ? `Last updated: ${renderDate(t)}` : '&minus;';

    return `<span class="tooltip" title="${tooltip}">${number}</span>`;
};

const renderUserScore = record => {
    let score = (record.score !== null && record.score !== undefined) ? record.score : '&minus;';
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

    let html = `<span class="ignore-select score ${cls}">${score}</span>`;
    const lastUpdate = `Last updated: ${renderDate(record.score_updated_at)}`;
    html = `<span class="tooltip" title="${lastUpdate}">${html}</span>`;

    return html;
};

//User
const renderUserId = (value) => {
    let html = '';

    if (value) {
        html = truncateWithHellip(value, MAX_STRING_USERID_LENGTH_IN_TABLE);
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderUser = (record, length = 'default') => {
    let html;
    const n = getNumberOfSymbols(length);
    const email = record.email;

    if (email) {
        html = truncateWithHellip(email, n);
    } else if (record.accounttitle) {
        html = renderUserId(record.accounttitle);
    }

    html = renderDefaultIfEmpty(html);

    return html;
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

const renderClikableImportantUser = (record, length = 'default') => {
    const user = renderClickableUser(record, length);
    const html = wrapWithImportantSpan(user, record);

    return html;
};

const renderUserWithScore = (record, length = 'default') => {
    const score = renderUserScore(record);
    return `${score}${renderUser(record, length)}`;
};

const renderClickableImportantUserWithScore = (record, length = 'default') => {
    const score = renderUserScore(record);
    return `${score}${renderClikableImportantUser(record, length)}`;
};

const renderClickableImportantUserWithScoreTile = (record) => {
    return renderClickableImportantUserWithScore(record, 'tile');
};

const renderSession = (record) => {
    let result = '<span class="angrt">&angrt;</span>';

    if (record.session_cnt) {
        const cnt = record.session_cnt;
        const max_t = renderTime(record.session_max_t).split(' ');
        const min_t = renderTime(record.session_min_t).split(' ');
        let value = `${cnt} action`;
        value += (cnt > 1) ? `s (${formatTime(record.session_duration)})` : '';
        const tooltip = (cnt > 1) ? `${min_t[0]} ${min_t[1] || ''} - ${max_t[1] || ''}` : `${max_t[0]} ${max_t[1] || ''}`;
        result = `<span class="tooltip" title="${tooltip}">${value}</span>`;
    }

    return result;
};

const renderUserForEvent = (record, length, sessionGroup, singleUser) => {
    if (!sessionGroup) return renderUserWithScore(record, length);  // regular events
    if (singleUser) return renderSession(record);                   // events on /user/abc page

    return (record.session_cnt) ? renderUserWithScore(record, length) : '<span class="angrt">&angrt;</span>'; // events on /event page
};

const renderTimestampForEvent = (record, sessionGroup, singleUser) => {
    if (sessionGroup && !singleUser && record.session_cnt && record.session_cnt > 1) return renderSession(record);  // events on /event page on session-end rows

    return renderTime(record.time); // events on other pages and on /event page in a middle of the session
};

const renderUserFirstname = record => {
    let html;
    const name = record.firstname;

    if (name) {
        html = name.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        html = truncateWithHellip(html, MAX_STRING_USER_NAME_IN_TABLE);
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderUserLastname = record => {
    let html;
    const name = record.lastname;

    if (name) {
        html = name.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        html = truncateWithHellip(html, MAX_STRING_USER_NAME_IN_TABLE);
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderUserReviewedStatus = record => {
    let html = '<span class="reviewstatus">Not reviewed</span>';
    let latestDecision = renderDate(record.latest_decision);

    if (record.fraud !== null) {
        const reviewStatus = (record.fraud) ? 'Blacklisted' : 'Whitelisted';
        html = `<span class="tooltip reviewstatus ${reviewStatus}" title="${latestDecision}">${reviewStatus}</span>`;
    }

    return html;
};

const renderUserActionButtons = record => {
    let html;
    if (record.reviewed) {
        html = getFraudLegitButtons(record);
    } else {
        html = getToBeReviewedButton(record);
    }

    return html;
};

const renderBlacklistButtons = record => {
    const html = `<button
        class="button is-small dark-loader"
        data-item-id="${record.entity_id}"
        data-item-type="${record.type}"
        data-button-type="deleteButton"
        type="button">Remove</button>`;

    return html;
};

const getFraudLegitButtons = record => {
    let fraudBtnCls = 'is-neutral';
    let legitBtnCls = 'is-neutral';

    let fraudBtnDisabled = '';
    let legitBtnDisabled = '';

    if (true === record.fraud) {
        fraudBtnCls = 'is-highlighted';
        legitBtnCls = 'is-neutral';

        fraudBtnDisabled = 'disabled';
        legitBtnDisabled = '';
    }

    if (false === record.fraud) {
        fraudBtnCls = 'is-neutral';
        legitBtnCls = 'is-highlighted';

        fraudBtnDisabled = '';
        legitBtnDisabled = 'disabled';
    }

    const whitelistButton = `<button
            class="button is-small light-loader ${legitBtnCls}"
            data-type="legit"
            data-user-id="${record.accountid}"
            data-button-type="fraudButton"
            ${legitBtnDisabled}
            type="button">Whitelist</button>`;
    const blacklistButton = `<button
            class="button is-small light-loader ${fraudBtnCls}"
            data-type="fraud"
            data-user-id="${record.accountid}"
            data-button-type="fraudButton"
            ${fraudBtnDisabled}
            type="button">Blacklist</button>`;

    const html = `<div class="legitfraud">${whitelistButton}${blacklistButton}</div>`;

    return html;
};

const getToBeReviewedButton = record => {
    const html = `<button
        class="reviewed button is-small dark-loader"
        data-type="reviewed"
        data-user-id="${record.accountid}"
        data-button-type="reviewedButton"
        type="button">Not reviewed</button>`;

    return html;
};

const renderScoreDetails = record => {
    if (!record.score_calculated) {
        return `<span class="tooltip no-rules-tile" title="${UNDEFINED_RULES_MSG.tooltip}">${UNDEFINED_RULES_MSG.value}</span>`;
    }

    // record should be an array
    let html = '';

    const details = record.score_details;

    if (Array.isArray(details)) {
        let uid = '';
        let descr = '';
        let name = '';
        let part = '';
        for (let i = 0; i < details.length; i++) {
            uid = (details[i].uid !== null && details[i].uid !== undefined) ? details[i].uid : '';
            descr = (details[i].descr !== null && details[i].descr !== undefined) ? details[i].descr : '';
            name = (details[i].name!== null && details[i].name !== undefined) ? details[i].name : '';
            part = `<p><span class="ruleHighlight ${getRuleClass(details[i].score)}"
                >${uid}</span>&nbsp;<span class="ruleName tooltip" title="${descr}">${name}</span></p>`;
            html += wrapWithRuleLink(part, uid);
        }
    }

    if (!html) {
        html = `<span class="tooltip no-rules-tile" title="${NO_RULES_MSG.tooltip}">${NO_RULES_MSG.value}</span>`;
    }

    return html;
};

//Email
const renderEmail = (record, length = MAX_STRING_LENGTH_FOR_EMAIL) => {
    let html;
    const email = record.email;

    if (email) {
        const n = (length === MAX_STRING_LENGTH_FOR_EMAIL) ? length : getNumberOfSymbols(length);

        const value = truncateWithHellip(email, n);

        html = `${value}`;
    }

    html = renderDefaultIfEmpty(html);
    return html;
};

const renderClickableEmail = record => {
    const email = renderEmail(record);

    //Overwrite ID attribute, because we are going to wrap it with domain link, not email
    record.id = record.domain_id;

    const html = wrapWithDomainLink(email, record);

    return html;
};

const renderReputation = record => {
    record = (record !== null && record !== undefined) ? record : {};

    let icon = 'reputation-none';
    let reputation = record.reputation;
    let text = reputation.charAt(0).toUpperCase() + reputation.slice(1);

    if ('low' === reputation)    icon = 'reputation-low';
    if ('medium' === reputation) icon = 'reputation-medium';
    if ('high' === reputation)   icon = 'reputation-high';

    const html = `<img class="tooltip" title="${reputation}" src="/ui/images/icons/${icon}.svg" alt="${reputation}">${text}`;

    return html;
};

//Phone
const renderPhone = (record) => {
    let html;
    const phone = record.phonenumber;

    if (phone) {
        const code = !COUNTRIES_EXCEPTIONS.includes(record.country) ? escapeForHTMLAttribute(record.country) : 'lh';
        const tooltip = (record.full_country !== null && record.full_country !== undefined) ? escapeForHTMLAttribute(record.full_country) : '';

        const n       = MAX_STRING_LENGTH_FOR_PHONE;
        const number  = truncateWithHellip(phone, n);

        const flag    = `<span class="tooltip" title="${tooltip}"
            ><img src="/ui/images/flags/${code.toLowerCase()}.svg" alt="${tooltip}"></span>`;

        html = `${flag}${number}`;
        html = wrapWithCountryDiv(html);
    }

    html = renderDefaultIfEmpty(html);
    return html;
};

const renderClickablePhone = record => {
    const phone = renderPhone(record);
    const html  = wrapWithPhoneLink(phone, record);

    return html;
};

const renderFullCountry = value => {
    let html = '&#65293;';
    if (value) {
        html = truncateWithHellip(value, MAX_STRING_LENGTH_FULL_COUNTRY);
    }
    return html;
};

const renderPhoneCarrierName = (record, length = 'medium') => {
    let html;
    let carrierName = record.carrier_name;

    if (carrierName) {
        const n = getNumberOfSymbols(length);
        carrierName = carrierName.replace(',', '');
        html = truncateWithHellip(carrierName, n);
    }

    html = renderDefaultIfEmpty(html);
    return html;
};

const renderPhoneType = record => {
    let html;
    const type = record.type;

    if (type) {
        let src = 'smartphone.svg';
        if (['landline', 'FIXED_LINE', 'FIXED_LINE_OR_MOBILE', 'TOLL_FREE', 'SHARED_COST'].includes(type)) src = 'landline.svg';
        if (['nonFixedVoip', 'VOIP'].includes(type)) src = 'voip.svg';

        const tooltip = escapeForHTMLAttribute(type.toLowerCase().replace(/_/g, ' '));

        html = `<span class="tooltip" title="${tooltip}"><img src="/ui/images/icons/${src}"/></span>`;
    }
    html = renderDefaultIfEmpty(html);

    return html;
};

const renderUsersList = (data) => {
    // data should be an array
    let html = '';
    if (Array.isArray(data)) {
        let userHtml = '';
        for (let i = 0; i < data.length; i++) {
            userHtml = renderClickableImportantUserWithScore(
                {
                    accountid:          data[i].accountid,
                    accounttitle:       data[i].accounttitle,
                    email:              data[i].email,
                    score_updated_at:   data[i].score_updated_at,
                    score:              data[i].score,
                },
                'long');
            html += `<div>${userHtml}</div>`;
        }
    }
    html = renderDefaultIfEmpty(html);

    return html;
};

//Resource
const renderResource = (value, tooltip) => {
    const n = getNumberOfSymbols();
    value = value ? value : '/';
    tooltip = tooltip ? escapeForHTMLAttribute(tooltip) : '/';
    value = truncateWithHellip(value, n);

    //Create a tooltip data with full url
    value = value.replace(/title=".*?"/, `title="${tooltip}"`);

    return value;
};

const renderResourceWithoutQuery = record => {
    let value = record.url;
    if (record.title) {
        value = record.title;
    }

    const tooltip  = record.url;
    const resource = renderResource(value, tooltip);

    return resource;
};

const renderResourceWithQueryAndEventType = record => {
    let url = record.url;
    if (record.query) {
        url += record.query;
    }

    let tooltip = '';
    if (url.length > MAX_TOOLTIP_URL_LENGTH) {
        tooltip = `${escapeForHTMLAttribute(url.slice(0, MAX_TOOLTIP_URL_LENGTH))}&hellip;`;
    } else {
        tooltip = escapeForHTMLAttribute(url);
    }

    const event_type = checkErrorEventType(record);

    const html = `<p class="bullet ${event_type.event_type} tooltip" title="${tooltip}"
        ></p><span class="tooltip" title="${tooltip}">${event_type.event_type_name}</span>`;

    return html;
};

const renderClickableResourceWithoutQuery = record => {
    const url  = renderResourceWithoutQuery(record);
    const html = wrapWithResourceLink(url, record);

    return html;
};

//IP
const renderIp = record => {
    const n = getNumberOfSymbols();

    let html = truncateWithHellip(record.ip, n);
    let name = escapeForHTMLAttribute(record.isp_name);
    if (name) {
        html = html.replace(/title=".*?"/, `title="${name}"`);
    }
    //html = wrapWithFraudSpan(html, record);

    return html;
};

const renderClickableIp = record => {
    const ip  = renderIp(record);
    const html = wrapWithIpLink(ip, record);

    return html;
};

const renderIpAndFlag = (ip, record) => {
    const countryCode = record.country;
    const code = !COUNTRIES_EXCEPTIONS.includes(countryCode) ? countryCode.toLowerCase() : 'lh';
    const iso = (countryCode !== null && countryCode !== undefined) ? countryCode : '';

    const net = escapeForHTMLAttribute(record.isp_name);
    const tooltip = (net !== null && net !== undefined && net !== '') ? `${iso} - ${net}` : `${iso}`;
    const alternative = (record.full_country !== null && record.full_country !== undefined) ? record.full_country : '';
    const flag = `<img src="/ui/images/flags/${code}.svg" alt="${alternative}">`;

    return wrapWithCountryDiv(`<span class="tooltip" title="${tooltip}">${flag}<span>${ip}</span>`);
};

const renderIpWithCountry = record => {
    let ip = record.ip;
    const n = getNumberOfSymbols();

    if (ip && ip.length > n) {
        ip = `${ip.slice(0, n)}&hellip;`;
    }

    return renderIpAndFlag(ip, record);
};

const renderClickableIpWithCountry = record => {
    let ip = record.ip;
    const n = getNumberOfSymbols();

    if (ip && ip.length > n) {
        ip = `${ip.slice(0, n)}&hellip;`;
    }

    ip = wrapWithIpLink(ip, record);

    return renderIpAndFlag(ip, record);
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

    const html = `<span class="tooltip iptype ${ipType}" title="${tooltip}">${record.ip_type}</span>`;

    return html;
};

//Net
const renderNetName = (record, length = 'default') => {
    let html = record.netname || record.description || record.asn || '';

    if (html) {
        const regex = /-|_/ig;
        html = html.replace(regex, ' ');

        html = html.replace(
            /\b\w+/g,
            function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            }
        );

        //TODO: move to constants
        html = truncateWithHellip(html, length == 'long' ? MAX_STRING_LONG_NETNAME_IN_TABLE : MAX_STRING_SHORT_NETNAME_IN_TABLE);
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderCidr = record => {
    let cidr = record.cidr;
    cidr = renderDefaultIfEmpty(cidr);

    return cidr;
};

const renderAsn = record => {
    let asn = (ASN_OVERRIDE[record.asn] !== undefined) ? ASN_OVERRIDE[record.asn] : record.asn;
    asn = renderDefaultIfEmpty(asn);

    return asn;
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
    tooltip = (tooltip !== null && tooltip !== undefined) ? tooltip : '';

    const country = `<img src="/ui/images/flags/${code.toLowerCase()}.svg" alt="${tooltip}"
        ><span class="tooltip" title="${tooltip}">${value}</span>`;
    const html = wrapWithCountryDiv(country);

    return html;
};

const renderCountryFull = record => {
    const code    = record.country;
    const value   = record.full_country;
    const tooltip = record.full_country;

    const html = renderCountry(code, value, tooltip);

    return html;
};

const renderCountryIso = record => {
    const code    = record.country;
    const value   = record.country;
    const tooltip = record.full_country;

    const html = renderCountry(code, value, tooltip);

    return html;
};

const renderClickableCountry = record => {
    const country = renderCountryFull(record);
    const html = wrapWithCountryLink(country, record);

    return html;
};

const renderClickableCountryName = record => {
    const value   = record.full_country;
    const country = (value !== null && value !== undefined) ? value : '';
    const html = wrapWithCountryLink(country, record);

    return html;
};

const renderClickableCountryTruncated = record => {
    const fullValue = record.full_country;
    let value   = record.full_country;
    value = (value !== null && value !== undefined) ? value : '';
    value = value.length <= MAX_STRING_LENGTH_FOR_TILE ? value : record.country;

    const country = `<span class="tooltip" title="${fullValue}">${value}</span>`;

    return wrapWithCountryLink(country, record);
};

//Device
const renderClickableBotId = record => {
    const device = record.id;
    const html   = wrapWithBotLink(device, record);

    return html;
};

const renderDevice = record => {
    const deviceIsNormal = NORMAL_DEVICES.includes(record.device_name);

    const deviceTypeTooltip = record.device_name ? record.device_name : 'unknown';
    const deviceTypeImg = deviceIsNormal ? record.device_name : 'unknown';

    const ua = escapeForHTMLAttribute(record.ua);

    let deviceTypeName = 'unknown';

    if (record.device_name && record.device_name !== 'unknown') {
        deviceTypeName = deviceIsNormal ? record.device_name : 'other device';
    }

    const html = `<span class="tooltip" title="${deviceTypeTooltip}"><img src="/ui/images/icons/${deviceTypeImg}.svg"
        /></span><span class="tooltip" title="${ua}">${deviceTypeName}</span>`;

    return html;
};

const renderDeviceWithOs = record => {
    const os = record.os_name ? record.os_name : 'N/A';
    const deviceTypeTooltip = record.device_name ? record.device_name : 'unknown';
    const deviceTypeImg = NORMAL_DEVICES.includes(record.device_name) ? record.device_name : 'unknown';

    const ua = escapeForHTMLAttribute(record.ua);

    const html = `<span class="tooltip" title="${deviceTypeTooltip}"><img src="/ui/images/icons/${deviceTypeImg}.svg"
        /></span><span class="tooltip" title="${ua}">${os}</span>`;

    return html;
};

const renderLanguage = record => {
    const language  = record.lang;
    const languages = parse(language);

    const rec1 = languages.find(record => record.code);
    const rec2 = languages.find(record => record.region);

    let codeAndRegion = [];

    if (rec1) {
        codeAndRegion.push(rec1.code.toUpperCase());
    }

    if (rec2) {
        codeAndRegion.push(rec2.region.toUpperCase());
    }

    codeAndRegion = codeAndRegion.join('-');
    if (codeAndRegion) {
        codeAndRegion = `<span class="nolight">${escapeForHTMLAttribute(codeAndRegion)}</span>`;
    }

    const html = renderDefaultIfEmpty(codeAndRegion);

    return html;
};

const renderOs = record => {
    let os = record.os;

    if ('string' == typeof os) {
        os = os.trim();
    }

    if (os) {
        os = truncateWithHellip(os, MAX_STRING_LENGTH_FOR_TILE);
    }

    os = renderDefaultIfEmpty(os);

    return os;
};

const renderClickableOs = record => {
    const os   = renderOs(record);
    const html = wrapWithBotLink(os, record);

    return html;
};

const renderDomain = (record, length = 'short') => {
    let domain = record.domain;

    if (domain) {
        const n = getNumberOfSymbols(length);
        domain = truncateWithHellip(domain, n);
    }

    domain = renderDefaultIfEmpty(domain);

    return domain;
};

const renderClickableDomain = (record, length = 'short') => {
    let html = renderDomain(record, length);
    html = (record.id !== null && record.id !== undefined) ? wrapWithDomainLink(html, record) : html;

    return html;
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

    browser = renderDefaultIfEmpty(browser);
    return browser;
};

const renderQuery = record => {
    return `<textarea readonly rows="4" cols="37">${escapeForHTMLAttribute(renderDefaultIfEmpty(record.query))}</textarea>`;
};

const renderReferer = record => {
    return `<textarea readonly rows="4" cols="37">${escapeForHTMLAttribute(renderDefaultIfEmpty(record.referer))}</textarea>`;
};

const renderUserAgent = record => {
    return `<textarea readonly rows="5" cols="37">${escapeForHTMLAttribute(renderDefaultIfEmpty(record.ua))}</textarea>`;
};

const renderDefaultIfEmpty = (value) => {
    if (value) {
        return value;
    }

    return '&#65293;';
};

const renderBlacklistItem = record => {
    let html = '';
    let rec = {};

    const type = record.type;

    if (type === 'ip') {
        rec.ip = record.value;
        rec.ipid = record.entity_id;
        html = renderClickableIp(rec);
    }
    if (type === 'email') {
        rec.accountid = record.accountid;
        html = escapeForHTMLAttribute(renderDefaultIfEmpty(record.value));
        html = wrapWithUserLink(html, rec);
    }
    if (type === 'phone') {
        rec.accountid = record.accountid;
        html = escapeForHTMLAttribute(renderDefaultIfEmpty(record.value));
        html = wrapWithUserLink(html, rec);
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderBlacklistType = record => {
    let html = '';
    const type = record.type;

    if (type) {
        if (type.toUpperCase() === 'IP') {
            html = 'IP';
        } else {
            html = type.replace(
                /\b\w+/g,
                function(txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                }
            );
        }

        html = `<span class="typestatus">${html}</span>`;
    }

    html = renderDefaultIfEmpty(html);

    return html;
};

const renderSensorErrorColumn = record => {
    const obj = openJson(record.error_text);
    const s = (obj !== null) ? obj.join('; ') : null;
    return truncateWithHellip(renderDefaultIfEmpty(s), MAX_STRING_LONG_NETNAME_IN_TABLE);
};

const renderSensorError = record => {
    const obj = openJson(record.error_text);
    const s = (obj !== null) ? obj.join(';\n') : null;
    return `<textarea readonly rows="4" cols="37">${escapeForHTMLAttribute(renderDefaultIfEmpty(s))}</textarea>`;
};

const renderRawRequest = record => {
    const obj = openJson(record.raw);
    const s = (obj !== null) ? JSON.stringify(obj, null, 2) : null;
    return `<textarea readonly rows="24" cols="37">${escapeForHTMLAttribute(renderDefaultIfEmpty(s))}</textarea>`;
};

const renderErrorType = record => {
    return `<p class="bullet ${escapeForHTMLAttribute(record.error_value)}"></p><span>${escapeForHTMLAttribute(record.error_name)}</span>`;
};

const renderMailto = record => {
    const subject = 'Request body';
    const body = record.raw;

    const href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    const html = `<a href="${href}">Email request data</a>`;

    return html;
};

const renderRawTime = record => {
    return truncateWithHellip(renderDefaultIfEmpty(renderTimeMs(record.raw_time)), MAX_STRING_LONG_NETNAME_IN_TABLE);
};

const currentPlanRender = (data, type, record, _meta) => {
    const value = record.sub_plan_api_calls;
    return (value !== null && value !== undefined) ? value + ' API calls' : HORIZONTAL_ELLIPSIS;
};

const currentStatusRender = (data, type, record, meta) => {
    const value = record.sub_status;
    return (value !== null && value !== undefined) ? value : HORIZONTAL_ELLIPSIS;
};

const currentUsageRender = (data, type, record, meta) => {
    let value = record.sub_calls_used;
    const used = (value !== null && value !== undefined) ? value : HORIZONTAL_ELLIPSIS;
    value = record.sub_calls_limit;
    const limit = (value !== null && value !== undefined) ? value : HORIZONTAL_ELLIPSIS;

    return used + '/' + limit;
};

const currentBillingEndRender = (data, type, record, meta) => {
    const value = record.sub_next_billed;
    return (value !== null && value !== undefined) ? renderDate(value.replace('T', ' ')) : HORIZONTAL_ELLIPSIS;
};

const updateCardButtonRender = (data, type, record, meta) => {
    const url = record.sub_update_url;
    const token = record.apiToken;
    const disabled = (url !== null && url !== undefined && token !== null && token !== undefined) ? '' : 'disabled';

    return `<button
        class="button is-primary"
        type="submit"
        onclick="window.open('${url}', '_blank')"
        ${disabled}>Update</button>`;
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

    return `<textarea readonly rows="6" cols="37">${text}</textarea>`;
};

export {
    //Primitive
    renderBoolean,
    renderDefaultIfEmpty,
    renderProportion,

    //Event
    renderHttpCode,
    renderHttpMethod,
    renderTotalFrame,

    //Time
    renderTime,
    renderDate,

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
    renderUserWithScore,
    renderClickableImportantUserWithScore,
    renderClickableImportantUserWithScoreTile,
    renderUserForEvent,
    renderTimestampForEvent,
    renderUserFirstname,
    renderUserLastname,
    renderClickableUser,
    renderImportantUser,                        //! not used
    renderClikableImportantUser,                //! only internal usage
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
    renderRawTime,

    //UsageStats
    currentPlanRender,
    currentStatusRender,
    currentUsageRender,
    currentBillingEndRender,
    updateCardButtonRender,

    //Enrichment
    renderEnrichmentCalculation,
};
