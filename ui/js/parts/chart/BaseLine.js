import {BaseChart}  from './BaseChart.js?v=2';
import {
    COLOR_MAP,
    X_AXIS_SERIFS,
} from '../utils/Constants.js?v=2';

export class BaseLineChart extends BaseChart {
    getSeries() {
        return [
            this.getDaySeries(),
            this.getSingleSeries('Total events', 'green'),
        ];
    }

    getSingleSeries(label, color) {
        return {
            label: label,
            scale: 'EVENTS',
            value: (u, v) => Number(v.toFixed(0)).toLocaleString(),
            points: {
                space: 0,
                fill: COLOR_MAP[color].main,
            },
            stroke: COLOR_MAP[color].main,
            fill: COLOR_MAP[color].light,
        };
    }

    getAxisConfig() {
        const axes = super.getAxisConfig();

        axes.x.space = function(self, axisIdx, scaleMin, scaleMax, plotDim) {
            let rangeDays   = (scaleMax - scaleMin) / 86400;
            if (rangeDays > X_AXIS_SERIFS) rangeDays = X_AXIS_SERIFS;
            const pxPerDay = plotDim / rangeDays;

            return pxPerDay;
        };

        axes.y.scale =  'EVENTS';
        axes.y.side =   3;
        axes.y.split =  u => [
            u.series[1].min,
            u.series[1].max,
        ];

        return axes;
    }

    getOptions(resolution = 'day') {
        return super.getOptions(resolution, '—');
    }

    // invert lines order to keep originally first line on top layer
    seriesResolutionShift(series, resolution) {
        if (resolution === 'hour') {
            series[0].label = 'Hour';
            series[0].scale = 'HOUR';
            series[0].value = '{YYYY}-{MM}-{DD} {HH}:{mm}';
        } else if (resolution === 'minute') {
            series[0].label = 'Minute';
            series[0].scale = 'MINUTE';
            series[0].value = '{YYYY}-{MM}-{DD} {HH}:{mm}';
        }

        const inverted = [series[0]].concat(series.slice(1).reverse());

        return inverted;
    }

    getData(data) {
        return [data[0]].concat(data.slice(1).reverse());
    }
}
