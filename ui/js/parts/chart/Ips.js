import {BaseLineChart} from './BaseLine.js?v=2';

export class IpsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Residential', 'green'),
            this.getSingleSeries('Privacy', 'yellow'),
            this.getSingleSeries('Suspicious', 'red'),
        ];
    }
}
