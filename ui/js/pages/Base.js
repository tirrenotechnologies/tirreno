import {AutocompleteBlock} from '../parts/AutocompleteBlock.js';
import {Tooltip} from '../parts/Tooltip.js?v=2';

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
            closeNotificationButtons.forEach(
                button => button.addEventListener('click', onCloseNotificationButtonClick, false)
            );
        }

        this.procedureNotifications.forEach(notification => {
            const btn = notification.querySelector('.delete');
            if (!btn) return;
            btn.addEventListener('click', () => {notification.remove();}, false);
        });
    }

    onCloseNotificationButtonClick() {
        const notification = event.target.closest('.notification.system');
        if (notification) {
            notification.remove();
        }
    }

    get initTooltip() {
        return true;
    }

    get closeNotificationButtons() {
        return document.querySelectorAll('.notification.system:not(.is-hidden) .delete');
    }

    get procedureNotifications() {
        return document.querySelectorAll('#success-procedure-notification, #error-procedure-notification');
    }
}
