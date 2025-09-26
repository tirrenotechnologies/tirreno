import {SearchLine} from '../parts/SearchLine.js';
import {Tooltip} from '../parts/Tooltip.js?v=2';

export class BasePage {
    constructor(name, single = false) {
        this.initCommonUi();

        if (name) {
            this.name = name;
            if (single) {
                let path = (this.name !== 'user') ? this.name : 'id';
                this.id = parseInt(window.location.pathname.replace('/' + path + '/', ''), 10);
                const key = this.name + 'Id';
                this.getParams = () => {
                    return {[key]: this.id};
                };
            }
        }
    }

    initCommonUi() {
        new SearchLine();

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
        const notification = event.target.closest('.notification.system');
        if (notification) {
            notification.remove();
        }
    }

    getDevicesGridParams() {
        return {
            url:        '/admin/loadDevices',
            tileId:     'totalDevices',
            tableId:    'devices-table',
            panelType:  'device',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getIpsGridParams() {
        return {
            url:        '/admin/loadIps',
            tileId:     'totalIps',
            tableId:    'ips-table',

            isSortable:         false,
            orderByLastseen:    true,

            getParams:  this.getParams,
        };
    }

    getEventsGridParams() {
        return {
            url:        '/admin/loadEvents',
            tileId:     'totalEvents',
            tableId:    'user-events-table',
            panelType:  'event',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getUsersGridParams() {
        return {
            url:        '/admin/loadUsers',
            tileId:     'totalUsers',
            tableId:    'users-table',

            isSortable: false,

            getParams:  this.getParams,
        };
    }

    getIspsGridParams() {
        return {
            url:        '/admin/loadIsps',
            tableId:    'isps-table',

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
            getParams: () => ({
                mode:   this.name,
                id:     this.id,
            }),
        };
    }

    getChartParams(datesFilter, searchFilter) {
        return {
            getParams: () => {
                const mode        = this.name;
                const dateRange   = datesFilter.getValue();
                const searchValue = searchFilter.getValue();

                return {mode, dateRange, searchValue};
            },
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
