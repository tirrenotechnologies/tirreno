import {BaseFilter} from './BaseFilter.js?v=0.10.0';
import {
    renderFileTypeSelectorItem,
    renderFileTypeSelectorChoice,
} from '../DataRenderers.js?v=0.10.0';

export class FileTypeFilter extends BaseFilter {
    constructor() {
        super(
            '#file-type-selectors',
            renderFileTypeSelectorItem,
            renderFileTypeSelectorChoice,
            'fileTypeFilterChanged'
        );
    }
}
