import {handleAjaxError} from '../utils/ErrorHandler.js?v=0.10.0';
import {getRuleClass} from '../utils/String.js?v=0.10.0';
import {Tooltip} from '../Tooltip.js?v=0.10.0';
import {BaseButton} from './BaseButton.js?v=0.10.0';
import {replaceChildren} from '../utils/Functions.js?v=0.10.0';
import {
    renderClickableUser,
    renderProportion,
    renderRulePlayResult,
} from '../DataRenderers.js?v=0.10.0';
import {closest} from '../utils/Functions.js?v=0.10.0';

export class RulesGridActionButtons extends BaseButton {
    constructor(tableId) {
        super();
        this.tableId = tableId;
        const onTableLoaded = this.onTableLoaded.bind(this);
        window.addEventListener('tableLoaded', onTableLoaded, false);
    }

    onTableLoaded(e) {
        // get buttons and hang listeners
        const onPlayButtonClick = this.onPlayButtonClick.bind(this);
        this.playButtons.forEach(button => button.addEventListener('click', onPlayButtonClick, false));

        const onSaveButtonClick = this.onSaveButtonClick.bind(this);
        this.saveButtons.forEach(button => button.addEventListener('click', onSaveButtonClick, false));

        const onSelectChange = this.onSelectChange.bind(this);
        this.selects.forEach(select => select.addEventListener('change', onSelectChange, false));

        if (this.reloadRulesInput) {
            this.reloadRulesInput.disabled = false;
        }
    }

    onPlayButtonClick(e) {
        e.preventDefault();

        this.updateDisabled(true);

        const currentPlayButton = closest(e.target, 'button');
        currentPlayButton.classList.add('is-loading');

        const ruleUid = currentPlayButton.dataset.ruleUid;

        const params  = {
            ruleUid:    ruleUid,
            token:      this.csrf,
        };

        $.ajax({
            url: `${window.app_base}/checkRule`,
            type: 'get',
            context: {currentPlayButton: currentPlayButton, ruleUid: ruleUid, tableId: this.tableId},
            data: params,
            success: this.onCheckRuleLoad,          // without binding to keep simultaneous calls scopes separate
            error: handleAjaxError,
            complete: this.updateDisabled.bind(this, false)
        });

        return false;
    }

    onCheckRuleLoad(data, status) {
        if ('success' !== status || 0 === data.length) {
            return;
        }

        this.currentPlayButton.classList.remove('is-loading');

        let row     = document.querySelector(`#${this.tableId} tr[data-rule-uid="${this.ruleUid}"]`);
        let nextRow = row.nextElementSibling;
        if (!nextRow || nextRow.dataset.ruleUid) {
            const ex = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 6;

            replaceChildren(ex, td);

            nextRow = row.parentNode.insertBefore(ex, row.nextSibling);
        }

        replaceChildren(nextRow.querySelector('td'), renderRulePlayResult(data.users, data.count, data.section, this.ruleUid));

        // 3 is index of proportion column
        replaceChildren(row.children[3], renderProportion(data.proportion, data.proportion_updated_at));

        Tooltip.addTooltipsToRulesProportion();
    }

    updateDisabled(disabled) {
        this.playButtons.forEach(button => button.disabled = disabled);
    }

    onSelectChange(e) {
        e.preventDefault();

        const field = e.target;
        const parentRow = closest(field, 'tr');
        const saveButton = parentRow.querySelector('button[type="button"]');

        const value = field.value;
        const cls   = getRuleClass(parseInt(value, 10));

        const newClassName = `ruleHighlight ${cls}`;
        parentRow.querySelector('h3').className = newClassName;

        if (field.dataset.initialValue == value) {
            parentRow.classList.remove('input-field-changed');
            saveButton.classList.add('is-hidden');
        } else {
            parentRow.classList.add('input-field-changed');
            saveButton.classList.remove('is-hidden');
        }

        return false;
    }

    onSaveButtonClick(e) {
        e.preventDefault();

        const currentSelector = closest(e.target, 'tr').querySelector('select');
        const currentSaveButton = closest(e.target, 'button');
        currentSaveButton.classList.add('is-loading');

        const select = closest(currentSaveButton, 'tr').querySelector('select');
        const token = this.csrf;

        const params = {
            rule:   select.name,
            value:  select.value,
            token:  token,
        };

        $.ajax({
            url: `${window.app_base}/saveRule`,
            type: 'PUT',
            data: params,
            context: {
                currentSaveButton:  currentSaveButton,
                currentSelector:    currentSelector,
                value:              select.value,
            },
            error: handleAjaxError,
            success: this.onSaveLoaded,         // without binding to keep simultaneous calls scopes separate
            complete: function() {
                this.currentSaveButton.classList.remove('is-loading');
            },
        });

        return false;
    }

    onSaveLoaded(data, status) {
        if ('success' !== status) {
            return;
        }

        this.currentSelector.value = this.value;
        this.currentSaveButton.classList.remove('is-loading');

        const parentRow = closest(this.currentSaveButton, 'tr');
        const saveButton = parentRow.querySelector('button[type="button"]');

        parentRow.classList.remove('input-field-changed');
        saveButton.classList.add('is-hidden');
    }

    get csrf() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    get reloadRulesInput() {
        return document.querySelector('#reload-rules-form input[type="submit"]');
    }

    get selects() {
        return document.querySelectorAll(`#${this.tableId} td select`);
    }

    get saveButtons() {
        return document.querySelectorAll(`#${this.tableId} td button[type="button"]`);
    }

    get playButtons() {
        return document.querySelectorAll(`#${this.tableId} td button[data-rule-uid]`);
    }
}
