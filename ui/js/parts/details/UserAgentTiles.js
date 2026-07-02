import {BaseTiles} from './BaseTiles.js?v=0.10.0';
import {
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderBrowser,
    renderOs,
} from '../DataRenderers.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

const URL   = `${window.app_base}/loadUserAgentDetails`;
const ELEMS = ['title', 'os', 'browser', 'modified'];

export class UserAgentTiles extends BaseTiles {
    updateTiles(data) {
        const os = [];
        if (data.os_name)    os.push(data.os_name);
        if (data.os_version) os.push(data.os_version);

        const browser = [];
        if (data.browser_name)    browser.push(data.browser_name);
        if (data.browser_version) browser.push(data.browser_version);

        const record = {
            os: os.join(' '),
            browser: browser.join(' ')
        };

        replaceChildren(document.getElementById('title'), renderDefaultIfEmptyElement(data.title));
        replaceChildren(document.getElementById('os'), renderOs(record));
        replaceChildren(document.getElementById('browser'), renderBrowser(record));
        replaceChildren(document.getElementById('modified'), renderBoolean(data.modified));
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
