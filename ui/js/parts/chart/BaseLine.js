import {BaseChart}  from './BaseChart.js?v=2';
import {COLOR_GREEN, COLOR_LIGHT_GREEN, X_AXIS_SERIFS} from '../utils/Constants.js?v=2';

export class BaseLineChart extends BaseChart {
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
            }
        ];

        return series;
    }

    getAxisConfig() {
        const xAxis = {
            scale: 'DAY',
            stroke: '#8180a0',
            grid: {
                width: 1 / devicePixelRatio,
                stroke: '#2b2a3d',
            },
            ticks: {
                width: 1 / devicePixelRatio,
                stroke: '#2b2a3d',
            },
            values: [
                //Copied from https://github.com/leeoniya/uPlot/tree/master/docs#axis--grid-opts
                // tick incr          default           year                           month    day                        hour     min                sec       mode
                [3600 * 24,         '{D}/{M}',        '\n{YYYY}',                      null,    null,                      null,    null,              null,        1]
            ],
            space: function(self, axisIdx, scaleMin, scaleMax, plotDim) {
                let rangeDays   = (scaleMax - scaleMin) / 86400;
                if (rangeDays > X_AXIS_SERIFS) rangeDays = X_AXIS_SERIFS;
                const pxPerDay = plotDim / rangeDays;

                return pxPerDay;
            }
        };

        return xAxis;
    }

    getOptions(resolution = 'day') {
        const tooltipsPlugin = this.tooltipsPlugin({cursorMemo: this.cursorMemo}, resolution, '—');
        const xAxis = this.xAxisResolutionShift(this.getAxisConfig(), resolution);
        const series = this.seriesResolutionShift(this.getSeries(), resolution);
        const opts = {
            width: 995,
            height: 200,

            tzDate: ts => uPlot.tzDate(new Date(ts * 1e3), 'Etc/UTC'),
            series: series,

            legend: {show: false},
            cursor: this.cursorMemo.get(),
            plugins: [tooltipsPlugin],
            scales: {
                x: {time: false},
            },
            axes: [
                xAxis,
                {
                    scale: 'EVENTS',
                    side: 3,
                    values: (u, vals, space) => vals.map(v => this.formatKiloValue(u, v)),
                    grid: {
                        width: 1 / devicePixelRatio,
                        stroke: '#2b2a3d',
                    },
                    ticks: {
                        width: 1 / devicePixelRatio,
                        stroke: '#2b2a3d',
                    },
                    stroke: '#8180a0',
                    split: u => [
                        u.series[1].min,
                        u.series[1].max,
                    ]
                }
            ]
        };

        return opts;
    }
}
