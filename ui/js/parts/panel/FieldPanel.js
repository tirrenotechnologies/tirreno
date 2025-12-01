import {EventPanel} from './EventPanel.js?v=2';
import {renderJsonTextarea} from '../DataRenderers.js?v=2';

export class FieldPanel extends EventPanel {

    constructor() {
        let eventParams = {
            enrichment: false,
            type: 'field',
            url: `${window.app_base}/admin/fieldEventDetails`,
            cardId: 'field-card',
            panelClosed: 'fieldPanelClosed',
            closePanel: 'closeFieldPanel',
            rowClicked: 'fieldTableRowClicked',
        };
        super(eventParams);
    }
}
