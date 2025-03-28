import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderEventTypeSelectorItem,
    renderEventTypeSelectorChoice,
} from '../DataRenderers.js?v=2';

export class EventTypeFilter extends BaseFilter {
    constructor() {
        super('#event-type-selectors', renderEventTypeSelectorItem, renderEventTypeSelectorChoice, 'eventTypeFilterChanged');
    }
}
