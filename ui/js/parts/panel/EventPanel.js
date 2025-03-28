import * as Renderers from '../DataRenderers.js?v=2';
import {BasePanel} from './BasePanel.js?v=2';

export class EventPanel extends BasePanel {

    constructor() {
        let eventParams = {
            enrichment: false,
            type: 'event',
            url: '/admin/eventDetails',
            cardId: 'event-card',
            panelClosed: 'eventPanelClosed',
            closePanel: 'closeEventPanel',
            rowClicked: 'eventTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        const event_record = {
            time:           data.event_time,
            http_code:      data.event_http_code,
            http_method:    data.event_http_method_name,
        };
        data.event_time                 = Renderers.renderTime(event_record.time);
        data.event_http_code            = Renderers.renderHttpCode(event_record);
        data.event_http_method          = Renderers.renderHttpMethod(event_record);
        //data.event_type_name            = data.event_type_name;

        //Convert to boolean if number exists
        //if(Number.isInteger(data.profiles)) {
        //    data.email_profiles = !!data.profiles;
        //
        //    //Revert profiles to "No profiles"
        //    data.email_profiles = !data.email_profiles;
        //}

        //Convert to boolean if number exists
        //if(Number.isInteger(data.phone_profiles)) {
        //    data.phone_profiles = !!data.phone_profiles;
        //
        //    //Revert profiles to "No profiles"
        //    data.phone_profiles = !data.phone_profiles;
        //}

        if ('boolean' === typeof data.data_breach) {
            //Revert data_breach to "No breach"
            data.data_breach = !data.data_breach;
        }
        const current_email_record = {
            accountid:          data.accountid,
            accounttitle:       data.accounttitle,
            email:              data.current_email,
            score_updated_at:   data.score_updated_at,
            score:              data.score,
            fraud:              data.fraud,
        };
        data.user_id              = Renderers.renderClickableImportantUserWithScore(current_email_record, 'long');
        data.accounttitle         = Renderers.renderUserId(data.accounttitle);
        data.reviewed_status      = Renderers.renderUserReviewedStatus(data);
        data.latest_decision      = Renderers.renderDate(data.latest_decision);
        data.score_details        = Renderers.renderScoreDetails(data);

        data.email                = Renderers.renderEmail(data, 'long');
        data.reputation           = Renderers.renderReputation(data);
        //data.email_profiles       = Renderers.renderBoolean(data.email_profiles);
        data.free_provider        = Renderers.renderBoolean(data.free_email_provider);
        data.data_breach          = Renderers.renderBoolean(data.data_breach);
        data.data_breaches        = Renderers.renderDefaultIfEmpty(data.data_breaches);
        data.blockemails          = Renderers.renderBoolean(data.blockemails);
        data.email_fraud_detected = Renderers.renderBoolean(data.email_fraud_detected);
        //  TODO: return alert_list back in next release
        //data.email_alert_list     = Renderers.renderBoolean(data.email_alert_list);
        data.email_earliest_breach= Renderers.renderDate(data.email_earliest_breach);

        const domain_record = {
            domain:     data.domain,
            id:         data.domainid,
            http_code:  data.domain_return_code,
        };
        data.domain                 = Renderers.renderClickableDomain(domain_record, 'long');
        data.tranco_rank            = Renderers.renderDefaultIfEmpty(data.tranco_rank);
        data.blockdomains           = Renderers.renderBoolean(data.blockdomains);
        data.disposable_domains     = Renderers.renderBoolean(data.disposable_domains);
        data.domain_disabled        = Renderers.renderBoolean(data.domain_disabled);
        data.domain_creation_date   = Renderers.renderDate(data.domain_creation_date);
        data.domain_expiration_date = Renderers.renderDate(data.domain_expiration_date);
        data.domain_return_code     = Renderers.renderHttpCode(domain_record);

        const phone_record = {
            phonenumber:    data.phonenumber,
            country:        data.phone_country,
            full_country:   data.phone_full_country,
            carrier_name:   data.carrier_name,
            type:           data.phone_type
        };
        data.phonenumber          = Renderers.renderPhone(phone_record);
        data.phone_country        = Renderers.renderFullCountry(data.phone_full_country);
        data.carrier_name         = Renderers.renderPhoneCarrierName(phone_record);
        data.phone_type           = Renderers.renderPhoneType(phone_record);
        data.phone_users          = Renderers.renderUserCounter(data.phone_users, 2);
        data.phone_invalid        = Renderers.renderBoolean(data.phone_invalid);
        data.phone_fraud_detected = Renderers.renderBoolean(data.phone_fraud_detected);
        //data.phone_profiles       = Renderers.renderBoolean(data.phone_profiles);
        //  TODO: return alert_list back in next release
        //data.phone_alert_list     = Renderers.renderBoolean(data.phone_alert_list);

        data.url                  = Renderers.renderClickableResourceWithoutQuery(data);

        const browser_name      = (data.browser_name !== null && data.browser_name !== undefined) ? data.browser_name : '';
        const browser_version   = (data.browser_version !== null && data.browser_version !== undefined) ? data.browser_version : '';

        const device_record = {
            id:             data.deviceid,
            ua:             data.ua,
            os_name:        data.os_name,
            device_name:    data.device_name,
            browser:        `${browser_name} ${browser_version}`,
            lang:           data.lang
        };
        data.device               = Renderers.renderDeviceWithOs(device_record);
        //data.device_id            = Renderers.renderClickableDeviceId(device_record);
        data.browser              = Renderers.renderBrowser(device_record);
        data.lang                 = Renderers.renderLanguage(device_record);
        data.device_created       = Renderers.renderDate(data.device_created);

        data.ua_modified          = Renderers.renderBoolean(data.ua_modified);
        const ip_country_record = {
            isp_name:       data.netname,
            ipid:           data.ipid,
            ip:             data.ip,
            country:        data.ip_country,
            full_country:   data.ip_full_country,
            serial:         data.serial             // should be named serial for app/Traits/Enrichment/Ips.php calculations
        };

        data.cidr                 = Renderers.renderCidr(data);
        data.netname              = Renderers.renderNetName(data);

        data.ip                   = Renderers.renderClickableIpWithCountry(ip_country_record);
        data.ip_country           = Renderers.renderClickableCountryName(ip_country_record);

        data.referer              = Renderers.renderReferer(data);
        data.ua                   = Renderers.renderUserAgent(data);
        data.query                = Renderers.renderQuery(data);

        data.asn                  = Renderers.renderClickableAsn(data);

        data.firstname            = Renderers.renderUserFirstname(data);
        data.lastname             = Renderers.renderUserLastname(data);

        data.ip_users             = Renderers.renderUserCounter(data.ip_users, 2);
        //data.ip_events            = data.ip_events;
        data.ip_spamlist          = Renderers.renderBoolean(data.spamlist);
        data.ip_type              = Renderers.renderIpType(data);
        //  TODO: return alert_list back in next release
        //data.ip_alert_list        = Renderers.renderBoolean(data.ip_alert_list);

        /***
        data.tor                  = Renderers.renderBoolean(data.tor);
        data.vpn                  = Renderers.renderBoolean(data.vpn);
        data.relay                = Renderers.renderBoolean(data.relay);
        data.data_center          = Renderers.renderBoolean(data.data_center);
        ***/
        return data;
    }
}
