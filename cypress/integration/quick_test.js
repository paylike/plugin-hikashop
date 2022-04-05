/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';
import { PaylikeTestHelper } from '../support/hikashop_helper.js';

describe('paylike plugin quick test', () => {
    /**
     * Go to backend site admin if necessary
     */
    before(() => {
        cy.goToPage(Cypress.env('ENV_ADMIN_URL'));
        PaylikeTestHelper.loginIntoAdmin();
    });

    /**
     * Run this on every test case bellow
     * - preserve cookies between tests
     */
     beforeEach(() => {
        Cypress.Cookies.defaults({
            preserve: (cookie) => {
              return true;
            }
        });
    });

    let captureMode = 'Delayed';

    /**
     * Modify Paylike settings
     */
    it('modify Paylike settings for capture mode', () => {
        TestMethods.changePaylikeCaptureMode(captureMode);
    });

    /**
     * Make a payment
     */
    it('makes a payment with Paylike', () => {
        TestMethods.makePaymentFromFrontend(Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH'));
    });

    /**
     * Process last order from admin panel
     */
    it('process (capture/refund/void) an order from admin panel', () => {
        TestMethods.processOrderFromAdmin();
    });

}); // describe