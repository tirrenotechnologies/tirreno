export class Loader {

    constructor() {
        this.symbols = [
            this.el('⣾'),
            this.el('⣷'),
            this.el('⣯'),
            this.el('⣟'),
            this.el('⡿'),
            this.el('⢿'),
            this.el('⣻'),
            this.el('⣽'),
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

            me.loaderEl.replaceChildren(symbol);

            counter++;
        }, 85);
    }

    stop() {
        this.loaderEl.classList.add('loaded');
        this.loaderEl.classList.remove('loading');
    }

    el(c) {
        const node = document.createElement('p');
        node.className = 'level-center';
        node.textContent = c;

        return node;
    }
}
