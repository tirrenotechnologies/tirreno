import {BaseGrid} from './Base.js?v=2';
import {
    renderClickableImportantUserWithScore,
    renderDate,
    renderUserFirstname,
    renderUserId,
    renderUserLastname,
    renderUserReviewedStatus,
    renderTime,
} from '../DataRenderers.js?v=2';


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
                className: 'user-name-col',
                targets: 2
            },
            {
                className: 'user-name-col',
                targets: 3
            },
            {
                className: 'user-date-col',
                targets: 4
            },
            {
                className: 'user-timestamp-col',
                targets: 5
            },
            {
                className: 'user-status-col',
                targets: 6
            },
            {
                visible: false,
                targets: 7
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
                data: 'firstname',
                render: (data, type, record) => {
                    return renderUserFirstname(record);
                },
            },
            {
                data: 'lastname',
                render: (data, type, record) => {
                    return renderUserLastname(record);
                },
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
