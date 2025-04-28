export class ThresholdsForm {
    constructor() {
        const updateReviewQueueOptions = this.updateReviewQueueOptions.bind(this);
        this.blacklistOptions.forEach(radio => {
            radio.addEventListener('change', updateReviewQueueOptions, false);
        });

        const updateBlacklistOptions = this.updateBlacklistOptions.bind(this);
        this.reviewQueueOptions.forEach(radio => {
            radio.addEventListener('change', updateBlacklistOptions, false);
        });
    }

    updateReviewQueueOptions(e) {
        const blacklistValue = this.blacklistVal;

        this.reviewQueueOptions.forEach(radio => {
            const value = parseInt(radio.value, 10);
            radio.disabled = value <= blacklistValue;
        });
    }

    updateBlacklistOptions(e) {
        const reviewValue = this.reviewQueueVal;

        this.blacklistOptions.forEach(radio => {
            const value = parseInt(radio.value, 10);
            radio.disabled = value >= reviewValue;
        });
    }


    get reviewQueueOptions() {
        return document.querySelectorAll('input[name="review-queue-threshold"]');
    }

    get blacklistOptions() {
        return document.querySelectorAll('input[name="blacklist-threshold"]');
    }

    get reviewQueueVal() {
        return parseInt(document.querySelector('input[name="review-queue-threshold"]:checked').value || 100, 10);
    }

    get blacklistVal() {
        return parseInt(document.querySelector('input[name="blacklist-threshold"]:checked').value || -1, 10);
    }
}
