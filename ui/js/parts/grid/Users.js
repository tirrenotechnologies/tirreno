import {BaseGrid} from './Base.js?v=0.10.0';
import {
    renderClickableImportantUserWithScore,
    renderDate,
    renderUserId,
    renderUserReviewedStatus,
    renderTime,
} from '../DataRenderers.js?v=0.10.0';

export class UsersGrid extends BaseGrid {
    get orderConfig() {
        return [[4, 'desc']];
    }

    get columnDefs() {
        const columnDefs = [
            {
                className: 'user-user-col',
                targets: 0
            },
            {
                className: 'user-userid-col',
                targets: 1
            },
            {
                className: 'user-date-col',
                targets: 2
            },
            {
                className: 'user-timestamp-col',
                targets: 3
            },
            {
                className: 'user-status-col',
                targets: 4
            },
            {
                visible: false,
                targets: 5
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
                data: 'accounttitle',
                render: renderUserId
            },
            {
                data: 'created',
                render: renderDate,
            },
            {
                data: 'lastseen',
                render: renderTime,
            },
            {
                data: 'fraud',
                render: (data, type, record) => {
                    return renderUserReviewedStatus(record);
                },
            },
            {
                data: 'id',
                name: 'id',
            },
        ];

        return columns;
    }
}
