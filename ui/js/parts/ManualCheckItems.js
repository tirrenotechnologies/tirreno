import * as Renderers from '../parts/DataRenderers.js?v=2';

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
        let value = null;

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
        this.renderDefaultIfEmpty(item);

        item = 'hostname';
        this.renderDefaultIfEmpty(item);

        item = 'emails';
        this.renderDefaultIfEmpty(item);

        item = 'phone';
        this.renderDefaultIfEmpty(item);

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
        value = Renderers.renderAsn(value);
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
        this.renderDefaultIfEmpty(item);

        item = 'blocklist';
        this.renderBoolean(item);

        item = 'domains_count';
        value = this.getItem(item);
        if (value) {
            value = JSON.parse(value);

            if (Array.isArray(value)) {
                value = value.length;
            } else {
                value = parseInt(value);
            }

            if (isNaN(value)) {
                value = Renderers.renderDefaultIfEmpty(value);
            } else {
                value = !!value;
                value = Renderers.renderBoolean(value);
            }

        } else {
            value = Renderers.renderDefaultIfEmpty(value);
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
            value = Renderers.renderDefaultIfEmpty(value);
        } else {
            //Revert databreach to "No databreach"
            value = !value;
            value = Renderers.renderBoolean(value);
        }

        this.setItem(itemId, value);
    }

    renderProfiles(itemId) {
        let value;

        value = this.getItem(itemId);
        value = parseInt(value);

        if (isNaN(value)) {
            value = Renderers.renderDefaultIfEmpty(value);
        } else {
            //Convert to boolean
            value = !!value;

            //Revert profiles to "No profiles"
            value = !value;

            value = Renderers.renderBoolean(value);
        }

        this.setItem(itemId, value);
    }

    renderDate(itemId) {
        let value;

        value = this.getItem(itemId);
        value = Renderers.renderDate(value);
        this.setItem(itemId, value);
    }

    renderCountryIso(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {country: value, full_country: value};
        value = Renderers.renderCountryIso(value);
        this.setItem(itemId, value);
    }

    renderHttpCode(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {http_code: value};
        value = Renderers.renderHttpCode(value);
        this.setItem(itemId, value);
    }

    renderPhoneType(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {type: value};
        value = Renderers.renderPhoneType(value);
        this.setItem(itemId, value);
    }

    renderPhoneCarrierName(itemId) {
        let value;

        value = this.getItem(itemId);
        value = {carrier_name: value};
        value = Renderers.renderPhoneCarrierName(value);
        this.setItem(itemId, value);

    }

    renderBoolean(itemId) {
        let value;

        value = this.getItem(itemId);
        value = Renderers.renderBoolean(value);
        this.setItem(itemId, value);
    }

    renderDefaultIfEmpty(itemId) {
        let value;

        value = this.getItem(itemId);
        value = Renderers.renderDefaultIfEmpty(value);
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
            item.innerHTML = value;
        }
    }
}
