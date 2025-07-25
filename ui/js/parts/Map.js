import {TotalTile} from './TotalTile.js?v=2';
import {getQueryParams} from './utils/DataSource.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';
import {fireEvent} from './utils/Event.js?v=2';

export class Map {

    constructor(mapParams) {
        this.config = mapParams;

        this.totalTile = new TotalTile();

        this.regions = {};

        const onRegionTipShow = this.onRegionTipShow.bind(this);
        const onRegionClick = this.onRegionClick.bind(this);

        $('#world-map-markers').vectorMap({
            map: 'world_mill_en',

            normalizeFunction: 'polynomial',
            hoverOpacity: 0.7,
            regionsSelectable: false,
            markersSelectable: false,
            zoomOnScroll: false,
            hoverColor: false,

            series: {
                regions: [
                    {
                        values: {},
                        scale: ['#4e6964', '#01EE99'],
                        normalizeFunction: 'polynomial'
                    }
                ]
            },

            regionStyle: {
                initial: {
                    fill: '#575678'
                },
                selected: {
                    fill: '#01EE99'
                }
            },

            onRegionTipShow: function(e, el, code) {
                onRegionTipShow(el, code);
            },
            onRegionClick: function(e, code) {
                onRegionClick(code);
            },

            backgroundColor: '#131220'
        });

        const onDateFilterChanged = this.onDateFilterChanged.bind(this);
        window.addEventListener('dateFilterChanged', onDateFilterChanged, false);

        this.loadData();
    }

    onRegionTipShow(tipEl, value) {
        const regionValue = this.mapObject.series.regions[0].values[value];
        const phrase      = this.getTooltipString(regionValue);

        tipEl.html(`${tipEl.html()} - ${phrase}`);
    }

    onRegionClick(value) {
        if (this.regions[value] !== undefined && this.regions[value][this.config.tooltipField] > 0) {
            const url = `/country/${this.regions[value].id}`;
            if (event.ctrlKey || event.metaKey) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }
    }

    getCountriesRegionsFromResponse(records) {
        const me = this;
        const regions = {};

        this.regions = {};

        records.forEach(rec => {
            const country = rec.iso;
            if (!regions[country]) {
                regions[country] = 0;
                this.regions[country] = 0;
            }

            const value = me.getRegionValue(rec);
            regions[country] = value;
            this.regions[country] = {
                [this.config.tooltipField]: value,
                id: rec.id,
            };
        });

        return regions;
    }

    getRegionValue(record) {
        const field = this.config.tooltipField;
        const value = record[field];

        return value;
    }

    selectRegions(regions) {
        const map  = this.mapObject;

        //Remove countries which does not exist in the vectormap: MU, BH, etc...
        for (const [key, value] of Object.entries(regions)) {
            if (!map.regions.hasOwnProperty(key)) {
                delete regions[key];
            }
        }

        //https://github.com/bjornd/jvectormap/issues/376
        map.series.regions[0].params.min = undefined;
        map.series.regions[0].params.max = undefined;

        map.series.regions[0].clear();
        map.series.regions[0].setValues(regions);
    }

    onDateFilterChanged() {
        this.loadData();
    }

    loadData() {
        const me     = this;
        const params = this.config.getParams();
        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        const data = getQueryParams(params);

        fireEvent('dateFilterChangedCaught');

        $.ajax({
            type: 'get',
            url: `/admin/loadMap?token=${token}`,
            data: data,
            scope: me,
            success: me.onCountriesListLoaded,
            error: handleAjaxError,
            complete: function() {
                fireEvent('dateFilterChangedCompleted');
            },
        });
    }

    onCountriesListLoaded(data, status) {
        if ('success' == status) {
            const me = this.scope;

            const tileId  = 'totalCountries';
            const tableId = 'countries-table';

            me.totalTile.update(tableId, tileId, data.length);

            const regions = me.getCountriesRegionsFromResponse(data);

            me.selectRegions(regions);
        }
    }

    getTooltipString(value) {
        value = value ? value : 0;

        let string = this.config.tooltipString;
        if (1 !== value) {
            string += 's';
        }

        const tooltipPhrase = `${value} ${string}`;

        return tooltipPhrase;
    }

    get mapObject() {
        return $('#world-map-markers').vectorMap('get', 'mapObject');
    }
}
