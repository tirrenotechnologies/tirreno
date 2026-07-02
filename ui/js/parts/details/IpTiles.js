import {BaseTiles} from './BaseTiles.js?v=0.10.0';
import {Tooltip} from '../Tooltip.js?v=0.10.0';
import {
    renderBoolean,
    renderClickableCountryTruncated,
    renderClickableAsn,
} from '../DataRenderers.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';

const URL   = `${window.app_base}/loadIpDetails`;
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

        replaceChildren(document.getElementById('country'), renderClickableCountryTruncated(record));
        replaceChildren(document.getElementById('asn'), renderClickableAsn(record));
        replaceChildren(document.getElementById('blocklist'), renderBoolean(data.blocklist));
        replaceChildren(document.getElementById('blacklist'), renderBoolean(data.fraud_detected));
        replaceChildren(document.getElementById('dc'), renderBoolean(data.data_center));
        replaceChildren(document.getElementById('vpn'), renderBoolean(data.vpn));
        replaceChildren(document.getElementById('tor'), renderBoolean(data.tor));
        replaceChildren(document.getElementById('ar'), renderBoolean(data.relay));
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
