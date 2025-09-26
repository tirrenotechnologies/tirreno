import {Loader} from '../Loader.js?v=2';
import {BaseChart}  from './BaseChart.js?v=2';
import {
    COLOR_LIGHT_GREEN,
    COLOR_GREEN,
    X_AXIS_SERIFS,
} from '../utils/Constants.js?v=2';

export class BaseSparklineChart extends BaseChart {
    constructor(chartParams) {
        super(chartParams);

        this.charts = null;

        if (!this.loaders) {
            this.loaders = [];
            this.elems.forEach(el => {this.loaders[el] = new Loader();});
        }
    }

    getOptions() {
        const tooltipsPlugin = this.tooltipsPlugin({cursorMemo: this.cursorMemo}, 'day', '0');
        return {
            width: 200,
            height: 30,
            pxAlign: false,
            cursor: {
                show: false
            },
            select: {
                show: false,
            },
            legend: {
                show: false,
            },
            scales: {
                x: {time: false},
            },
            axes: [
                {show: false},
                {show: false}
            ],
            cursor: this.cursorMemo.get(),
            plugins: [tooltipsPlugin],
            series: [
                {
                    label: 'Day',
                    scale: 'DAY',
                    value: '{YYYY}-{MM}-{DD}',
                    stroke: '#8180a0',
                },
                {
                    label: 'This week',
                    stroke: COLOR_GREEN,
                    fill: COLOR_LIGHT_GREEN,
                    points: {show: false}
                },
                {
                    label: 'Previous week',
                    stroke: 'rgba(129,128,160,0.7)',
                    fill: 'rgba(129,128,160,0.03)',
                    points: {show: false}
                },
            ],
        };
    }

    onChartLoaded(data, status, resolution) {
        if ('success' == status) {
            data = this.getData(data);

            this.stopLoader();

            this.charts = [];

            this.elems.forEach(el => {
                const lines = [data.time, data[el], data[el + 'Prev']];
                this.charts.push(new uPlot(this.getOptions(), lines, this.getChartBlock(el)));
            });
        }
    }

    startLoader() {
        if (!this.loaders) {
            this.loaders = [];
            this.elems.forEach(el => this.loaders[el] = new Loader());
        }

        this.elems.forEach(name => {
            const el = document.createElement('p');
            const block = this.getChartBlock(name);

            block.classList.remove('is-hidden');
            block.replaceChildren(el);

            const p = block.querySelector('p');

            this.loaders[name].start(p);
        });
    }

    stopLoader() {
        this.elems.forEach(el => {
            this.loaders[el].stop();
            this.getChartBlock(el).querySelector('p').classList.add('is-hidden');
        });
    }

    getData(data) {
        return {
            'time':                 data[0],
            'totalDevices':         data[1],
            'totalIps':             data[2],
            'totalSessions':        data[3],
            'totalEvents':          data[4],
            'totalDevicesPrev':     data[5],
            'totalIpsPrev':         data[6],
            'totalSessionsPrev':    data[7],
            'totalEventsPrev':      data[8],
        };
    }

    getChartBlock(cls) {
        return document.querySelector(`td.${cls} p.session-stat`);
    }

    get chartBlocks() {
        const result = {};
        this.elems.forEach(el => {result[el] = this.getChartBlock(el);});

        return result;
    }

    get elems() {
        return ['totalDevices', 'totalIps', 'totalSessions', 'totalEvents'];
    }
}
