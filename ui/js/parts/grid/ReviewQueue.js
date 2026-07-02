import {BaseGrid} from './Base.js?v=0.10.0';
import {
    renderTime,
    renderDate,
    renderUserActionButtons,
    renderClickableImportantUserWithScore,
} from '../DataRenderers.js?v=0.10.0';

export class ReviewQueueGrid extends BaseGrid {
    get orderConfig() {
        return [[1, 'desc']];
    }

    onTableRowClick(event) {}

    onDateFilterChanged() {}

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
                className: 'review-queue-timestamp-col',
                targets: 2
            },
            {
                className: 'review-queue-date-col',
                targets: 3
            },
            {
                className: 'review-queue-button-col',
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
                data: 'added_to_review',
                render: renderTime
            },
            {
                data: 'lastseen',
                render: renderTime
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
                    record.addedToReview = record.added_to_review;

                    return renderUserActionButtons(record);
                },
            },
        ];

        return columns;
    }
}
