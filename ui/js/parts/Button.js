import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class Button {
    onSuccessCount(data) {
        const span = document.querySelector('span.reviewed-users-tile');
        span.textContent = data.total;
    }

    setMenuCount() {
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        $.ajax({
            type: 'GET',
            url: `${window.app_base}/admin/loadReviewQueueCount`,
            data: {token: token},
            success: this.onSuccessCount,
            error: handleAjaxError,
        });
    }

    onSuccessBlacklistCount(data) {
        const span = document.querySelector('span.blacklist-users-tile');
        span.textContent = data.total;
    }

    setBlacklistMenuCount() {
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        $.ajax({
            type: 'GET',
            url: `${window.app_base}/admin/loadBlacklistCount`,
            data: {token: token},
            success: this.onSuccessBlacklistCount,
            error: handleAjaxError,
        });
    }
}
