import {
    renderDefaultIfEmptyElement,
    renderBoolean,
    renderDate,
    renderCountryIso,
    renderHttpCode,
    renderPhoneType,
    renderPhoneCarrierName,
    renderAsn,
} from '../parts/DataRenderers.js?v=2';

export class ManualCheckItems {

    constructor() {
        const table = document.querySelector('.events-card.is-hidden');

        if (!table) return;

        table.classList.remove('is-hidden');

        const itemType = table.dataset.itemType;

        if ('ip' == itemType) {
            this.enrichIpDetails();
        }

        if ('email' == itemType) {
            this.enrichEmailDetails();
        }

        if ('domain' == itemType) {
            this.enrichDomainDetails();
        }

        if ('phone' == itemType) {
            this.enrichPhoneDetails();
        }
    }

    enrichPhoneDetails() {
        let item  = null;

        item = 'iso_country_code';
        this.renderCountryIso(item);

        item  = 'type';
        this.renderPhoneType(item);

        item = 'invalid';
        this.renderBoolean(item);

        item  = 'profiles';
        this.renderProfiles(item);

        item = 'carrier_name';
        this.renderPhoneCarrierName(item);
    }

    enrichDomainDetails() {
        let item   = null;

        item = 'blockdomains';
        this.renderBoolean(item);

        item = 'disposable_domains';
        this.renderBoolean(item);

        item = 'free_email_provider';
        this.renderBoolean(item);

        item = 'geo_ip';
        this.renderCountryIso(item);

        item = 'geo_html';
        this.renderCountryIso(item);

        item = 'web_server';
        this.renderDefaultIfEmptyElement(item);

        item = 'hostname';
        this.renderDefaultIfEmptyElement(item);

        item = 'emails';
        this.renderDefaultIfEmptyElement(item);

        item = 'phone';
        this.renderDefaultIfEmptyElement(item);

        item = 'discovery_date';
        this.renderDate(item);

        item = 'creation_date';
        this.renderDate(item);

        item = 'expiration_date';
        this.renderDate(item);

        item = 'mx_record';
        this.renderBoolean(item);

        item = 'return_code';
        this.renderHttpCode(item);

        item = 'disabled';
        this.renderBoolean(item);

        item = 'closest_snapshot';
        this.renderDate(item);
    }

    enrichIpDetails() {
        let item   = null;
        let value  = null;

        item = 'country';
        this.renderCountryIso(item);

        item = 'asn';
        value = this.getItem(item);
        value = {asn: value};
        value = renderAsn(value);
        this.setItem(item, value);

        item = 'hosting';
        this.renderBoolean(item);

        item = 'vpn';
        this.renderBoolean(item);

        item = 'tor';
        this.renderBoolean(item);

        item = 'relay';
        this.renderBoolean(item);

        item = 'starlink';
        this.renderBoolean(item);

        item = 'description';
        this.renderDefaultIfEmptyElement(item);

        item = 'blocklist';
        this.renderBoolean(item);

        item = 'domains_count';
        value = this.getItem(item);
        if (value) {
            value = JSON.parse(value);

            if (Array.isArray(value)) {
                value = value.length;
            } else {
                value = parseInt(value, 10);
            }

            if (isNaN(value)) {
                value = renderDefaultIfEmptyElement(value);
            } else {
                value = !!value;
                value = renderBoolean(value);
            }

        } else {
            value = renderDefaultIfEmptyElement(value);
        }
        this.setItem(item, value);
    }

    enrichEmailDetails() {
        let item   = null;

        item = 'blockemails';
        this.renderBoolean(item);

        item = 'data_breach';
        this.renderDataBreach(item);

        item = 'earliest_breach';
        this.renderDate(item);

        item = 'domain_contact_email';
        this.renderBoolean(item);

        item  = 'profiles';
        this.renderProfiles(item);
    }

    renderDataBreach(itemId) {
        let value;

        value = this.getItem(itemId);

        if (null === value) {
            value = renderDefaultIfEmptyElement(value);
        } else {
            //Revert databreach to "No databreach"
            value = !value;
            value = renderBoolean(value);
        }

        this.setItem(itemId, value);
    }

    renderProfiles(itemId) {
        let value;

        value = this.getItem(itemId);
        value = parseInt(value, 10);

        if (isNaN(value)) {
            value = renderDefaultIfEmptyElement(value);
        } else {
            //Convert to boolean
            value = !!value;

            //Revert profiles to "No profiles"
            value = !value;

            value = renderBoolean(value);
        }

        this.setItem(itemId, value);
    }

    renderDate(itemId) {
        let value;

        value = this.getItem(itemId);
        value = renderDate(value);
        this.setItem(itemId, value);
    }

    renderCountryIso(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {country: value, full_country: value};
        value = renderCountryIso(value);
        this.setItem(itemId, value);
    }

    renderHttpCode(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {http_code: value};
        value = renderHttpCode(value);
        this.setItem(itemId, value);
    }

    renderPhoneType(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {type: value};
        value = renderPhoneType(value);
        this.setItem(itemId, value);
    }

    renderPhoneCarrierName(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {carrier_name: value};
        value = renderPhoneCarrierName(value);
        this.setItem(itemId, value);

    }

    renderBoolean(itemId) {
        let value;

        value = this.getItem(itemId);
        value = renderBoolean(value);
        this.setItem(itemId, value);
    }

    renderDefaultIfEmptyElement(itemId) {
        let value;

        value = this.getItem(itemId);
        value = renderDefaultIfEmptyElement(value);
        this.setItem(itemId, value);
    }

    getItem(itemId, returnNode = false) {
        const td = document.querySelector(`td[data-item-id="${itemId}"]`);

        if (!td) return null;

        const tr = td.closest('tr');

        const valueTd = tr.lastElementChild;
        if (returnNode) {
            return valueTd;
        } else {
            let text  = valueTd.innerText;
            let value = text;

            if ('false' === text) value = false;
            if ('true'  === text) value = true;
            if ('null'  === text) value = null;

            return value;
        }
    }

    setItem(itemId, value) {
        const item = this.getItem(itemId, true);
        if (item) {
            if (item instanceof Node) {
                item.replaceChildren(value);
            } else {
                item.innerHTML = value;
            }
        }
    }
}
