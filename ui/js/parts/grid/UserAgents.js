import {BaseGrid} from './Base.js?v=0.10.0';
import {
    renderClickableUserAgentId,
    renderDevice,
    renderOs,
    renderBrowser,
    renderBoolean,
} from '../DataRenderers.js?v=0.10.0';

export class UserAgentsGrid extends BaseGrid {
    get orderConfig() {
        return [[4, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'user-agent-id-col',
                targets: 0
            },
            {
                className: 'user-agent-type-col',
                targets: 1
            },
            {
                className: 'user-agent-os-col',
                targets: 2
            },
            {
                className: 'user-agent-browser-col',
                targets: 3
            },
            {
                className: 'user-agent-modified-col',
                targets: 4
            },
            {
                className: 'user-agent-cnt-col',
                targets: 5
            },
            {
                visible: false,
                targets: 6
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'id',
                render: (data, type, record) => {
                    return renderClickableUserAgentId(record);
                }
            },
            {
                data: 'device',
                render: (data, type, record) => {
                    return renderDevice(record);
                }
            },
            {
                data: 'os_name',
                render: (data, type, record) => {
                    return renderOs(record);
                }
            },
            {
                data: 'browser_name',
                render: (data, type, record) => {
                    return renderBrowser(record);
                }
            },
            {
                data: 'modified',
                render: renderBoolean
            },
            {
                data: 'total_account',
                name: 'total_account',
                orderable: false,
                render: this.renderTotalsLoader
            },
            {
                data: 'id',
                name: 'id',
            }

        ];

        return columns;
    }
}
