import {BaseTiles} from './BaseTiles.js?v=2';
import {Tooltip} from '../Tooltip.js?v=2';
import {
    renderBoolean,
    renderClickableCountryTruncated,
    renderClickableAsn,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadIpDetails';
const ELEMS = ['country', 'asn', 'blocklist', 'blacklist', 'dc', 'vpn', 'tor', 'ar'];

export class IpTiles extends BaseTiles {
    updateTiles(data) {
        const record = {
            full_country:   data.full_country,
            country_id:     data.country_id,
            country_iso:    data.country_iso,
            asn:            data.asn,
            ispid:          data.ispid,
        };

        document.getElementById('country').replaceChildren(renderClickableCountryTruncated(record));
        document.getElementById('asn').replaceChildren(renderClickableAsn(record));
        document.getElementById('blocklist').replaceChildren(renderBoolean(data.blocklist));
        document.getElementById('blacklist').replaceChildren(renderBoolean(data.fraud_detected));
        document.getElementById('dc').replaceChildren(renderBoolean(data.data_center));
        document.getElementById('vpn').replaceChildren(renderBoolean(data.vpn));
        document.getElementById('tor').replaceChildren(renderBoolean(data.tor));
        document.getElementById('ar').replaceChildren(renderBoolean(data.relay));
    }

    initTooltips() {
        super.initTooltips();
        Tooltip.addTooltipToSpans();
    }

    get elems() {
        return ELEMS;
    }

    get url() {
        return URL;
    }
}
