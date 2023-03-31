/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';
import { PluginTestHelper } from '../support/hikashop_helper.js';

describe('log version remotely', () => {
    /**
     * Go to backend site admin
     */
    before(() => {
        cy.goToPage(TestMethods.HikashopAdminUrl);
        PluginTestHelper.loginIntoAdmin();
    });

    /** Send log after full test finished. */
    it('log shop & plugin versions remotely', () => {
        TestMethods.logVersion();
    });
}); // describe