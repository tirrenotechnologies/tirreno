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

    paths(u, seriesIdx, idx0, idx1, extendGap, buildClip) {
        return uPlot.paths.bars({size: [0.6, 100]})(u, seriesIdx, idx0, idx1, extendGap, buildClip);
    }
}
