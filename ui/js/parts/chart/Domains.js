import {BaseLineChart} from './BaseLine.js?v=2';

export class DomainsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total domains', 'green'),
            this.getSingleSeries('New domains', 'yellow'),
        ];
    }
}
