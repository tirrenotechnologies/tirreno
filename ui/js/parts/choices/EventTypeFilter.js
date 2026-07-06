import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderEventTypeSelectorItem,
    renderEventTypeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class EventTypeFilter extends BaseFilter {
    constructor() {
        super(
            '#event-type-selectors',
            renderEventTypeSelectorItem,
            renderEventTypeSelectorChoice,
            'eventTypeFilterChanged'
        );
    }
}
