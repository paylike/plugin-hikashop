/// <reference types="cypress" />

'use strict';

import { PaylikeTestHelper } from './hikashop_helper.js';
import { PaylikeCurrencies } from './currencies.js';

export var TestMethods = {

    /** Admin & frontend user credentials. */
    StoreUrl: Cypress.env('ENV_STORE_URL'),
    AdminUrl: Cypress.env('ENV_ADMIN_URL'),
    StoreUsername: Cypress.env('ENV_CLIENT_USER'),
    StorePassword: Cypress.env('ENV_CLIENT_PASS'),

    RemoteVersionLogUrl: Cypress.env('REMOTE_LOG_URL'),
    CaptureMode: Cypress.env('ENV_CAPTURE_MODE'),

    /**
     * Constants used to make or skip some tests.
     */
    NeedToAdminLogin: true === Cypress.env('ENV_STOP_EMAIL') ||
                      true === Cypress.env('ENV_LOG_VERSION') ||
                      true === Cypress.env('ENV_SETTINGS_CHECK'),

    /** Construct some variables to be used bellow. */
    PaylikeName: 'paylike',
    HikashopAdminUrl: '/index.php?option=com_hikashop',
    ManageExtensionsAdminUrl: '/index.php?option=com_installer&view=manage',
    ManageEmailSettingUrl: '/index.php?option=com_hikashop&ctrl=email',
    ManagePaylikeSettingUrl: '/index.php?option=com_hikashop&ctrl=plugins&plugin_type=payment',
    OrdersPageAdminUrl: '/index.php?option=com_hikashop&ctrl=order&order_type=sale',

    /**
     * Get Hikashop & Paylike versions and send log data.
     */
    logHikashopPaylikeVersions() {
        /** Go to hikashop section from admin. */
        cy.goToPage(this.HikashopAdminUrl);

        cy.get('.hikashop_footer').then(($hikashopFooter) => {
            var footerText = $hikashopFooter.text();
            var hikashopVersion = footerText.replace(/[^0-9.]/g, '');
            cy.wrap(hikashopVersion).as('hikashopVersion');
        });

        /** Go to system settings admin page. */
        cy.goToPage(this.ManageExtensionsAdminUrl);

        /** Set position relative for toolbar. */
        PaylikeTestHelper.setPositionRelativeOn('#subhead-container');

        /** Select search input and type "paylike", then press enter. */
        cy.get('input[name="filter[search]"]').type(`${this.PaylikeName}{enter}`);

        /** Select row from plugins table to get plugin version from there. */
        cy.get('tbody tr').each(($element, index, $list) => {
            var rowText = $element.text();
            if (rowText.includes('hikashoppayment')) {
                cy.get('tr td:nth-child(6)').eq(index).then($pluginVersion => {
                    var paylikePaymentVersion = $pluginVersion.text();
                    /** Make global variable to be accessible bellow. */
                    cy.wrap(paylikePaymentVersion).as('paylikePaymentVersion');
                });
            }
        });

        /** Get global variables and make log data request to remote url. */
        cy.get('@hikashopVersion').then(hikashopFooterVersion => {
            cy.get('@paylikePaymentVersion').then(paylikeModuleVersion => {

                cy.request('GET', this.RemoteVersionLogUrl, {
                    key: hikashopFooterVersion,
                    tag: 'hikashop',
                    view: 'html',
                    ecommerce: hikashopFooterVersion,
                    plugin: paylikeModuleVersion
                }).then((resp) => {
                    expect(resp.status).to.eq(200);
                });
            });
        });
    },

    /**
     * Modify Hikashop email settings (disable notifications)
     */
    deactivateHikashopEmailNotifications() {
        /** Go to hikashop email settings page. */
        cy.goToPage(this.ManageEmailSettingUrl);

        /** Set position relative for toolbar. */
        PaylikeTestHelper.setPositionRelativeOn('#subhead-container');

        /** Disable admin email notifications. */
        cy.get('span[id*="config_value-order_"]').each(($element) => {
            console.log($element.attr('id'))
            if (
                'config_value-order_notification.published' == $element.attr('id') ||
                'config_value-order_admin_notification.published' == $element.attr('id')
            ) {
                var $spanLink = $element.children('a')
                if ($spanLink.hasClass('icon-publish')) {
                    $spanLink.trigger('click');
                }
            }
        });
    },

    /**
     * Modify Paylike settings
     */
    changePaylikeCaptureMode() {
        /** Go to plugins page, and select Paylike. */
        cy.goToPage(this.ManagePaylikeSettingUrl);

        /** Set position relative for toolbar. */
        PaylikeTestHelper.setPositionRelativeOn('#subhead-container');

        /** Select search input and type "paylike", then press enter. */
        cy.wait(1000);
        cy.get('input[name=search]').type(`${this.PaylikeName}{enter}`);

        /** Select link to Paylike settings. */
        cy.get('a[href*="' + `=edit&name=${this.PaylikeName}` + '"]').click();

        /** Set position relative for toolbar. */
        PaylikeTestHelper.setPositionRelativeOn('#subhead-container');

        /** Change capture mode. */
        cy.get('#datapaymentpayment_paramsinstant_mode').select(this.CaptureMode);
        cy.get('#toolbar-save').click();
    },

    /**
     * Make an instant payment
     */
    makePaymentFromFrontend(currency) {
        /** Go to store frontend. */
        cy.goToPage(this.StoreUrl);

        /** Client frontend login. */
        cy.get('input[name=username]').type(`${this.StoreUsername}`);
        cy.get('input[name=password]').type(`${this.StorePassword}{enter}`);

        /** Change currency & wait for products price to finish update. */
        cy.get('#hikashopcurrency option').each(($option) => {
            if ($option.text().includes(currency)) {
                cy.get('#hikashopcurrency').select($option.val());
            }
        });
        cy.wait(2000);

        /** Add to cart random product. */
        var randomInt = PaylikeTestHelper.getRandomInt(/*max*/ 6);
        cy.get('.hikabtn.hikacart').eq(randomInt).click();

        /** Wait for 'added to cart' notification to disappear */
        cy.wait(3000);
        cy.get('.notifyjs-metro-base.notifyjs-metro-info').should('not.exist');

        /** Proceed to checkout. */
        cy.get('.hikashop_cart_proceed_to_checkout').click();

        /** Choose Paylike. */
        cy.get(`input[id*=${this.PaylikeName}]`).click();

        /**
         * Extract order amount
         */
        cy.get('.hikashop_checkout_cart_final_total').then(($frontendTotalAmount) => {
            /** Get multiplier based on currency code. */
            var multiplier = PaylikeCurrencies.get_paylike_currency_multiplier(currency);

            /** Replace any character except numbers, commas, points */
            var filtered = ($frontendTotalAmount.text()).replace(/[^0-9,.]/g, '')
            var matchPointFirst = filtered.match(/\..*,/g);
            var matchCommaFirst = filtered.match(/,.*\./g);

            if (matchPointFirst) {
                var amountAsText = (filtered.replace('.', '')).replace(',', '.');
            } else if (matchCommaFirst) {
                var amountAsText = filtered.replace(',', '');
            } else {
                var amountAsText = filtered.replace(',', '.');
            }
            var formattedAmount = parseFloat(amountAsText);
            var expectedAmount = formattedAmount * multiplier;

            /** Save expected amount as global. */
            cy.wrap(expectedAmount).as('expectedAmount');
        });

        /** Go to checkout next step. */
        cy.get('#hikabtn_checkout_next').click();

        /** Check if order was placed. */
        cy.get('#paylike_paying').should('be.visible');

        /**
         * Fill in Paylike popup.
         */
        PaylikeTestHelper.fillAndSubmitPaylikePopup();

        /** Verify amount. */
        /** We verify here, because "window.paylikeAmount" is available after paylike popup show */
        cy.get('@expectedAmount').then(expectedAmount => {
            cy.window().then((win) => {
                expect(expectedAmount).to.eq(Number(win.paylikeAmount))
            })
        });

        /** Check if order was paid. */
        cy.get('.hikashop_paylike_end #paylike_paid').should('be.visible');
    },

    /**
     * Process last order from admin panel
     */
    processOrderFromAdmin(contextFlag = false) {

        /** Login & go to admin orders page. */
        if (false === this.NeedToAdminLogin && !contextFlag) {
            cy.goToPage(this.OrdersPageAdminUrl);
            PaylikeTestHelper.loginIntoAdmin();
        } else {
            cy.goToPage(this.OrdersPageAdminUrl);
        }

        PaylikeTestHelper.setPositionRelativeOn('#subhead-container');

        /** Click on first order from table (last created). */
        cy.get('.hikashop_order_number_value a').first().click();

        /**
         * If CaptureMode='Delayed' => make 'capture' (set shipped on order status)
         * If CaptureMode='Instant' => make 'refund' (set refunded on order status)
         */
        if ('Delayed' === this.CaptureMode) {
            PaylikeTestHelper.setPositionRelativeOn('#subhead-container');
            PaylikeTestHelper.changeOrderStatus('shipped');
        } else {
            PaylikeTestHelper.setPositionRelativeOn('#subhead-container');
            PaylikeTestHelper.changeOrderStatus('refunded');
        }
    },
    /**
     * Make payment with specified currency and process order
     */
    payWithSelectedCurrency(currency, contextFlag = false) {

        /** Make an instant payment. */
        it(`makes a Paylike payment with "${currency}"`, () => {
            this.makePaymentFromFrontend(currency);
        });

        /** Process last order from admin panel. */
        it('process (capture/refund/void) an order from admin panel', () => {
            this.processOrderFromAdmin(contextFlag);
        });

        /** Send log if currency = DKK. */
        /**
         * HARDCODED currency
         */
        if ('DKK' == currency) {
            it('log hikashop & paylike versions remotely', () => {
                this.logHikashopPaylikeVersions();
            });
        }
    }
}