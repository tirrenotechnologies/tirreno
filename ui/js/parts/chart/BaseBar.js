import {BaseChart}  from './BaseChart.js?v=2';
import {COLOR_GREEN} from '../utils/Constants.js?v=2';

export class BaseBarChart extends BaseChart {
    getSeries() {
        const series = [
            {
                label: 'Day',
                scale: 'DAY',
                value: '{YYYY}-{MM}-{DD}'
            },
            {
                label:      'Total events',
                //width:      -1 / devicePixelRatio,  // negative for thick overlapping bars
                width:      -1,
                drawStyle:  1,                      // bars
                stroke:     COLOR_GREEN,
                fill:       COLOR_GREEN,
                paths:      this.paths,
            },
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
        };

        return xAxis;
    }

    getOptions(resolution = 'day') {
        const tooltipsPlugin = this.tooltipsPlugin({cursorMemo: this.cursorMemo}, resolution, '0');
        const xAxis = this.xAxisResolutionShift(this.getAxisConfig(), resolution);
        const series = this.seriesResolutionShift(this.getSeries(), resolution);

        const opts = {
            width: 995,
            height: 200,

            tzDate: ts => uPlot.tzDate(new Date(ts * 1000), 'Etc/UTC'),
            series: series,

            legend: {
                show: false
            },
            cursor: this.cursorMemo.get(),
            plugins: [tooltipsPlugin],
            scales: {
                x: {time: false},
            },
            axes: [
                xAxis,
                {
                    stroke: '#8180a0',
                    values: (u, vals, space) => vals.map(v => this.formatKiloValue(u, v)),
                    grid: {
                        width: 1 / devicePixelRatio,
                        stroke: '#2b2a3d',
                    },
                    ticks: {
                        width: 1 / devicePixelRatio,
                        stroke: '#2b2a3d',
                    }
                },
            ]
        };

        return opts;
    }

    paths(u, seriesIdx, idx0, idx1, extendGap, buildClip) {
        return uPlot.paths.bars({size: [0.6, 100]})(u, seriesIdx, idx0, idx1, extendGap, buildClip);
    }
}
