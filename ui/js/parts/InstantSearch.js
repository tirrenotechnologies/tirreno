import {
    closest,
    inArray,
} from './utils/Functions.js?v=0.10.0';

export class InstantSearch {
    constructor(tableId, skipCols = []) {
        this.tableId = tableId;
        this.skipCols = skipCols;

        const searchTable = this.searchTable.bind(this);
        this.searchInput.addEventListener('keyup', searchTable, false);
    }

    searchTable() {
        let td, i, txtValue;
        const filter    = this.searchInput.value.toLowerCase();
        const tr        = this.tableRows;

        let cnt = 0;

        if (!filter) {
            this.counter.textContent = tr.length;

            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = '';
            }

            return;
        }

        // i = 1 because search must skip first line with column names
        for (i = 1; i < tr.length; i++) {
            td = tr[i].getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < td.length; j++) {
                if (td[j] && !inArray(this.skipCols, j)) {
                    txtValue = this.getCellText(td[j]);
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? '' : 'none';
            cnt += found ? 1 : 0;
        }

        this.counter.textContent = cnt;
    }

    getCellText(td) {
        const select = td.querySelector('select');

        if (select) {
            return select.options[select.selectedIndex].textContent || '';
        }

        return td.textContent || td.innerText || '';
    }

    get counter() {
        return closest(document.getElementById(this.tableId), '.card.events-card').querySelector('div.card-header-title span');
    }

    get tableRows() {
        return document.querySelectorAll(`#${this.tableId} tr`);
    }

    get searchInput() {
        return closest(document.getElementById(this.tableId), '.card.events-card').querySelector('#search');
    }
}
