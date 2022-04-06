/// <reference types="cypress" />

'use strict';

import { TestMethods } from '../support/test_methods.js';
import { PaylikeTestHelper } from '../support/hikashop_helper.js';

describe('paylike plugin version log remotely', () => {
    /**
     * Go to backend site admin
     */
    before(() => {
        cy.goToPage(TestMethods.HikashopAdminUrl);
        PaylikeTestHelper.loginIntoAdmin();
    });

    /** Send log after full test finished. */
    it('log shop & paylike versions remotely', () => {
        TestMethods.logHikashopPaylikeVersions();
    });
}); // describe