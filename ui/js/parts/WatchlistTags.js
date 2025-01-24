import {fireEvent} from './utils/Event.js?v=2';
import {handleAjaxError} from './utils/ErrorHandler.js?v=2';

export class WatchlistTags {

    constructor() {
        this.onRemoveUserTagClick = this.onRemoveUserTagClick.bind(this);
        this.onUserTagRemoveCallback = this.onUserTagRemoveCallback.bind(this);

        this.tags.forEach(tag => tag.addEventListener('click', this.onRemoveUserTagClick, false));
    }

    onRemoveUserTagClick(e) {
        const wrapper = e.target.closest('.control');
        const id = wrapper.querySelector('[data-id]').dataset.id;
        const data = {
            'userId': id,
            'token': this.csrf,
        };
        const me = this;

        $.ajax({
            type: 'POST',
            url: '/admin/removeWatchlisted',
            data: data,
            scope: me,
            success: me.onUserTagRemoveCallback,
            error: handleAjaxError,
            dataType: 'json'
        });
    }

    onUserTagRemoveCallback(response) {
        if (response.success) {
            const userId = response.userId;
            const tag = document.querySelector(`[data-id="${userId}"]`);
            const wrapper = tag.closest('.control');

            wrapper.remove();

            //TODO: fireevent reloadData();
            fireEvent('watchlistTagRemoved', {});
        }
    }

    get tags() {
        return document.querySelectorAll('#important-users a.is-delete');
    }

    get csrf() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
}
