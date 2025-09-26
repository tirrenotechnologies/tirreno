import {BaseGrid} from './Base.js?v=2';
import {
    renderTime,
    renderBlacklistButtons,
    renderBlacklistItem,
    renderBlacklistType,
    renderClickableImportantUserWithScore,
} from '../DataRenderers.js?v=2';

export class BlacklistGrid extends BaseGrid {
    get orderConfig() {
        return [[1, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'blacklist-user-col',
                targets: 0
            },
            {
                className: 'blacklist-timestamp-col',
                targets: 1
            },
            {
                className: 'blacklist-type-col',
                targets: 2
            },
            {
                className: 'blacklist-value-col',
                targets: 3
            },
            {
                className: 'blacklist-button-col',
                targets: 4
            }
        ];

        return columnDefs;
    }

    get columns() {
        const columns = [
            {
                data: 'score',
                render: (data, type, record) => {
                    return renderClickableImportantUserWithScore(record, 'medium');
                }
            },
            {
                data: 'created',
                render: (data, type, record) => {
                    return renderTime(data);
                }
            },
            {
                data: 'type',
                render: (data, type, record) => {
                    return renderBlacklistType(record);
                }
            },
            {
                data: 'value',
                render: (data, _ype, record) => {
                    return renderBlacklistItem(record);
                }
            },
            {
                data: 'entity_id',
                orderable: false,
                render: (data, type, record) => {
                    return renderBlacklistButtons(record);
                }
            }
        ];

        return columns;
    }
}
