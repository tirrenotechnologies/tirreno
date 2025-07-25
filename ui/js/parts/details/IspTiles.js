import {BaseTiles} from './BaseTiles.js?v=2';
import {renderAsn} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadIspDetails';
const ELEMS = ['asn', 'total-ips', 'total-visits', 'total-accounts', 'total-fraud'];

export class IspTiles extends BaseTiles {
    updateTiles(data) {
        document.getElementById('asn').replaceChildren(renderAsn(data));
        document.getElementById('total-accounts').replaceChildren(data.total_account);
        document.getElementById('total-visits').replaceChildren(data.total_visit);
        document.getElementById('total-fraud').replaceChildren(data.total_fraud);
        document.getElementById('total-ips').replaceChildren(data.total_ip);
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
