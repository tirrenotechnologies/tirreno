import {BaseLineChart} from './BaseLine.js?v=2';

export class IspsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total ISPs', 'green'),
            this.getSingleSeries('New ISPs', 'yellow'),
        ];
    }
}
