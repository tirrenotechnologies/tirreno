import {BaseFilter} from './BaseFilter.js?v=2';
import {
    renderFileTypeSelectorItem,
    renderFileTypeSelectorChoice,
} from '../DataRenderers.js?v=2';

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
