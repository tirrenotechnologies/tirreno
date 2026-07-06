import {BasePopUp} from './BasePopUp.js?v=0.10.0';

export class ResetApiKeyPopUp extends BasePopUp {
    constructor() {
        const formParams = {
            mainButtonId:       'reset-api-key-btn',
            confirmButtonId:    'confirm-reset-api-key-button',
            formId:             'reset-api-key-form',
            popupId:            'reset-api-key-popup',
        };

        super(formParams);
    }
}
