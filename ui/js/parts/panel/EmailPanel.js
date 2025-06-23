import {BasePanel} from './BasePanel.js?v=2';
import {
    renderEmail,
    renderReputation,
    renderBoolean,
    renderDefaultIfEmpty,
    renderDate,
    renderClickableDomain,
    renderHttpCode,
} from '../DataRenderers.js?v=2';

export class EmailPanel extends BasePanel {

    constructor() {
        let eventParams = {
            enrichment: true,
            type: 'email',
            url: '/admin/emailDetails',
            cardId: 'email-card',
            panelClosed: 'emailPanelClosed',
            closePanel: 'closeEmailPanel',
            rowClicked: 'emailTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        data.email                  = renderEmail(data, 'long');
        data.reputation             = renderReputation(data);

        // to 'No breach'
        data.data_breach            = renderBoolean(data.data_breach === null ? null : !data.data_breach);
        // to 'No Profiles'
        // data.profiles               = renderBoolean(data.profiles === null ? null : data.profiles === 0);
        data.data_breaches          = renderDefaultIfEmpty(data.data_breaches);

        data.earliest_breach        = renderDate(data.earliest_breach);
        data.fraud_detected         = renderBoolean(data.fraud_detected);
        data.blockemails            = renderBoolean(data.blockemails);
        //  TODO: return alert_list back in next release
        //data.alert_list           = renderBoolean(data.alert_list);
        data.domain_contact_email   = renderBoolean(data.domain_contact_email);

        data.free_email_provider    = renderBoolean(data.free_email_provider);

        const domain_record = {
            domain:     data.domain,
            id:         data.domain_id,
        };
        data.domain                 = renderClickableDomain(domain_record, 'long');
        data.blockdomains           = renderBoolean(data.blockdomains);
        data.disabled               = renderBoolean(data.disabled);
        data.mx_record              = renderBoolean(data.mx_record === null ? null : !data.mx_record);
        data.disposable_domains     = renderBoolean(data.disposable_domains);
        data.disabled               = renderBoolean(data.disabled);
        data.tranco_rank            = renderDefaultIfEmpty(data.tranco_rank);
        data.creation_date          = renderDate(data.creation_date);
        data.expiration_date        = renderDate(data.expiration_date);
        data.closest_snapshot       = renderDate(data.closest_snapshot);
        data.return_code            = renderHttpCode({http_code: data.return_code});

        // also data.checked is used

        return data;
    }
}
