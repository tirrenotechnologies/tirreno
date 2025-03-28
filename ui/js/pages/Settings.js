import {BasePage} from './Base.js';
import {DeleteAccountPopUp} from '../parts/DeleteAccountPopUp.js?v=2';

export class SettingsPage extends BasePage {

    constructor() {
        super();

        this.initUi();
    }

    initUi() {
        new DeleteAccountPopUp();
    }
}
