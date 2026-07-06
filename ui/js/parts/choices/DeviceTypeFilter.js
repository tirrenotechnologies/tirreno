import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderDeviceTypeSelectorItem,
    renderDeviceTypeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class DeviceTypeFilter extends BaseFilter {
    constructor() {
        super(
            '#device-type-selectors',
            renderDeviceTypeSelectorItem,
            renderDeviceTypeSelectorChoice,
            'deviceTypeFilterChanged'
        );
    }
}
