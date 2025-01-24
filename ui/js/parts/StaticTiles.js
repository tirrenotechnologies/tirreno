import {Loader} from './Loader.js?v=2';

export class StaticTiles {

    constructor(tilesParams) {
        const me = this;
        this.config = tilesParams;
        this.loaders = {};

        this.config.elems.forEach(elem => {
            me.loaders[elem] = new Loader();
        });

        const onDateFilterChanged = this.onDateFilterChanged.bind(this);
        window.addEventListener('dateFilterChanged', onDateFilterChanged, false);

        this.runLoaders();
    }

    onDateFilterChanged() {
        this.runLoaders();
    }

    runLoaders() {
        for (const property in this.loaders) {
            const el = document.querySelector(`.${property} .title`);
            this.loaders[property].start(el);
        }
    }
}
