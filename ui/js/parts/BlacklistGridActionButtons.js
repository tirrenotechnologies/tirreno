import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class BlacklistGridActionButtons {

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
        const url  = '/admin/removeBlacklisted';

        const data = {
            id:    target.dataset.itemId,
            type:  target.dataset.itemType,
            token: me.csrf
        };

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

    onSuccess(dta, status) {
        if ('success' !== status) {
            return;
        }

        const me = this.scope;

        const target    = this.target;
        const tableRow  = target.closest('tr');

        target.classList.remove('is-loading');
        target.setAttribute('disabled', '');
        target.innerHTML = 'Removed';

        const card  = target.closest('.card');
        const span  = card.querySelector('.card-header-title span');

        let total = parseInt(span.innerHTML);

        if (total > 0) {
            total -= 1;
        }

        span.innerHTML = total;

        if (tableRow) {
            const dataTable = $(`#${me.tableId}`).DataTable();
            dataTable.row(tableRow).remove().draw(false);
        }
    }

    get csrf() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
}
