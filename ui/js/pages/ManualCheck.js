import {BasePage} from './Base.js?v=2';

import {ManualCheckItems} from '../parts/ManualCheckItems.js?v=2';

export class ManualCheckPage extends BasePage {
    constructor() {
        super('manual-check');
    }

    initUi() {
        new ManualCheckItems();

        const onTableLinkClick = e => {
            e.preventDefault();

            const f = e.target.closest('form');
            f.submit();

            return false;
        };

        const historyTableLinks = document.querySelectorAll('[data-item-id="manual-check-history-item"]');
        historyTableLinks.forEach(link => link.addEventListener('click', onTableLinkClick, false));
    }
}
