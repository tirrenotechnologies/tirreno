import {BaseLineChart} from './BaseLine.js?v=2';

export class EventsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total regular events', 'green'),
            this.getSingleSeries('Total warning events', 'yellow'),
            this.getSingleSeries('Total alert events', 'red'),
        ];
    }
}
