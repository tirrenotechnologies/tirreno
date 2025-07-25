import {Loader} from '../Loader.js?v=2';
import {getQueryParams}  from '../utils/DataSource.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';
import {formatIntTimeUtc} from '../utils/Date.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';
import {renderChartTooltipPart} from '../DataRenderers.js?v=2';
import {
    MAX_HOURS_CHART,
    MIN_HOURS_CHART,
    X_AXIS_SERIFS,
} from '../utils/Constants.js?v=2';

export class BaseChart {
    constructor(chartParams) {
        this.config = chartParams;

        this.cursLeft = -10;
        this.cursTop = -10;
        this.cursorMemo = {
            set: (left, top) => {
                this.cursLeft = left;
                this.cursTop = top;
            },
            get: () => ({
                left: this.cursLeft,
                top: this.cursTop,
                y: false,
                drag: {
                    x: false,
                    y: false
                }
            })
        };

        this.loader = new Loader();

        const loaderDiv = document.createElement('div');
        loaderDiv.id = 'loader';
        this.chartBlock.appendChild(loaderDiv);

        this.chart = null;
        this.reloadData();

        const onDateFilterChanged = this.onDateFilterChanged.bind(this);
        window.addEventListener('dateFilterChanged', onDateFilterChanged, false);
    }

    onDateFilterChanged() {
        this.reloadData();
    }

    stopAnimation() {
        this.loaderBlock.classList.add('is-hidden');
        this.loader.stop();
    }

    updateTimer() {
        const el = document.createElement('p');
        el.className = 'text-loader';

        this.loaderBlock.classList.remove('is-hidden');
        this.loaderBlock.replaceChildren(el);

        const p = this.loaderBlock.querySelector('p');

        this.loader.start(p);
    }

    reloadData() {
        this.updateTimer();

        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;
        const params = this.config.getParams();
        const data   = getQueryParams(params);

        data['mode']        = params.mode;
        data['type']        = params.chartType;
        data['token']       = token;
        data['resolution']  = 'day';
        if (data['dateFrom']) {
            const diff = new Date(data['dateTo']) - new Date(data['dateFrom']);
            const hours = diff/(60 * 60 * 1000);
            if (hours <= MAX_HOURS_CHART && hours > MIN_HOURS_CHART) {
                data['resolution'] = 'hour';
            } else if (hours <= MIN_HOURS_CHART) {
                data['resolution'] = 'minute';
            }
        }

        fireEvent('dateFilterChangedCaught');

        $.ajax({
            url: '/admin/loadChart',
            type: 'get',
            data: data,
            success: (responseData, status) => this.onChartLoaded(responseData, status, data['resolution']),
            error: handleAjaxError,
            complete: function() {
                fireEvent('dateFilterChangedCompleted');
            },
        });
    }

    onChartLoaded(data, status, resolution) {
        if ('success' == status) {
            if (this.chart) {
                this.chart.destroy();
            }
            const prepData = this.getData(data);
            this.chart = new uPlot(this.getOptions(resolution), prepData, this.chartBlock);

            this.stopAnimation();
        }
    }

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

        return series;
    }

    getDaySeries() {
        return {
            label: 'Day',
            scale: 'DAY',
            value: '{YYYY}-{MM}-{DD}'
        };
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
                // tick incr     default          year        month    day        hour     min        sec       mode
                [3600 * 24,     '{DD}/{MM}',  '\n{YYYY}',     null,    null,      null,    null,      null,        1],
            ],
        };
        const yAxis = {
            stroke: '#8180a0',
            values: (u, vals, space) => vals.map(v => this.formatKiloValue(u, v)),
            grid: {
                width: 1 / devicePixelRatio,
                stroke: '#2b2a3d',
            },
            ticks: {
                width: 1 / devicePixelRatio,
                stroke: '#2b2a3d',
            },
        };

        return {
            x: xAxis,
            y: yAxis,
        };
    }

    getData(data) {
        return data;
    }

    getOptions(resolution = 'day', nullChar = '0') {
        const tooltipsPlugin = this.tooltipsPlugin({cursorMemo: this.cursorMemo}, resolution, nullChar);
        const axes = this.getAxisConfig();
        const series = this.seriesResolutionShift(this.getSeries(), resolution);
        const xAxis = this.xAxisResolutionShift(axes.x, resolution);
        const yAxis = axes.y;

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
                yAxis,
            ]
        };

        return opts;
    }

    xAxisResolutionShift(xAxis, resolution) {
        if (resolution === 'hour') {
            xAxis.scale = 'HOUR';
            xAxis.values = [
                // tick incr default                year         month         day         hour     min        sec     mode
                [3600,      '{HH}:{mm}', '\n{DD}/{MM}/{YYYY}',   null,    '\n{DD}/{MM}',   null,    null,      null,   1]
            ];
            xAxis.space = function(self, axisIdx, scaleMin, scaleMax, plotDim) {
                let rangeHours   = (scaleMax - scaleMin) / 3600;
                if (rangeHours > X_AXIS_SERIFS) rangeHours = X_AXIS_SERIFS;
                const pxPerHour = plotDim / rangeHours;

                return pxPerHour;
            };
        } else if (resolution === 'minute') {
            xAxis.scale = 'MINUTE';
            xAxis.values = [
                // tick incr default              year           month         day         hour     min        sec     mode
                [60,        '{HH}:{mm}', '\n{DD}/{MM}/{YYYY}',   null,    '\n{DD}/{MM}',   null,    null,      null,   1]
            ];
            xAxis.space = function(self, axisIdx, scaleMin, scaleMax, plotDim) {
                let rangeMinutes   = (scaleMax - scaleMin) / 60;
                if (rangeMinutes > X_AXIS_SERIFS) rangeMinutes = X_AXIS_SERIFS;
                const pxPerMinute = plotDim / rangeMinutes;

                return pxPerMinute;
            };
        }

        return xAxis;
    }

    formatKiloValue(u, value) {
        if (value === 0) {
            return value;
        }
        if (value % 1000000 === 0) {
            return Math.round(value / 1000000) + 'M';
        }
        if (value % 1000 === 0) {
            return Math.round(value / 1000) + 'k';
        }
        return value;
    }

    get loaderBlock() {
        return document.getElementById('loader');
    }

    get chartBlock() {
        return document.querySelector('.stat-chart');
    }

    tooltipsPlugin(opts, resolution = 'day', defaultVal = '0') {
        let self = this;
        let seriestt;

        function init(u, opts, data) {
            let over = u.over;

            let tt = document.createElement('div');
            tt.className = 'tooltipline';
            tt.textContent = '';
            tt.style.pointerEvents = 'none';
            tt.style.position = 'absolute';
            tt.style.background = 'rgba(0,0,0,1)';
            over.appendChild(tt);
            seriestt = tt;

            over.addEventListener('mouseleave', () => {
                if (!u.cursor._lock) {
                    tt.style.display = 'none';
                }
            });

            over.addEventListener('mouseenter', () => {
                tt.style.display = u.data.length > 1 ? null : 'none';
            });

            tt.style.display = (u.cursor.left < 0) ? 'none' : null;
        }

        function setCursor(u) {
            const {left, idx} = u.cursor;

            if (opts && opts.cursorMemo) {
                opts.cursorMemo.set(left, top);
            }

            if (left >= 0) {
                let xVal = u.data[0][idx];

                const vtp = (resolution === 'day') ? 'DAY' : ((resolution === 'hour') ? 'HOUR' : 'MINUTE');
                let ts = '';

                if (Number.isInteger(xVal)) {
                    const useTime = resolution === 'hour' || resolution === 'minute';
                    ts = formatIntTimeUtc(xVal * 1000, useTime);
                }

                let frag = document.createDocumentFragment();
                frag.appendChild(document.createTextNode(ts.replace(/\./g, '/')));

                for (let i = 1; i <= 4; i++) {
                    frag = self.extendTooltipFragment(i, idx, u.data, defaultVal, u, frag);
                }

                if (frag.children.length > 1) {
                    seriestt.replaceChildren(frag);

                    let val = null;
                    let lvl = 1;

                    const lim = Math.min(u.data.length - 1, 4);

                    for (let i = 1; i <= lim; i++) {
                        if (u.data[i][idx] > val) {
                            val = u.data[i][idx];
                            lvl = i;
                        }
                    }

                    val = (val !== null && val != undefined) ? val : defaultVal;

                    seriestt.style.top = Math.round(u.valToPos(val, u.series[lvl].scale)) + 'px';
                    //seriestt.style.top = Math.round(self.getTop(idx, u.data, defaultVal, u)) + 'px';
                    seriestt.style.left = Math.round(u.valToPos(xVal, vtp)) + 'px';
                    seriestt.style.display = null;
                } else {
                    seriestt.style.display = 'none';
                }
            } else {
                seriestt.style.display = 'none';
            }
        }

        return {
            hooks: {
                init,
                setCursor,
            },
        };
    }

    extendTooltipFragment(lvl, idx, data, defaultVal, u, frag) {
        if (data.length > lvl) {
            let series = u.series[lvl];
            let val = (idx !== null) ? data[lvl][idx] : data[lvl];
            val = (val !== null && val != undefined) ? val : defaultVal;

            frag.appendChild(document.createElement('br'));
            frag.appendChild(renderChartTooltipPart(series.stroke(), series.label, val));
        }

        return frag;
    }
}
