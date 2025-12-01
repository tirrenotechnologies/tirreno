import {BaseLineChart} from './BaseLine.js?v=2';

export class EventsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Regular events', 'green'),
            this.getSingleSeries('Warning events', 'yellow'),
            this.getSingleSeries('Alert events', 'red'),
        ];
    }
}
