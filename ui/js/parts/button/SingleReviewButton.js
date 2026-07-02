import {
    renderUserActionButtons,
    renderUserReviewedStatus,
} from '../DataRenderers.js?v=0.10.0';
import {Constants} from '../utils/Constants.js?v=0.10.0';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=0.10.0';
import {replaceAll} from '../utils/String.js?v=0.10.0';
import {
    replaceChildren,
    closest,
} from '../utils/Functions.js?v=0.10.0';
import {BaseButton} from './BaseButton.js?v=0.10.0';

export class SingleReviewButton extends BaseButton {
    constructor(userId) {
        super();
        this.userId = userId;

        const me = this;
        const onButtonClick = this.onButtonClick.bind(this);

        if (me.legitFraudButtonsBlock) {
            //Get HTML w/ new fraud&legit buttons
            const userFraud = me.legitFraudButtonsBlock.dataset.userFraud;
            const fraud = userFraud === 'true' ? true : (userFraud === 'false' ? false : null);
            const record = {addedToReview: true, accountid: me.userId, fraud: fraud};

            replaceChildren(me.legitFraudButtonsBlock, renderUserActionButtons(record, false));
        }

        if (me.addToReviewButton) {
            this.addToReviewButton.addEventListener('click', onButtonClick, false);
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
        const url = `${window.app_base}/manageUser`;
        const token = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        const data = {userId: this.userId, type: target.dataset.type, token: token};

        target.classList.add('is-loading');

        $.ajax({
            type: 'PUT',
            url: url,
            data: data,
            scope: me,
            target: target,
            success: me.onSuccess,
            error: handleAjaxError,
            complete: function() {
                target.classList.remove('is-loading');
            },
            dataType: 'json'
        });

        return false;
    }

    onSuccess() {
        const me   = this.scope;

        const target = this.target;
        const type   = target.dataset.type;

        target.classList.remove('is-loading');

        if ('add-to-review-button' === target.id) {
            //Get HTML w/ new fraud&legit buttons
            let record = {addedToReview: true, accountid: me.userId};
            replaceChildren(closest(target, 'div.head-button'), renderUserActionButtons(record, false));

            record = {fraud: null, added_to_review: true};
            replaceChildren(document.getElementById('review-status'), renderUserReviewedStatus(record));

            const onButtonClick = me.onButtonClick.bind(me);

            if (me.legitButton) {
                me.legitButton.addEventListener('click', onButtonClick, false);
            }

            if (me.fraudButton) {
                me.fraudButton.addEventListener('click', onButtonClick, false);
            }

            const userTitleSpan = document.querySelector('h1 span');
            userTitleSpan.textContent = '!';
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

            replaceChildren(tile.querySelector('#review-status'), renderUserReviewedStatus(record));

            const userTitleSpan = document.querySelector('h1 span');

            userTitleSpan.textContent = (reviewStatus === 'Blacklisted') ? Constants.MULTIPLICATION_SIGN : 'OK';
            userTitleSpan.classList.remove('high', 'medium', 'low', 'empty');
            userTitleSpan.classList.add((reviewStatus === 'Blacklisted') ? 'low' : 'high');
        }

        me.setMenuCount();
        me.setBlacklistMenuCount();
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

    get addToReviewButton() {
        return document.getElementById('add-to-review-button');
    }
}
