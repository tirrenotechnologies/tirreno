import {
    renderUserActionButtons,
    renderUserReviewedStatus,
} from './DataRenderers.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {replaceAll} from './utils/String.js?v=2';

export class SingleReviewButton {

    constructor(userId) {
        this.userId = userId;

        const me = this;
        const onButtonClick = this.onButtonClick.bind(this);

        if (me.legitFraudButtonsBlock) {
            //Get HTML w/ new fraud&legit buttons
            let fraud = null;
            if ('true'  == me.legitFraudButtonsBlock.dataset.userFraud) fraud = true;
            if ('false' == me.legitFraudButtonsBlock.dataset.userFraud) fraud = false;

            const record = {reviewed: true, accountid: me.userId, fraud: fraud};

            me.legitFraudButtonsBlock.replaceChildren(renderUserActionButtons(record, false));
        }

        if (me.reviewedButton) {
            this.reviewedButton.addEventListener('click', onButtonClick, false);
        }

        if (me.legitButton) {
            this.legitButton.addEventListener('click', onButtonClick, false);
        }

        if (me.fraudButton) {
            this.fraudButton.addEventListener('click', onButtonClick, false);
        }
    }

    onButtonClick(e) {
        e.preventDefault();

        const me = this;
        const target = e.target;
        const url = '/admin/manageUser';
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        const data = {userId: this.userId, type: target.dataset.type, token: token};

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

    onSuccess() {
        const me   = this.scope;

        const target = this.target;
        const type   = target.dataset.type;

        target.classList.remove('is-loading');

        if ('reviewed-button' === target.id) {
            //Get HTML w/ new fraud&legit buttons
            const record = {reviewed: true, accountid: me.userId};

            const div = target.closest('div.head-button');
            div.replaceChildren(renderUserActionButtons(record, false));

            const onButtonClick = me.onButtonClick.bind(me);

            if (me.legitButton) {
                me.legitButton.addEventListener('click', onButtonClick, false);
            }

            if (me.fraudButton) {
                me.fraudButton.addEventListener('click', onButtonClick, false);
            }
        }

        const buttonType = target.dataset.buttonType;
        if ('fraudButton' === buttonType) {
            let reviewStatus = '';
            if ('fraud' === type) {
                reviewStatus = 'Blacklisted';
                me.fraudButton.classList.replace('is-neutral', 'is-highlighted');
                me.fraudButton.setAttribute('disabled', '');

                me.legitButton.classList.replace('is-highlighted', 'is-neutral');
                me.legitButton.removeAttribute('disabled');
            } else {
                reviewStatus = 'Whitelisted';
                me.legitButton.classList.replace('is-neutral', 'is-highlighted');
                me.legitButton.setAttribute('disabled', '');

                me.fraudButton.classList.replace('is-highlighted', 'is-neutral');
                me.fraudButton.removeAttribute('disabled');
            }
            const tile = document.querySelector('#user-id-tile');
            const title = tile.querySelector('#review-status span').title;

            const record = {
                fraud:              (reviewStatus === 'Blacklisted'),
                latest_decision:    title,
            };

            tile.querySelector('#review-status').replaceChildren(renderUserReviewedStatus(record));

            const userTitleSpan = document.querySelector('h1 span');

            userTitleSpan.textContent = (reviewStatus === 'Blacklisted') ? 'X' : 'OK';
            userTitleSpan.classList.remove('high', 'medium', 'low', 'empty');
            userTitleSpan.classList.add((reviewStatus === 'Blacklisted') ? 'low' : 'high');
        }
    }

    get legitFraudButtonsBlock() {
        return document.getElementById('legit-fraud-buttons-block');
    }

    get fraudButton() {
        return document.querySelector('[data-type="fraud"]');
    }

    get legitButton() {
        return document.querySelector('[data-type="legit"]');
    }

    get reviewedButton() {
        return document.getElementById('reviewed-button');
    }
}
