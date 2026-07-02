import {BasePage} from './Base.js?v=0.10.0';
import {ManualCheckItems} from '../parts/ManualCheckItems.js?v=0.10.0';
import {closest} from '../parts/utils/Functions.js?v=0.10.0';

export class ManualCheckPage extends BasePage {
    constructor() {
        super('manual-check');
    }

    initUi() {
        new ManualCheckItems();

        const onTableLinkClick = e => {
            e.preventDefault();

            const f = closest(e.target, 'form');
            f.submit();

            return false;
        };

        const historyTableLinks = document.querySelectorAll('[data-item-id="manual-check-history-item"]');
        historyTableLinks.forEach(link => link.addEventListener('click', onTableLinkClick, false));
    }
}
