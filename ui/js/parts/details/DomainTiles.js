import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderBoolean,
    renderDefaultIfEmpty,
    renderDate,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadDomainDetails';
const ELEMS = [
    'free-email', 'tranco-rank', 'unavailable', 'disposable',
    'creation-date', 'expiration-date', 'total-account', 'fraud'];

export class DomainTiles extends BaseTiles {
    updateTiles(data) {
        document.getElementById('free-email').innerHTML         = renderBoolean(data.free_email_provider);
        document.getElementById('tranco-rank').innerHTML        = renderDefaultIfEmpty(data.tranco_rank);
        document.getElementById('unavailable').innerHTML        = renderBoolean(data.disabled);
        document.getElementById('disposable').innerHTML         = renderBoolean(data.disposable_domains);

        document.getElementById('creation-date').innerHTML      = renderDate(data.creation_date);
        document.getElementById('expiration-date').innerHTML    = renderDate(data.expiration_date);
        document.getElementById('total-account').innerHTML      = data.total_account;
        document.getElementById('fraud').innerHTML              = data.fraud;
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
