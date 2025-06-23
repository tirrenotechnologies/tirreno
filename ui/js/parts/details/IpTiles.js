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
            country: data.abbr_country,
            full_country: data.country,
            serial: data.serial,
            asn: data.asn,
            ispid: data.ispid,
        };

        document.getElementById('country').innerHTML    = renderClickableCountryTruncated(record);
        document.getElementById('asn').innerHTML        = renderClickableAsn(record);
        document.getElementById('blocklist').innerHTML  = renderBoolean(data.blocklist);
        document.getElementById('blacklist').innerHTML  = renderBoolean(data.fraud_detected);
        document.getElementById('dc').innerHTML         = renderBoolean(data.data_center);
        document.getElementById('vpn').innerHTML        = renderBoolean(data.vpn);
        document.getElementById('tor').innerHTML        = renderBoolean(data.tor);
        document.getElementById('ar').innerHTML         = renderBoolean(data.relay);
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
