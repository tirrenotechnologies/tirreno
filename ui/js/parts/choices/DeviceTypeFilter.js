import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderDeviceTypeSelectorItem,
    renderDeviceTypeSelectorChoice,
} from '../DataRenderers.js?v=2';

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
