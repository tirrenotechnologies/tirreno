import {BaseTiles} from './BaseTiles.js?v=0.10.0';
import {
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderDate,
} from '../DataRenderers.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

const URL   = `${window.app_base}/loadDomainDetails`;
const ELEMS = [
    'free-email', 'tranco-rank', 'unavailable', 'disposable',
    'creation-date', 'expiration-date', 'total-account', 'fraud'];

export class DomainTiles extends BaseTiles {
    updateTiles(data) {
        replaceChildren(document.getElementById('free-email'), renderBoolean(data.free_email_provider));
        replaceChildren(document.getElementById('tranco-rank'), renderDefaultIfEmptyElement(data.tranco_rank));
        replaceChildren(document.getElementById('unavailable'), renderBoolean(data.disabled));
        replaceChildren(document.getElementById('disposable'), renderBoolean(data.disposable_domains));

        replaceChildren(document.getElementById('creation-date'), renderDate(data.creation_date));
        replaceChildren(document.getElementById('expiration-date'), renderDate(data.expiration_date));
        replaceChildren(document.getElementById('total-account'), data.total_account);
        replaceChildren(document.getElementById('fraud'), data.fraud);
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
