export class Tooltip {

    static init() {
        this.addTooltipToSpans();
        this.addTooltipToParagraphs();
        this.addTooltipToTableHeaders();
    }

    static addTooltipsToEventDetailsPanel() {
        const path   = '.details-card .tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static addTooltipsToScoreDetails() {
        const path   = '.score-details-content .tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static addTooltipsToTiles() {
        const path   = 'span.detailsTileValue .tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(false);

        $(items).tooltipster(config);
    }

    static addTooltipsToGridRecords(tableId) {
        const path1   = `#${tableId} td .tooltip:not(.iptype)`;
        const items1  = document.querySelectorAll(path1);
        const config1 = this.getConfig(true);

        const path2   = `#${tableId} td .tooltip.iptype`;
        const items2  = document.querySelectorAll(path2);
        const config2 = this.getConfig(true);

        $(items1).tooltipster(config1);
        $(items2).tooltipster(config2);
    }

    static addTooltipToSpans() {
        const path   = 'span.tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static addTooltipToTableHeaders() {
        const path   = 'th.tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static addTooltipToParagraphs() {
        const path   = 'p.tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static addTooltipsToRulesProportion() {
        const path   = 'td .tooltip';
        const items  = document.querySelectorAll(path);
        const config = this.getConfig(true);

        $(items).tooltipster(config);
    }

    static getConfig(useMaxWidth) {
        const config = {
            delay: 0,
            delayTouch: 0,
            debug: false,
            side: 'bottom',
            animationDuration: 0,
            theme: 'tooltipster-borderless'
        };

        if (useMaxWidth) {
            config['maxWidth'] = 250;
        }

        return config;
    }
}
