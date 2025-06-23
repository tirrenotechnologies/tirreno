import {BaseLineChart} from './BaseLine.js?v=2';

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
