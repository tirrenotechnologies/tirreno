import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderDate,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadDomainDetails';
const ELEMS = [
    'free-email', 'tranco-rank', 'unavailable', 'disposable',
    'creation-date', 'expiration-date', 'total-account', 'fraud'];

export class DomainTiles extends BaseTiles {
    updateTiles(data) {
        document.getElementById('free-email').replaceChildren(renderBoolean(data.free_email_provider));
        document.getElementById('tranco-rank').replaceChildren(renderDefaultIfEmptyElement(data.tranco_rank));
        document.getElementById('unavailable').replaceChildren(renderBoolean(data.disabled));
        document.getElementById('disposable').replaceChildren(renderBoolean(data.disposable_domains));

        document.getElementById('creation-date').replaceChildren(renderDate(data.creation_date));
        document.getElementById('expiration-date').replaceChildren(renderDate(data.expiration_date));
        document.getElementById('total-account').replaceChildren(data.total_account);
        document.getElementById('fraud').replaceChildren(data.fraud);
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
