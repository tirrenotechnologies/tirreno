import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class ResourcesChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('200', 'green'),
            this.getSingleSeries('404', 'yellow'),
            this.getSingleSeries('403 & 500', 'red'),
        ];
    }
}
