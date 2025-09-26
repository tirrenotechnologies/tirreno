export class Tooltip {

    static init() {
        this.addTooltipToSpans();
        this.addTooltipToParagraphs();
        this.addTooltipToTableHeaders();
    }

    static addTooltipsToEventDetailsPanel() {
        this.baseTooltips('.details-card .tooltip', true);
    }

    static addTooltipsToScoreDetails() {
        this.baseTooltips('.score-details-content .tooltip', true);
    }

    static addTooltipsToTiles() {
        this.baseTooltips('span.detailsTileValue .tooltip', false);
    }

    static addTooltipsToGridRecords(tableId) {
        this.baseTooltips(`#${tableId} td .tooltip.tooltipster-word-break`, true, true);
        this.baseTooltips(`#${tableId} td .tooltip:not(.tooltipster-word-break)`, true);
    }

    static addTooltipToSpans() {
        this.baseTooltips('span.tooltip', true);
    }

    static addTooltipToTableHeaders() {
        this.baseTooltips('th.tooltip', true);
    }

    static addTooltipToParagraphs() {
        this.baseTooltips('p.tooltip', true);
    }

    static addTooltipsToRulesProportion() {
        this.baseTooltips('td .tooltip', true);
    }

    static addTooltipsToClock() {
        this.baseTooltips('div .day-tile.tooltip, div .time-tile.tooltip', true);
    }

    static baseTooltips(path, useMaxWidth = true, wordBreak = false) {
        $(document.querySelectorAll(path)).tooltipster(this.getConfig(useMaxWidth, wordBreak));
    }

    static getConfig(useMaxWidth, wordBreak) {
        const config = {
            delay: 0,
            delayTouch: 0,
            debug: false,
            side: 'bottom',
            animationDuration: 0,
            theme: ['tooltipster-borderless'],
        };

        if (wordBreak) {
            config.theme.push('tooltipster-word-break');
        }

        if (useMaxWidth) {
            config['maxWidth'] = 250;
        }

        return config;
    }
}
