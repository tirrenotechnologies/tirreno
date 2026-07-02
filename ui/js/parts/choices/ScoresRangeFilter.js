import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderScoresRangeSelectorItem,
    renderScoresRangeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class ScoresRangeFilter extends BaseFilter {
    constructor() {
        super(
            '#scores-range-selectors',
            renderScoresRangeSelectorItem,
            renderScoresRangeSelectorChoice,
            'scoresRangeFilterChanged'
        );
    }
}
