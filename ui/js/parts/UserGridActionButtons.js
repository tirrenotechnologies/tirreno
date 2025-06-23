import {renderUserActionButtons} from './DataRenderers.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class UserGridActionButtons {

    constructor(tableId) {
        this.tableId = tableId;
        const onTableLoaded = this.onTableLoaded.bind(this);
        window.addEventListener('tableLoaded', onTableLoaded, false);
    }

    onTableLoaded(e) {
        const tableId = e.detail.tableId;
        const buttons = document.querySelectorAll(`#${tableId} button`);

        const onButtonClick = this.onButtonClick.bind(this);
        buttons.forEach(button => button.addEventListener('click', onButtonClick, false));
    }

    onButtonClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const me = this;
        const target = e.target;
        const url = '/admin/manageUser';
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        const data = {userId: target.dataset.userId, type: target.dataset.type, token: token};

        target.classList.add('is-loading');

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            scope: me,
            target: target,
            success: me.onSuccess,
            error: handleAjaxError,
            dataType: 'json'
        });

        return false;
    }

    onSuccessCount(data) {
        const span = document.querySelector('span.reviewedUsersTile');
        span.innerHTML = `${data.total}`;
    }

    setMenuCount() {
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        $.ajax({
            type: 'GET',
            url: '/admin/loadReviewQueueCount',
            data: {token: token},
            success: this.onSuccessCount,
            error: handleAjaxError,
        });
    }

    onSuccess() {
        const me   = this.scope;

        const target     = this.target;
        const type       = target.dataset.type;
        const buttonType = target.dataset.buttonType;
        const accountId  = target.dataset.userId;
        const tableRow   = target.closest('tr');

        target.classList.remove('is-loading');

        const twoButtonsContainer = target.closest('.legitfraud');
        if (twoButtonsContainer && !twoButtonsContainer.hasAttribute('counterUpdated')) {
            twoButtonsContainer.setAttribute('counterUpdated', 0);
        }

        if ('fraudButton' === buttonType) {
            const td = target.closest('td');
            const fraudButton = td.querySelector('[data-type="fraud"]');
            const legitButton = td.querySelector('[data-type="legit"]');

            if ('fraud' === type) {
                fraudButton.classList.replace('is-neutral', 'is-highlighted');
                fraudButton.setAttribute('disabled', '');

                legitButton.classList.replace('is-highlighted', 'is-neutral');
                legitButton.removeAttribute('disabled');
            } else {
                legitButton.classList.replace('is-neutral', 'is-highlighted');
                legitButton.setAttribute('disabled', '');

                fraudButton.classList.replace('is-highlighted', 'is-neutral');
                fraudButton.removeAttribute('disabled');
            }

            const counterUpdated    = twoButtonsContainer.getAttribute('counterUpdated');
            const wasCounterUpdated = parseInt(counterUpdated, 10);
            if (!wasCounterUpdated) {
                const card = target.closest('.card');
                const span = card.querySelector('.card-header-title span');
                let total  = parseInt(span.innerHTML, 10);

                if (total > 0) {
                    total -= 1;
                }

                span.innerHTML = total;

                twoButtonsContainer.setAttribute('counterUpdated', 1);
            }

            if (tableRow) {
                const dataTable = $(`#${me.tableId}`).DataTable();
                dataTable.row(tableRow).remove().draw(false);
                me.setMenuCount();
            }
        }

        if ('reviewedButton' === buttonType) {
            //Get HTML w/ new fraud&legit buttons
            const record = {reviewed: true, accountid: accountId};
            const html   = renderUserActionButtons(record);

            const td = target.closest('td');
            td.innerHTML = html;

            //Add event listener to newly created buttons
            const buttons = td.querySelectorAll('button');
            const onButtonClick = me.onButtonClick.bind(me);
            buttons.forEach(button => button.addEventListener('click', onButtonClick, false));
        }
    }
}
