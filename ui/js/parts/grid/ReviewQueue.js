import {BaseGrid} from './Base.js?v=2';
import {
    renderTime,
    renderDate,
    renderUserFirstname,
    renderUserLastname,
    renderUserActionButtons,
    renderClickableImportantUserWithScore,
} from '../DataRenderers.js?v=2';

export class ReviewQueueGrid extends BaseGrid {
    get orderConfig() {
        return [[1, 'desc']];
    }

    onTableRowClick(event) {}

    get columnDefs() {
        const columnDefs = [
            {
                className: 'review-queue-user-col',
                targets: 0
            },
            {
                className: 'review-queue-timestamp-col',
                targets: 1
            },
            {
                className: 'review-queue-name-col',
                targets: 2
            },
            {
                className: 'review-queue-name-col',
                targets: 3
            },
            {
                className: 'review-queue-date-col',
                targets: 4
            },
            {
                className: 'review-queue-button-col',
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
                data: 'added_to_review',
                render: renderTime
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
                render: (data, type, record) => {
                    return renderDate(data);
                },
            },
            {
                orderable: false,
                data: 'actions',
                render: (data, type, record) => {
                    return renderUserActionButtons(record);
                },
            },
        ];

        return columns;
    }
}
