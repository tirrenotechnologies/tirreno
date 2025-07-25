import {BasePanel} from './BasePanel.js?v=2';
import {
    renderTime,
    renderHttpCode,
    renderHttpMethod,
    renderClickableImportantUserWithScore,
    renderUserId,
    renderUserReviewedStatus,
    renderDate,
    renderScoreDetails,
    renderEmail,
    renderReputation,
    renderBoolean,
    renderDefaultIfEmptyElement,
    renderClickableDomain,
    renderPhone,
    renderFullCountry,
    renderPhoneCarrierName,
    renderPhoneType,
    renderUserCounter,
    renderClickableResourceWithoutQuery,
    renderDeviceWithOs,
    // renderClickableDeviceId,
    renderBrowser,
    renderLanguage,
    renderCidr,
    renderNetName,
    renderClickableIpWithCountry,
    renderClickableCountryName,
    renderReferer,
    renderUserAgent,
    renderQuery,
    renderClickableAsn,
    renderUserFirstname,
    renderUserLastname,
    renderIpType,
} from '../DataRenderers.js?v=2';

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
        data.event_time                 = renderTime(event_record.time);
        data.event_http_code            = renderHttpCode(event_record);
        data.event_http_method          = renderHttpMethod(event_record);
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
        data.user_id              = renderClickableImportantUserWithScore(current_email_record, 'long');
        data.accounttitle         = renderUserId(data.accounttitle);
        data.reviewed_status      = renderUserReviewedStatus(data);
        data.latest_decision      = renderDate(data.latest_decision);
        data.score_details        = renderScoreDetails(data);

        data.email                = renderEmail(data, 'long');
        data.reputation           = renderReputation(data);
        //data.email_profiles       = renderBoolean(data.email_profiles);
        data.free_provider        = renderBoolean(data.free_email_provider);
        data.data_breach          = renderBoolean(data.data_breach);
        data.data_breaches        = renderDefaultIfEmptyElement(data.data_breaches);
        data.blockemails          = renderBoolean(data.blockemails);
        data.email_fraud_detected = renderBoolean(data.email_fraud_detected);
        //  TODO: return alert_list back in next release
        //data.email_alert_list     = renderBoolean(data.email_alert_list);
        data.email_earliest_breach= renderDate(data.email_earliest_breach);

        const domain_record = {
            domain:     data.domain,
            id:         data.domainid,
            http_code:  data.domain_return_code,
        };
        data.domain                 = renderClickableDomain(domain_record, 'long');
        data.tranco_rank            = renderDefaultIfEmptyElement(data.tranco_rank);
        data.blockdomains           = renderBoolean(data.blockdomains);
        data.disposable_domains     = renderBoolean(data.disposable_domains);
        data.domain_disabled        = renderBoolean(data.domain_disabled);
        data.domain_creation_date   = renderDate(data.domain_creation_date);
        data.domain_expiration_date = renderDate(data.domain_expiration_date);
        data.domain_return_code     = renderHttpCode(domain_record);

        const phone_record = {
            phonenumber:    data.phonenumber,
            country_id:     data.phone_country_id,
            country_iso:    data.phone_country_iso,
            full_country:   data.phone_full_country,
            carrier_name:   data.carrier_name,
            type:           data.phone_type
        };
        data.phonenumber          = renderPhone(phone_record);
        data.phone_country        = renderFullCountry(data.phone_full_country);
        data.carrier_name         = renderPhoneCarrierName(phone_record);
        data.phone_type           = renderPhoneType(phone_record);
        data.phone_users          = renderUserCounter(data.phone_users, 2);
        data.phone_invalid        = renderBoolean(data.phone_invalid);
        data.phone_fraud_detected = renderBoolean(data.phone_fraud_detected);
        //data.phone_profiles       = renderBoolean(data.phone_profiles);
        //  TODO: return alert_list back in next release
        //data.phone_alert_list     = renderBoolean(data.phone_alert_list);

        data.url                  = renderClickableResourceWithoutQuery(data);

        const browser_name      = (data.browser_name !== null && data.browser_name !== undefined) ? data.browser_name : '';
        const browser_version   = (data.browser_version !== null && data.browser_version !== undefined) ? data.browser_version : '';

        const device_record = {
            id:             data.deviceid,
            ua:             data.ua,
            os_name:        data.os_name,
            os_version:     data.os_version,
            device_name:    data.device_name,
            browser:        `${browser_name} ${browser_version}`,
            lang:           data.lang
        };
        data.device               = renderDeviceWithOs(device_record);
        //data.device_id            = renderClickableDeviceId(device_record);
        data.browser              = renderBrowser(device_record);
        data.lang                 = renderLanguage(device_record);
        data.device_created       = renderDate(data.device_created);

        data.ua_modified          = renderBoolean(data.ua_modified);
        const ip_country_record = {
            isp_name:       data.netname,
            ipid:           data.ipid,
            ip:             data.ip,
            country_id:     data.ip_country_id,
            country_iso:    data.ip_country_iso,
            full_country:   data.ip_full_country,
        };

        data.cidr                 = renderCidr(data);
        data.netname              = renderNetName(data);

        data.ip                   = renderClickableIpWithCountry(ip_country_record);
        data.ip_country           = renderClickableCountryName(ip_country_record);

        data.referer              = renderReferer(data);
        data.ua                   = renderUserAgent(data);
        data.query                = renderQuery(data);

        data.asn                  = renderClickableAsn(data);

        data.firstname            = renderUserFirstname(data);
        data.lastname             = renderUserLastname(data);

        data.ip_users             = renderUserCounter(data.ip_users, 2);
        //data.ip_events            = data.ip_events;
        data.ip_spamlist          = renderBoolean(data.spamlist);
        data.ip_type              = renderIpType(data);
        //  TODO: return alert_list back in next release
        //data.ip_alert_list        = renderBoolean(data.ip_alert_list);

        /***
        data.tor                  = renderBoolean(data.tor);
        data.vpn                  = renderBoolean(data.vpn);
        data.relay                = renderBoolean(data.relay);
        data.data_center          = renderBoolean(data.data_center);
        ***/
        return data;
    }
}
