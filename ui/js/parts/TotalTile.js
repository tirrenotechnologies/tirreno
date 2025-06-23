import {
    USER_IPS_CRITICAL_VALUE,
    USER_EVENTS_CRITICAL_VALUE,
    USER_DEVICES_CRITICAL_VALUE,
    USER_COUNTRIES_CRITICAL_VALUE,
} from './utils/Constants.js?v=2';

export class TotalTile {

    constructor() {
        this.criticalValues = {
            totalIps:       USER_IPS_CRITICAL_VALUE,
            totalEvents:    USER_EVENTS_CRITICAL_VALUE,
            totalDevices:   USER_DEVICES_CRITICAL_VALUE,
            totalCountries: USER_COUNTRIES_CRITICAL_VALUE
        };
    }

    update(tableId, tileId, value) {
        const tileCls = this.getTileClass(tableId, tileId, value);
        const path    = `.${tileId} .title`;
        const el      = document.querySelector(path);

        if (el) {
            el.classList.add('loaded');
            el.classList.remove('loading');

            //Remove previous class if exists
            el.classList.remove('low');
            el.classList.remove('medium');
            el.classList.remove('high');

            //Add new color class
            el.classList.add(tileCls);

            el.innerHTML = value;
        }
    }

    getTileClass(tableId, tileId, value) {
        const litmus = this.criticalValues[tileId];

        const USER_ID     = parseInt(window.location.pathname.replace('/id/', ''), 10);
        const isUserPage  = () => !isNaN(USER_ID);

        if (!litmus || !isUserPage()) return;

        let cls = null;

        if (value >= litmus) {
            cls = 'medium';
        }

        return cls;
    }
}
