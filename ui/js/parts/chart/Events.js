import {BaseLineChart} from './BaseLine.js?v=2';
import {COLOR_GREEN, COLOR_YELLOW, COLOR_LIGHT_GREEN} from '../utils/Constants.js?v=2';

export class EventsChart extends BaseLineChart {
    getSeries() {
        const series = [
            {
                label: 'Day',
                scale: 'DAY',
                value: '{YYYY}-{MM}-{DD}'
            },
            {
                label: 'Total events',
                scale: 'EVENTS',
                value: (u, v) => Number(v.toFixed(0)).toLocaleString(),
                points: {
                    space: 0,
                    fill: COLOR_GREEN,
                },
                stroke: COLOR_GREEN,
                fill: COLOR_LIGHT_GREEN
            },
            {
                label: 'Total users',
                scale: 'EVENTS',
                value: (u, v) => Number(v.toFixed(0)).toLocaleString(),
                points: {
                    space: 0,
                    fill: COLOR_YELLOW,
                },
                stroke: COLOR_YELLOW
            }
        ];

        return series;
    }
}
