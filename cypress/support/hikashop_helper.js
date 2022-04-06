import { PaylikeCurrencies } from './currencies.js';

export let PaylikeTestHelper = {
    /**
     * Set position=relative on selected element
     * Useful when an element cover another element
     *
     * @param {String} selector
     */
    setPositionRelativeOn(selector) {
        cy.get(selector).then(($selectedElement) => {
            $selectedElement.attr('style', 'position:relative;');
        });
    },
    /**
     * Filter amount text with symbols
     * Get it in currency minor unit
     *
     * @param {Object} $unfilteredAmount
     * @param {String} currency
     *
     * @return {Number}
     */
     filterAndGetAmountInMinor($unfilteredAmount, currency) {
        var formattedAmount = this.filterAndGetAmountInMajorUnit($unfilteredAmount);

        /** Get multiplier based on currency code. */
        var multiplier = PaylikeCurrencies.get_paylike_currency_multiplier(currency);

        return Math.ceil(Math.round(formattedAmount * multiplier));
    },

    /**
     * Filter amount text with symbols
     * Get it in currency major unit
     *
     * @param {Object} $unfilteredAmount
     *
     * @return {Number}
     */
     filterAndGetAmountInMajorUnit($unfilteredAmount) {
        /** Replace any character except numbers, commas, points */
        var filtered = ($unfilteredAmount.text()).replace(/[^0-9,.][a-z.]*/g, '')
        var matchPointFirst = filtered.match(/\..*,/g);
        var matchCommaFirst = filtered.match(/,.*\./g);

        if (matchPointFirst) {
            var amountAsText = (filtered.replace('.', '')).replace(',', '.');
        } else if (matchCommaFirst) {
            var amountAsText = filtered.replace(',', '');
        } else {
            var amountAsText = filtered.replace(',', '.');
        }

        return parseFloat(amountAsText);
    },

    /**
     * Get a random int/float between 0 and provided max
     * @param {int|float} max
     * @returns int|float
     */
    getRandomInt(max) {
        return Math.floor(Math.random() * max);
    },
    /**
     * Fill Paylike popup and submit the form
     */
    fillAndSubmitPaylikePopup() {
        cy.get('#card-number').type(`${Cypress.env('ENV_CARD_NUMBER')}`);
        cy.get('#card-expiry').type(`${Cypress.env('ENV_CARD_EXPIRY')}`);
        cy.get('#card-code').type(`${Cypress.env('ENV_CARD_CVV')}{enter}`);
    },
    /**
     * Change order status
     */
    changeOrderStatus(status) {
        cy.get('.hkc-md-6 #hikashop_order_field_general a .fa-pen').click();
        cy.get('.hikashop_order_status select').select(status);
        cy.get('.hkc-md-6 #hikashop_order_field_general .fa-save').click();
    },
    /**
     * Login into admin
     */
    loginIntoAdmin() {
        /** Select username & password inputs, then press enter. */
        cy.get('input[name=username]').type(`${Cypress.env('ENV_ADMIN_USER')}`);
        cy.get('input[name=passwd]').type(`${Cypress.env('ENV_ADMIN_PASS')}{enter}`);
    },
};
