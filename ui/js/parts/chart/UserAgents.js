import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class UserAgentsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('User agents', 'red'),
        ];
    }
}
