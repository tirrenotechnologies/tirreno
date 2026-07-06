import {BaseTiles} from './BaseTiles.js?v=0.10.0';
import {renderAsn} from '../DataRenderers.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

const URL   = `${window.app_base}/loadIspDetails`;
const ELEMS = ['asn', 'total-ips', 'total-visits', 'total-accounts', 'total-fraud'];

export class IspTiles extends BaseTiles {
    updateTiles(data) {
        replaceChildren(document.getElementById('asn'), renderAsn(data));
        replaceChildren(document.getElementById('total-accounts'), data.total_account);
        replaceChildren(document.getElementById('total-visits'), data.total_visit);
        replaceChildren(document.getElementById('total-fraud'), data.total_fraud);
        replaceChildren(document.getElementById('total-ips'), data.total_ip);
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
