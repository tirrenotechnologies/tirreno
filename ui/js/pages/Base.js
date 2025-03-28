import {AutocompleteBlock} from '../parts/AutocompleteBlock.js';
import {Tooltip}           from '../parts/Tooltip.js?v=2';

export class BasePage {
    constructor() {
        this.initCommonUi();
    }

    initCommonUi() {
        new AutocompleteBlock();

        document.addEventListener('keyup', e => {
            if (e.key !== '/' || e.ctrlKey || e.metaKey) return;
            if (/^(?:input|textarea|select|button)$/i.test(e.target.tagName)) return;

            e.preventDefault();
            document.getElementById('auto-complete').focus();
        });

        const initTooltip = this.initTooltip;
        if (initTooltip) {
            Tooltip.init();
        }

        const closeNotificationButtons = this.closeNotificationButtons;
        if (closeNotificationButtons && closeNotificationButtons.length) {
            const onCloseNotificationButtonClick = this.onCloseNotificationButtonClick.bind(this);
            closeNotificationButtons.forEach(button => button.addEventListener('click', onCloseNotificationButtonClick, false));
        }

        const procedureNotification = this.procedureNotification;
        if (procedureNotification) {
            const onCloseProcedureNotificationButtonClick = this.onCloseProcedureNotificationButtonClick.bind(this);
            this.closeProcedureNotificationButton.addEventListener('click', onCloseProcedureNotificationButtonClick, false);
        }
    }

    onCloseNotificationButtonClick() {
        const notification = event.target.closest('.notification.system');
        if (notification) {
            notification.remove();
        }
    }

    onCloseProcedureNotificationButtonClick() {
        this.procedureNotification.remove();
    }

    get initTooltip() {
        return true;
    }

    get closeNotificationButtons() {
        return document.querySelectorAll('.notification.system:not(.is-hidden) .delete');
    }

    get procedureNotification() {
        return document.querySelector('#procedure-notification');
    }

    get closeProcedureNotificationButton() {
        return document.querySelector('#procedure-notification .delete');
    }
}
