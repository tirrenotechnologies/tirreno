import {BaseTiles} from './BaseTiles.js?v=2';
import {
    renderBoolean,
    renderDate,
    renderDefaultIfEmpty,
    renderReputation,
    renderUserId,
    renderUserFirstname,
    renderUserLastname,
    renderUserReviewedStatus,
} from '../DataRenderers.js?v=2';

const URL   = '/admin/loadUserDetails';

export class UserTiles extends BaseTiles {
    updateTiles(data) {
        this.updateIdDetails(data);
        this.updateIpDetails(data);
        this.updateEmailDetails(data);
        this.updateDomainDetails(data);
    }

    updateIdDetails(data) {
        const tile = document.querySelector('#user-id-tile');
        const record = data.userDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#signup-date').innerHTML        = renderDate(record.created);
        tile.querySelector('#lastseen').innerHTML           = renderDate(record.lastseen);
        tile.querySelector('#latest-decision').innerHTML    = renderDate(record.latest_decision);
        tile.querySelector('#review-status').innerHTML      = renderUserReviewedStatus(record);
        tile.querySelector('#firstname').innerHTML          = renderUserFirstname(record);
        tile.querySelector('#lastname').innerHTML           = renderUserLastname(record);
        tile.querySelector('#userid').innerHTML             = renderUserId(record.userid);
    }

    updateIpDetails(data) {
        const tile = document.querySelector('#user-ip-tile');
        const record = data.ipDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#datacenter').innerHTML     = renderBoolean(record.withdc);
        tile.querySelector('#vpn').innerHTML            = renderBoolean(record.withvpn);
        tile.querySelector('#tor').innerHTML            = renderBoolean(record.withtor);
        tile.querySelector('#apple-relay').innerHTML    = renderBoolean(record.withar);
        tile.querySelector('#ip-shared').innerHTML      = renderBoolean(record.sharedips);
        tile.querySelector('#spam-list').innerHTML      = renderBoolean(record.spamlist);
        tile.querySelector('#blacklisted').innerHTML    = renderBoolean(record.fraud_detected);
    }

    updateEmailDetails(data) {
        const tile = document.querySelector('#user-email-tile');
        const record = data.emailDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#reputation').innerHTML         = renderReputation(record);
        //tile.querySelector('#no-profiles').innerHTML        = renderBoolean(record.profiles === null ? null : !record.profiles);
        tile.querySelector('#no-breach').innerHTML          = renderBoolean(record.data_breach === null ? null : !record.data_breach);
        tile.querySelector('#total-breaches').innerHTML     = renderDefaultIfEmpty(record.data_breaches);
        tile.querySelector('#earliest-breach').innerHTML    = renderDate(record.earliest_breach);
        tile.querySelector('#free-provider').innerHTML      = renderBoolean(record.free_email_provider);
        tile.querySelector('#spam-list').innerHTML          = renderBoolean(record.blockemails);
        tile.querySelector('#blacklisted').innerHTML        = renderBoolean(record.fraud_detected);
    }

    updateDomainDetails(data) {
        const tile = document.querySelector('#user-domain-tile');
        const record = data.domainDetails;
        this.removeLoaderBackground(tile);

        tile.querySelector('#total-accounts').innerHTML = renderDefaultIfEmpty(record.total_account);
        tile.querySelector('#registered-on').innerHTML  = renderDate(record.creation_date);
        tile.querySelector('#expires-on').innerHTML     = renderDate(record.expiration_date);
        tile.querySelector('#disposable').innerHTML     = renderBoolean(record.disposable_domains);
        tile.querySelector('#global-rank').innerHTML    = renderDefaultIfEmpty(record.tranco_rank);
        tile.querySelector('#spam-list').innerHTML      = renderBoolean(record.blockdomains);
        tile.querySelector('#unavailable').innerHTML    = renderBoolean(record.disabled);
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
