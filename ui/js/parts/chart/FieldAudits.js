import {BaseLineChart} from './BaseLine.js?v=0.10.0';

export class FieldAuditsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total changes', 'yellow'),
        ];
    }
}
