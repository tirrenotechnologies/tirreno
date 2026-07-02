import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class UsersChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('High trust', 'green'),
            this.getSingleSeries('Average trust', 'yellow'),
            this.getSingleSeries('In review', 'red'),
        ];
    }
}
