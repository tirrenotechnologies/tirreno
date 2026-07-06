import {BasePopUp} from './BasePopUp.js?v=0.10.0';

export class DeleteAccountPopUp extends BasePopUp {
    constructor() {
        const formParams = {
            mainButtonId:       'close-account-btn',
            confirmButtonId:    'confirm-close-account-button',
            formId:             'close-account-form',
            popupId:            'close-account-popup',
        };

        super(formParams);
    }
}
