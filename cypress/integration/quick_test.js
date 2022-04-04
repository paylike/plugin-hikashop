/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';
import { PaylikeTestHelper } from '../support/hikashop_helper.js';

describe('paylike plugin quick test', () => {
    /**
     * Go to backend site admin if necessary
     */
    before(() => {
        if (TestMethods.NeedToAdminLogin) {
            cy.goToPage(Cypress.env('ENV_ADMIN_URL'));
        }
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

    /**
     * Login into admin if necessary.
     */
    if (TestMethods.NeedToAdminLogin) {
        it('login into admin backend', () => {
            PaylikeTestHelper.loginIntoAdmin();
        });
    }

    /**
     * Modify Hikashop email settings (disable notifications)
     */
    if (Cypress.env('ENV_STOP_EMAIL') ?? false) {
        it('modify Hikashop settings for email notifications', () => {
            TestMethods.deactivateHikashopEmailNotifications();
        });
    }

    /**
     * Modify Paylike settings
     */
    if (Cypress.env('ENV_SETTINGS_CHECK') ?? false) {
        it('modify Paylike settings for capture mode', () => {
            TestMethods.changePaylikeCaptureMode();
        });
    }

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