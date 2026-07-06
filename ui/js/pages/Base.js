import {SearchLine} from '../parts/SearchLine.js?v=0.10.0';
import {Tooltip} from '../parts/Tooltip.js?v=0.10.0';
import {Constants} from '../parts/utils/Constants.js?v=0.10.0';
import {
    closest,
    mapKeys,
} from '../parts/utils/Functions.js?v=0.10.0';

export class BasePage {
    constructor(name, single = false) {
        this.initCommonUi();

        if (name) {
            this.name = name;
            if (single) {
                let parts = window.location.pathname.split('/');
                this.id = parseInt(parts[parts.length -1], 10);
                const key = this.name + 'Id';
                this.getParams = () => {
                    return {[key]: this.id};
                };
            } else {
                this.filters = {};
                this.getParamsSection = () => {
                    let result = {};

                    const filterKeys = mapKeys(this.filters);
                    for (let i = 0; i < filterKeys.length; i++) {
                        const key = filterKeys[i];
                        result[key] = this.filters[key].getValue();
                    }

                    return result;
                };
            }
        }

        const initUi = this.initUi.bind(this);
        window.addEventListener('constantsLoaded', initUi, false);
    }

    initCommonUi() {
        new SearchLine();

        Constants.init();

        document.addEventListener('keyup', e => {
            if (e.key !== '/' || e.ctrlKey || e.metaKey) return;
            if (/^(?:input|textarea|select|button)$/i.test(e.target.tagName)) return;

            e.preventDefault();
            document.getElementById('auto-complete').focus();
        });

        const initTooltip = this.initTooltip;
        if (initTooltip) {
            Tooltip.init();
        }

        const closeNotificationButtons = this.closeNotificationButtons;
        if (closeNotificationButtons && closeNotificationButtons.length) {
            const onCloseNotificationButtonClick = this.onCloseNotificationButtonClick.bind(this);
            closeNotificationButtons.forEach(
                button => button.addEventListener('click', onCloseNotificationButtonClick, false)
            );
        }

        this.procedureNotifications.forEach(notification => {
            const btn = notification.querySelector('.delete');
            if (!btn) return;
            btn.addEventListener('click', () => {notification.remove();}, false);
        });
    }

    onCloseNotificationButtonClick() {
        const notification = closest(event.target, '.notification.system');
        if (notification) {
            notification.remove();
        }
    }

    setBaseFilters(datesFilter, searchFilter) {
        this.filters = {
            dateRange:      datesFilter,
            searchValue:    searchFilter,
        };
    }

    getDevicesGridParams() {
        return {
            url:        `${window.app_base}/loadDevices`,
            tileId:     'totalDevices',
            tableId:    'devices-table',
            panelType:  'device',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getIpsGridParams() {
        return {
            url:        `${window.app_base}/loadIps`,
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable:         false,
            orderByLastseen:    true,

            getParams:  this.getParams,
        };
    }

    getEventsGridParams() {
        return {
            url:        `${window.app_base}/loadEvents`,
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getUsersGridParams() {
        return {
            url:        `${window.app_base}/loadUsers`,
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getIspsGridParams() {
        return {
            url:        `${window.app_base}/loadIsps`,
            tileId:     'totalIsps',
            tableId:    'isps-table',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getFieldAuditTrailParams() {
        return {
            url:        `${window.app_base}/loadFieldAuditTrail`,
            tileId:     'totalEdits',
            tableId:    'field-audit-trail-table',
            panelType:  'field',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getMapParams() {
        return {
            getParams:      this.getParams,
            tooltipString:  'event',
            tooltipField:   'total_visit',
            tileId:         'totalCountries',
        };
    }

    getBarChartParams() {
        return {
            url:    `${window.app_base}/loadEventsChart`,
            mode:   this.name,
            id:     this.id,
        };
    }

    getSelfDetails() {
        return {
            getParams: this.getParams,
        };
    }

    get initTooltip() {
        return true;
    }

    get closeNotificationButtons() {
        return document.querySelectorAll('.notification.system:not(.is-hidden) .delete');
    }

    get procedureNotifications() {
        return document.querySelectorAll('#success-procedure-notification, #error-procedure-notification');
    }
}
