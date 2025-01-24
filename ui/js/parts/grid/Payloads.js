import { BaseGrid } from './Base.js?v=2';

export class PayloadsGrid extends BaseGrid {

    get orderConfig() {
        return [];
    }

    get columnDefs() {
        const columnDefs = [];

        this.payloadsColumns.forEach((column, index) => {
            columnDefs.push({
                className: `${column.innerHTML}-col`,
                targets: index
            });
        });

        return columnDefs;
    }

    get columns() {
        const columns = [];

        this.payloadsColumns.forEach(column => {
            columns.push({data: column.innerHTML});
        });

        return columns;
    }

    get payloadsColumns() {
        return document.querySelectorAll('#payloads-table th');
    }
}
