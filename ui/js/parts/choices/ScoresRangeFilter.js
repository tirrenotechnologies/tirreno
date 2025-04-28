import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderScoresRangeSelectorItem,
    renderScoresRangeSelectorChoice,
} from '../DataRenderers.js?v=2';

export class ScoresRangeFilter extends BaseFilter {
    constructor() {
        super('#scores-range-selectors', renderScoresRangeSelectorItem, renderScoresRangeSelectorChoice, 'scoresRangeFilterChanged');
    }
}
