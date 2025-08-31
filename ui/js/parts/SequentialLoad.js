import {fireEvent} from './utils/Event.js?v=2';

export class SequentialLoad {
    constructor(data, eventName = 'dateFilterChangedCompleted') {
        this.objects = [];
        this.eventName = eventName;

        for (let i = 0; i < data.length; i++) {
            data[i][1].sequential = true;
            this.objects.push(new (data[i][0])(data[i][1]));
        }

        const me = this;

        $(document).ready(() => {
            me.startLoaders();

            let i = 0;

            const onReady = () => {
                if (i >= me.objects.length) {
                    window.removeEventListener(eventName, onReady);
                    fireEvent('sequentialLoadCompleted');

                    return;
                }

                me.objects[i].loadData();
                i++;
            };

            window.addEventListener(eventName, onReady);

            onReady();
        });

        // catching filters change
        const onFilterChanged = this.onFilterChanged.bind(this);
        window.addEventListener('searchFilterChanged', onFilterChanged, false);
        window.addEventListener('dateFilterChanged', onFilterChanged, false);
    }

    onFilterChanged() {
        this.startLoaders();

        let i = 0;

        const onLoad = () => {
            if (i >= this.objects.length) {
                fireEvent('sequentialLoadCompleted');
                window.removeEventListener(this.eventName, onLoad);

                return;
            }

            this.objects[i].loadData();
            i++;
        };

        window.addEventListener(this.eventName, onLoad);

        onLoad();
    }

    startLoaders() {
        this.objects.forEach(item => {
            item.startLoader();
        });
    }
};
