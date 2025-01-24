export class Loader {

    constructor() {
        this.symbols = [
            '<p class=\'level-center\'>⣾</p>',
            '<p class=\'level-center\'>⣷</p>',
            '<p class=\'level-center\'>⣯</p>',
            '<p class=\'level-center\'>⣟</p>',
            '<p class=\'level-center\'>⡿</p>',
            '<p class=\'level-center\'>⢿</p>',
            '<p class=\'level-center\'>⣻</p>',
            '<p class=\'level-center\'>⣽</p>'
        ];
    }

    start(loaderEl) {
        this.loaderEl = loaderEl;

        let me = this;
        let counter = 0;

        this.loaderEl.classList.add('loading');
        this.loaderEl.classList.remove('loaded');

        let timerId = setInterval(() => {
            if (me.loaderEl.classList.contains('loaded')) {
                clearInterval(timerId);
                return;
            }

            let symbol = me.symbols[counter % me.symbols.length];

            me.loaderEl.innerHTML = symbol;

            counter++;
        }, 85);
    }

    stop() {
        this.loaderEl.classList.add('loaded');
        this.loaderEl.classList.remove('loading');
    }
}
