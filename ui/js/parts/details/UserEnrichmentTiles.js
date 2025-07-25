import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderBoolean,
    renderDate,
    renderDefaultIfEmptyElement,
    renderReputation,
    renderUserId,
    renderUserFirstname,
    renderUserLastname,
    renderUserReviewedStatus,
    renderPhone,
    renderFullCountry,
    renderPhoneCarrierName,
    renderPhoneType,
    renderUserCounter,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadUserEnrichmentDetails';

export class UserEnrichmentTiles extends BaseTiles {
    updateTiles(data) {
        this.updateIpDetails(data);
        this.updateEmailDetails(data);
        this.updateDomainDetails(data);
        this.updatePhoneDetails(data);
    }

    updateIpDetails(data) {
        const tile = document.querySelector('#user-ip-tile');

        if (!tile) {
            return;
        }

        const record = data.ipDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#datacenter').replaceChildren(renderBoolean(record.withdc));
        tile.querySelector('#vpn').replaceChildren(renderBoolean(record.withvpn));
        tile.querySelector('#tor').replaceChildren(renderBoolean(record.withtor));
        tile.querySelector('#apple-relay').replaceChildren(renderBoolean(record.withar));
        tile.querySelector('#ip-shared').replaceChildren(renderBoolean(record.sharedips));
        tile.querySelector('#spam-list').replaceChildren(renderBoolean(record.spamlist));
        tile.querySelector('#blacklisted').replaceChildren(renderBoolean(record.fraud_detected));
    }

    updateEmailDetails(data) {
        const tile = document.querySelector('#user-email-tile');

        if (!tile) {
            return;
        }

        const record = data.emailDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#reputation').replaceChildren(renderReputation(record));
        //tile.querySelector('#no-profiles').replaceChildren(renderBoolean(record.profiles === null ? null : !record.profiles);
        tile.querySelector('#no-breach').replaceChildren(renderBoolean(record.data_breach === null ? null : !record.data_breach));
        tile.querySelector('#total-breaches').replaceChildren(renderDefaultIfEmptyElement(record.data_breaches));
        tile.querySelector('#earliest-breach').replaceChildren(renderDate(record.earliest_breach));
        tile.querySelector('#free-provider').replaceChildren(renderBoolean(record.free_email_provider));
        tile.querySelector('#spam-list').replaceChildren(renderBoolean(record.blockemails));
        tile.querySelector('#blacklisted').replaceChildren(renderBoolean(record.fraud_detected));
    }

    updateDomainDetails(data) {
        const tile = document.querySelector('#user-domain-tile');

        if (!tile) {
            return;
        }

        const record = data.domainDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#total-accounts').replaceChildren(renderDefaultIfEmptyElement(record.total_account));
        tile.querySelector('#registered-on').replaceChildren(renderDate(record.creation_date));
        tile.querySelector('#expires-on').replaceChildren(renderDate(record.expiration_date));
        tile.querySelector('#disposable').replaceChildren(renderBoolean(record.disposable_domains));
        tile.querySelector('#global-rank').replaceChildren(renderDefaultIfEmptyElement(record.tranco_rank));
        tile.querySelector('#spam-list').replaceChildren(renderBoolean(record.blockdomains));
        tile.querySelector('#unavailable').replaceChildren(renderBoolean(record.disabled));
    }

    updatePhoneDetails(data) {
        const tile = document.querySelector('#user-phone-tile');

        if (!tile) {
            return;
        }

        const record = data.phoneDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#phonenumber').replaceChildren(renderPhone(record));
        tile.querySelector('#invalid').replaceChildren(renderBoolean(record.invalid));
        tile.querySelector('#country').replaceChildren(renderFullCountry(record.full_country));
        tile.querySelector('#carrier').replaceChildren(renderPhoneCarrierName(record));
        tile.querySelector('#type').replaceChildren(renderPhoneType(record));
        tile.querySelector('#shared').replaceChildren(renderUserCounter(record.shared, 2));
        tile.querySelector('#blacklist').replaceChildren(renderBoolean(record.fraud_detected));
    }

    removeLoaderBackground(tile) {
        const backgrounds = tile.querySelectorAll('.loading-background');
        for (let i = 0; i < backgrounds.length; i++) {
            backgrounds[i].classList.remove('loading-background');
        }
    }

    get elems() {
        return [];
    }

    get url() {
        return URL;
    }
}
