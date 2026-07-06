import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class ReviewQueueChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Whitelisted', 'green'),
            this.getSingleSeries('In review', 'yellow'),
            this.getSingleSeries('Blacklisted', 'red'),
        ];
    }
}
