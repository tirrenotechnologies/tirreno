import {BaseLineChart} from './BaseLine.js?v=2';

export class FieldAuditsChart extends BaseLineChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total changes', 'yellow'),
        ];
    }
}
