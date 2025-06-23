import {BaseLineChart} from './BaseLine.js?v=2';

export class BotsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Bots', 'red'),
        ];
    }
}
