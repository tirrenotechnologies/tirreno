import {fireEvent} from '../utils/Event.js?v=2';

export class BaseFilter {
    constructor(selectorId, renderItemFn, renderChoiceFn, eventType) {
        this.selectorId = selectorId;
        this.renderItemFn = renderItemFn;
        this.renderChoiceFn = renderChoiceFn;
        this.eventType = eventType;

        const renderItem = renderItemFn;
        const renderChoice = renderChoiceFn;

        const choices = new Choices(`${this.selectorId} select`, {
            removeItemButton: true,
            allowHTML: true,
            callbackOnCreateTemplates: function(strToEl) {
                const {classNames, itemSelectText} = this.config;
                return {
                    item: function({classNames}, data) {return strToEl(renderItem(classNames, data));},
                    choice: function({classNames}, data) {return strToEl(renderChoice(classNames, data, itemSelectText));},
                };
            }
        });
        choices.passedElement.element.addEventListener(
            'change',
            () => fireEvent(this.eventType)
        );
    }

    getValues() {
        return Array.from(document.querySelector(`${this.selectorId} select`).options)
            .filter(option => option.selected)
            .map(option => option.value);
    }

    getEventType() {
        return this.eventType;
    }
}
