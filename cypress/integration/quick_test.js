/// <reference types="cypress" />

'use strict';

import { helper } from "./helper.js";

describe('paylike plugin quick test', () => {

    /** Get ENV variables to use forward. */
    const AdminUrl = Cypress.env("ENV_ADMIN_URL");
    const HttpUsername = Cypress.env('ENV_HTTP_USER');
    const HttpPassword = Cypress.env('ENV_HTTP_PASS');

    const AdminUsername = Cypress.env('ENV_ADMIN');
    const AdminPassword = Cypress.env('ENV_ADMIN_PASS');

    const RemoteVersionLogUrl = Cypress.env('REMOTE_LOG_URL');
    const CurrencyToChangeWith = Cypress.env('ENV_CURRENCY_TO_CHANGE_WITH');

    /** Construct some variables to be used bellow. */
    const PaylikeName = 'paylike';
    const HikashopAdminUrl = (AdminUrl + '/index.php?option=com_hikashop');
    // const ManageExtensionsAdminUri = 'index.php?option=com_installer&view=manage';
    const ManageExtensionsAdminUrl = (AdminUrl + '/index.php?option=com_installer&view=manage');
    const ManageEmailSettingUrl = (AdminUrl + '/index.php?option=com_hikashop&ctrl=email');

    /**
     * Go to backend site admin
     */
    before(() => {
        cy.visit(AdminUrl, {
            auth: {
                username: HttpUsername,
                password: HttpPassword,
              },
        });
    });

    /**
     * Run this on every test case bellow
     * - preserve cookies between tests
     */
    beforeEach(() => {
        Cypress.Cookies.preserveOnce(Cypress.env('ENV_COOKIE_HASH'));
    });

    /**
     * Login into admin.
     */
    it("manage Paylike module from admin", () => {
        /** Select username & password inputs, then press enter. */
        cy.get("input[name=username]").type(`${AdminUsername}`);
        cy.get("input[name=passwd]").type(`${AdminPassword}{enter}`);
    });

    /**
     * Get Hikashop & Paylike versions and send log data.
     */
    it("log hikashop & paylike versions remotely", () => {

        /** Go to hikashop section from admin. */
        cy.visit(HikashopAdminUrl, {
            auth: {
                username: HttpUsername,
                password: HttpPassword,
            },
        });

        cy.get('.hikashop_footer').then(($hikashopFooter) => {
            var footerText = $hikashopFooter.text();
            var hikashopVersion = footerText.replace(/[^0-9.]/g, '');
            cy.wrap(hikashopVersion).as('hikashopVersion');
        });

        /** Go to system settings admin page. */
        cy.visit(ManageExtensionsAdminUrl, {
            auth: {
                username: HttpUsername,
                password: HttpPassword,
            },
        });

        /** Change toolbar position to relative (to not cover search input during cypress test). */
        cy.get('#subhead-container').then(($toolbarNav) => {
            $toolbarNav.attr('style', 'position:relative;');
        });

        /** Select search input and type "paylike", then press enter. */
        cy.get('input[name="filter[search]"]').type(`${PaylikeName}{enter}`);

        /** Select row from plugins table to get plugin version from there. */
        cy.get("tbody tr").each(($element, index, $list) => {
            /** Get element text. */
            var rowText = $element.text();

            /** Check if all row text contain specific text. */
            if(rowText.includes("hikashoppayment")) {
                /** Select specific column from matched row, then get plugin version. */
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

                cy.request('GET', RemoteVersionLogUrl, {
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

    });

    /**
     * Modify hikashop settings
     */
    it("modify hikashop settings", () => {
        /** Go to system settings admin page. */
        cy.visit(ManageEmailSettingUrl, {
            auth: {
                username: HttpUsername,
                password: HttpPassword,
            },
        });

        /** Wait for page elements to loading. */
        cy.wait(1000);

        /** Disable email notifications. */
        // cy.get('#config_value-order_notification.published').click();
        // cy.get('#config_value-order_admin_notification.published').click();
        // cy.get('.hk_center #config_value-order_admin_notification.published .icon-unpublish');


    });


}); // describe
