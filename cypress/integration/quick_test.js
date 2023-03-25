/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';

describe('paylike plugin quick test', () => {
    /**
     * Go to backend site admin if necessary
     */
    before(() => {
        TestMethods.loginIntoClientAccount();
        TestMethods.loginIntoAdminBackend();
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
     * Make payment & CAPTURE from admin
     */
    TestMethods.payWithSelectedCurrency(Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH'), 'capture');

    /**
    * Make payment & VOID from admin
    */
    TestMethods.payWithSelectedCurrency(Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH'), 'void');

    /**
    * Make payment & CAPTURE from admin
    */
    TestMethods.payWithSelectedCurrency(Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH'), 'capture');
    it('process REFUND last order from admin panel', () => {
        TestMethods.processOrderFromAdmin('refund');
    });


}); // describe