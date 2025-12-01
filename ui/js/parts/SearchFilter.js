import {fireEvent} from './utils/Event.js?v=2';
import {debounce} from './utils/Functions.js?v=2';

export class SearchFilter {
    constructor(id=null) {
        this.id = id;

        const onSearchInputChange = this.onSearchInputChange.bind(this, this.id);
        const debouncedOnSearchInputChange = debounce(onSearchInputChange);
        this.searchField.addEventListener('input', debouncedOnSearchInputChange, false);
    }

    onSearchInputChange(id, {target}) {
        const value = target.value;
        let eventName = 'searchFilterChanged';
        if (id !== null) {
            eventName = id + '-' + eventName;
        }
        fireEvent(eventName, {query: value});
    }

    getValue() {
        return this.searchField.value;
    }

    get searchField() {
        return document.getElementById('search');
    }
}
