import {BasePanel} from './BasePanel.js?v=2';
import {
    renderPhone,
    renderDefaultIfEmptyElement,
    renderFullCountry,
    renderPhoneCarrierName,
    renderPhoneType,
    renderUserCounter,
    renderBoolean,
    renderUsersList,
} from '../DataRenderers.js?v=2';

export class PhonePanel extends BasePanel {

    constructor() {
        let eventParams = {
            enrichment: true,
            type: 'phone',
            url: '/admin/phoneDetails',
            cardId: 'phone-card',
            panelClosed: 'phonePanelClosed',
            closePanel: 'closePhonePanel',
            rowClicked: 'phoneTableRowClicked',
        };
        super(eventParams);
    }

    proceedData(data) {
        const phone_record = {
            phonenumber:    data.phone_number,
            country_id:     data.country_id,
            country_iso:    data.country_iso,
            full_country:   data.full_country,
            carrier_name:   data.carrier_name,
            type:           data.type
        };
        data.phone_number           = renderPhone(phone_record);
        data.phone_national         = renderDefaultIfEmptyElement(data.national_format);
        data.country                = renderFullCountry(data.full_country);
        data.carrier_name           = renderPhoneCarrierName(phone_record);
        data.type                   = renderPhoneType(phone_record);
        data.shared                 = renderUserCounter(data.shared, 2);

        // to 'No Profiles'
        //data.profiles               = renderBoolean(data.profiles === null ? null : data.profiles === 0);

        data.fraud_detected         = renderBoolean(data.fraud_detected);
        data.invalid                = renderBoolean(data.invalid);
        //  TODO: return alert_list back in next release
        //data.alert_list           = renderBoolean(data.alert_list);

        data.shared_users           = renderUsersList(data.shared_users);

        // also data.checked is used

        return data;
    }
}
