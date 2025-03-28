import {Loader} from '../Loader.js?v=2';
import {getQueryParams}  from '../utils/DataSource.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';
import {formatIntTimeUtc} from '../utils/Date.js?v=2';
import {MAX_HOURS_CHART, MIN_HOURS_CHART, X_AXIS_SERIFS} from '../utils/Constants.js?v=2';

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
        this.loaderBlock.classList.remove('is-hidden');
        this.loaderBlock.innerHTML = '<p class="text-loader"></p>';
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

        $.ajax({
            url: '/admin/loadChart',
            type: 'get',
            data: data,
            success: (responseData, status) => this.onChartLoaded(responseData, status, data['resolution']),
            error: handleAjaxError,
        });
    }

    onChartLoaded(data, status, resolution) {
        if ('success' == status) {
            if (this.chart) {
                this.chart.destroy();
            }
            this.chart = new uPlot(this.getOptions(resolution), data, this.chartBlock);

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

    xAxisResolutionShift(xAxis, resolution) {
        if (resolution === 'hour') {
            xAxis.scale = 'HOUR';
            xAxis.values = [
                // tick incr    default           year              month       day         hour     min        sec     mode
                [3600,          '{HH}:{mm}',     '\n{D}/{M}/{YY}',   null,    '\n{D}/{M}',   null,    null,      null,   1]
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
                // tick incr    default           year            month       day         hour     min        sec     mode
                [60,          '{HH}:{mm}',     '\n{D}/{M}/{YY}',   null,    '\n{D}/{M}',   null,    null,      null,   1]
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
        return document.querySelector('.statChart');
    }

    tooltipsPlugin(opts, resolution = 'day', defaultVal = '0') {
        let seriestt;

        function init(u, opts, data) {
            let over = u.over;

            let tt = document.createElement('div');
            tt.className = 'tooltipline';
            tt.textContent = '';
            tt.style.pointerEvents = 'none';
            tt.style.position = 'absolute';
            tt.style.background = 'rgba(0,0,0,0.5)';
            over.appendChild(tt);
            seriestt = tt;

            over.addEventListener('mouseleave', () => {
                if (!u.cursor._lock) {
                    tt.style.display = 'none';
                }
            });

            over.addEventListener('mouseenter', () => {
                const display = u.data.length > 1 ? null : 'none';

                tt.style.display = display;
            });

            if (u.cursor.left < 0)
                tt.style.display = 'none';
            else
                tt.style.display = null;
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

                let top;
                let html = [];

                if (u.data.length > 1) {
                    let s1 = u.series[1];
                    let yVal1 = u.data[1][idx];
                    yVal1 = (yVal1 !== null && yVal1 != undefined) ? yVal1 : defaultVal;
                    let color = u.series[1].stroke();
                    html.push(`<span style="border-radius: 3px; color:#131220; padding: 2px 3px; background: ${color}">${s1.label}: ${yVal1}</span>`);
                    top = u.valToPos(yVal1, s1.scale);
                }

                if (u.data.length > 2) {
                    let s2 = u.series[2];
                    let yVal2 = u.data[2][idx];
                    yVal2 = (yVal2 !== null && yVal2 != undefined) ? yVal2 : defaultVal;
                    let color = u.series[2].stroke();
                    html.push(`<span style="border-radius: 3px; color:#131220; padding: 2px 3px; background: ${color}">${s2.label}: ${yVal2}</span>`);
                }

                if (u.data.length > 3) {
                    let s3 = u.series[3];
                    let yVal3 = u.data[3][idx];
                    yVal3 = (yVal3 !== null && yVal3 != undefined) ? yVal3 : defaultVal;
                    let color = u.series[3].stroke();
                    html.push(`<span style="border-radius: 3px; color:#131220; padding: 2px 3px; background: ${color}">${s3.label}: ${yVal3}</span>`);
                }

                if (u.data.length > 4) {
                    let s4 = u.series[4];
                    let yVal4 = u.data[4][idx];
                    yVal4 = (yVal4 !== null && yVal4 != undefined) ? yVal4 : defaultVal;
                    let color = u.series[4].stroke();
                    html.push(`<span style="border-radius: 3px; color:#131220; padding: 2px 3px; background: ${color}">${s4.label}: ${yVal4}</span>`);
                }

                if (html.length) {
                    html.push(ts.replace(/\./g, '/'));
                    seriestt.innerHTML = html.join('<br>');
                    let left = u.valToPos(xVal, vtp);
                    seriestt.style.top = Math.round(top) + 'px';
                    seriestt.style.left = Math.round(left) + 'px';
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
                setCursor
            }
        };
    }
}
