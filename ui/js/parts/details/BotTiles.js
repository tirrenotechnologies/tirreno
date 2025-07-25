import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderBrowser,
    renderOs,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadBotDetails';
const ELEMS = ['title', 'os', 'browser', 'modified'];

export class BotTiles extends BaseTiles {
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

        document.getElementById('title').replaceChildren(renderDefaultIfEmptyElement(data.title));
        document.getElementById('os').replaceChildren(renderOs(record));
        document.getElementById('browser').replaceChildren(renderBrowser(record));
        document.getElementById('modified').replaceChildren(renderBoolean(data.modified));
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
