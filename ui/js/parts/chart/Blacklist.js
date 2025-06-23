import {BaseLineChart} from './BaseLine.js?v=2';

export class BlacklistChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Blacklisted identities', 'red'),
        ];
    }
}
