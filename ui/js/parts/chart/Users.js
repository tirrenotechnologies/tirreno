import {BaseLineChart} from './BaseLine.js?v=2';

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
