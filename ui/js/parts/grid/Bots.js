import {BaseGrid} from './Base.js?v=2';
import {
    renderClickableBotId,
    renderDevice,
    renderOs,
    renderBoolean,
} from '../DataRenderers.js?v=2';

export class BotsGrid extends BaseGrid {
    get columnDefs() {
        const columnDefs = [
            {
                className: 'bot-id-col',
                targets: 0
            },
            {
                className: 'bot-type-col',
                targets: 1
            },
            {
                className: 'bot-os-col',
                targets: 2
            },
            {
                className: 'bot-modified-col',
                targets: 3
            },
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'id',
                render: (data, type, record) => {
                    return renderClickableBotId(record);
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
                data: 'modified',
                render: renderBoolean
            }
        ];

        return columns;
    }
}
