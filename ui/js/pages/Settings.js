import {BasePage} from './Base.js?v=0.10.0';
import {DeleteAccountPopUp} from '../parts/popup/DeleteAccountPopUp.js?v=0.10.0';

export class SettingsPage extends BasePage {
    constructor() {
        super('settings');
    }

    initUi() {
        new DeleteAccountPopUp();
    }
}
