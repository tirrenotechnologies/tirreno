import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class BlacklistChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Blacklisted identities', 'red'),
        ];
    }
}
