import {BaseGrid} from './Base.js?v=2';
import {fireEvent} from '../utils/Event.js?v=2';

export class BaseGridWithPanel extends BaseGrid {

    constructor(gridParams) {
        super(gridParams);

        this.config = gridParams;
        this.markerClass = 'marker';

        this.allPanels = {
            'event':    {id: 'event-card',  closedEvent: 'eventPanelClosed',    close: 'closeEventPanel',   rowClicked: 'eventTableRowClicked'},
            'logbook':  {id: 'logbook-card',closedEvent: 'logbookPanelClosed',  close: 'closeLogbookPanel', rowClicked: 'logbookTableRowClicked'},
            'email':    {id: 'email-card',  closedEvent: 'emailPanelClosed',    close: 'closeEmailPanel',   rowClicked: 'emailTableRowClicked'},
            'device':   {id: 'device-card', closedEvent: 'devicePanelClosed',   close: 'closeDevicePanel',  rowClicked: 'deviceTableRowClicked'},
            'phone':    {id: 'phone-card',  closedEvent: 'phonePanelClosed',    close: 'closePhonePanel',   rowClicked: 'phoneTableRowClicked'},
        };

        this.panelType = gridParams.panelType;

        this.currentPanel = this.allPanels[this.panelType];

        const onDetailsPanelClosed = this.onDetailsPanelClosed.bind(this);
        window.addEventListener(this.currentPanel.closedEvent, onDetailsPanelClosed, false);

        const onTableRowClicked = this.onTableRowClicked.bind(this);
        window.addEventListener(this.currentPanel.rowClicked, onTableRowClicked, false);
    }

    drawCallback(settings) {
        super.drawCallback(settings);

        this.addTableRowsEvents();
    }

    onDetailsPanelClosed() {
        const markerClass = this.markerClass;
        const tableId     = this.config.tableId;

        $(`#${tableId} tbody tr`).removeClass(markerClass);
    }

    onRowClick(e) {
        const selection = window.getSelection();
        if ('Range' === selection.type) {
            return;
        }

        e.preventDefault();

        const row    = e.target.closest('tr');
        const itemId = row.dataset.itemId;
        const data   = {itemId: itemId};

        fireEvent(this.currentPanel.rowClicked, data);
    }

    onTableRowClicked(e) {
        e.preventDefault();

        const itemId      = e.detail.itemId;
        const targetRow   = this.table.querySelector(`tr[data-item-id="${itemId}"]`);

        const markerClass   = this.markerClass;
        const isRowMarkered = targetRow.classList.contains(markerClass);

        if (isRowMarkered) {
            fireEvent(this.currentPanel.close);
            targetRow.classList.remove(markerClass);
        } else {
            // close other panels
            for (const panel in this.allPanels) {
                if (panel !== this.panelType) {
                    const card = document.querySelector(`.details-card#${this.allPanels[panel].id}`);
                    if (card && !card.classList.contains('is-hidden')) {
                        fireEvent(this.allPanels[panel].closedEvent);
                        card.classList.add('is-hidden');
                    }
                }
            }
            // unmark other rows in the same table
            const rows = this.table.querySelectorAll('tr[data-item-id]');
            rows.forEach(row => row.classList.remove(markerClass));

            // mark current row
            targetRow.classList.add(markerClass);
        }
    }
}
