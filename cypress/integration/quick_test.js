/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';

describe('plugin quick test', () => {
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

    let captureModes = ['Instant', 'Delayed'];
    let currency = Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH');

    /**
     * Modify plugin settings
     */
    it(`modify settings for capture mode -> ${captureModes[1]}`, () => {
        TestMethods.changeCaptureMode(captureModes[1]);
    });

    /**
     * Make payment & CAPTURE from admin
     */
    TestMethods.payWithSelectedCurrency(currency, 'capture');

    /**
    * Make payment & VOID from admin
    */
    TestMethods.payWithSelectedCurrency(currency, 'void');

    /**
    * Make payment & CAPTURE from admin, then refund it
    */
    TestMethods.payWithSelectedCurrency(currency, 'capture');
    it('process REFUND last order from admin panel', () => {
        TestMethods.processOrderFromAdmin('refund');
    });


    /**
     * INSTANT MODE
     */
    /**
     * Modify plugin settings
     */
    it(`modify settings for capture mode -> ${captureModes[0]}`, () => {
        TestMethods.changeCaptureMode(captureModes[0]);
    });
    /**
     * Make payment & REFUND from admin
     */
    TestMethods.payWithSelectedCurrency(currency, 'refund');

}); // describe