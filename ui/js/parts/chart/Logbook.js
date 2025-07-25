import {BaseLineChart} from './BaseLine.js?v=2';

export class LogbookChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Success', 'green'),
            this.getSingleSeries('Validation issues', 'yellow'),
            this.getSingleSeries('Failed', 'red'),
        ];
    }
}
