import {Loader} from '../Loader.js?v=2';
import {Tooltip} from '../Tooltip.js?v=2';
import {handleAjaxError} from '../utils/ErrorHandler.js?v=2';

export class BaseTiles {

    constructor(tilesParams) {
        const me = this;
        this.config = tilesParams;
        this.loaders = {};

        this.elems.forEach(elem => {
            me.loaders[elem] = new Loader();
        });

        this.loadData();
    }

    loadData() {
        const me     = this;
        const url    = this.url;
        const params = this.config.getParams();
        const token  = document.head.querySelector('[name=\'csrf-token\'][content]').content;

        this.startLoaders();

        $.ajax({
            url: `${url}?token=${token}`,
            type: 'get',
            scope: me,
            data: params,
            success: function(response) {
                me.onLoad(response, 'success');
                me.initTooltips();
            },
            error: handleAjaxError,
        });
    }

    stopLoaders() {
        for (const property in this.loaders) {
            this.loaders[property].stop();
        }
    }

    startLoaders() {
        for (const property in this.loaders) {
            const el = document.querySelector(`#${property}`);
            this.loaders[property].start(el);
        }
    }

    onLoad(data, status) {
        if ('success' == status) {
            this.stopLoaders();
            this.updateTiles(data);
        }
    }

    initTooltips() {
        Tooltip.addTooltipsToTiles();
    }

    updateTiles(data) {}
}
