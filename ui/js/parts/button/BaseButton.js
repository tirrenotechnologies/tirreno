import {handleAjaxError} from '../utils/ErrorHandler.js?v=0.10.0';
import {formatKiloValue} from '../utils/String.js?v=0.10.0';

export class BaseButton {
    onSuccessCount(data) {
        const span = document.querySelector('span.reviewed-users-tile');
        span.textContent = formatKiloValue(data.total);
    }

    setMenuCount() {
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        $.ajax({
            type: 'GET',
            url: `${window.app_base}/loadReviewQueueCount`,
            data: {token: token},
            success: this.onSuccessCount,
            error: handleAjaxError,
        });
    }

    onSuccessBlacklistCount(data) {
        const span = document.querySelector('span.blacklist-users-tile');
        span.textContent = formatKiloValue(data.total);
    }

    setBlacklistMenuCount() {
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        $.ajax({
            type: 'GET',
            url: `${window.app_base}/loadBlacklistCount`,
            data: {token: token},
            success: this.onSuccessBlacklistCount,
            error: handleAjaxError,
        });
    }
}
