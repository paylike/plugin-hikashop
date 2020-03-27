<?php


namespace Hikashop;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\WebDriverDimension;

class HikashopRunner extends HikashopTestHelper
{

    /**
     * @param $args
     *
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    public function ready($args) {
        $this->set($args);
        $this->go();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function loginAdmin() {
        $this->goToPage('', '#mod-login-username', true);
        while ( ! $this->hasValue('#mod-login-username', $this->user)) {
            $this->typeLogin();
        }
        $this->click('.login-button');
        $this->waitForElement('.com_cpanel');

    }

    /**
     *  Insert user and password on the login screen
     */
    private function typeLogin() {
        $this->type('#mod-login-username', $this->user);
        $this->type('#mod-login-password', $this->pass);
    }

    /**
     * @param $args
     */
    private function set($args) {
        foreach ($args as $key => $val) {
            $name = $key;
            if (isset($this->{$name})) {
                $this->{$name} = $val;
            }
        }
    }

    public function changeCurrency() {
        $this->selectByText('.hikashopcurrency', $this->currency);

    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function disableEmail() {
        if ($this->stop_email === true) {
            $this->goToPage('index.php?option=com_hikashop&ctrl=email', '#hikashop_email_listing', true);
            $this->click("#config_value-order_notification.published");
            $this->click("#config_value-order_admin_notification.published");
            $this->waitForElement(".hk_center #config_value-order_admin_notification.published .icon-unpublish");
        }
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */

    public function changeMode() {
        $this->goToPage('/index.php?option=com_hikashop&ctrl=plugins&plugin_type=payment', '.hikashop_footer', true);
        $this->type("#search", "Paylike");
        $this->click(".icon-search");
        $this->click(".searchtext");
        $this->waitForElement("#datapaymentpayment_paramsinstant_mode_chzn");
        $this->captureMode();
    }


    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */

    private function logVersionsRemotly() {
        $versions = $this->getVersions();
        $this->wd->get(getenv('REMOTE_LOG_URL') . '&key=' . $this->get_slug($versions['ecommerce']) . '&tag=hikashop&view=html&' . http_build_query($versions));
        $this->waitForElement('#message');
        $message = $this->getText('#message');
        $this->main_test->assertEquals('Success!', $message, "Remote log failed");
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function getVersions() {
        $this->goToPage('index.php?option=com_hikashop', '.hikashop_footer', true);
        $hikashop = $this->getText('.hikashop_footer');
        $hikashop = preg_replace("/[^0-9.]/", "", $hikashop);
        $this->goToPage("/index.php?option=com_installer&view=manage", "#filter_search", true);
        $this->type("#filter_search", "paylike");
        $this->click(".icon-search");
        $this->waitForElement(".table-striped");
        $paylike = $this->getText('.table-striped tbody td.hidden-phone');

        return ['ecommerce' => $hikashop, 'plugin' => $paylike];
    }


    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    private function directPayment() {
        $this->goToPage('', '.hikashop_product');
        $this->changeCurrency();
        $this->addToCart();
        $this->proceedToCheckout();
        $this->finalPaylike();
        $this->selectOrder();
        if ($this->capture_mode == 'delayed') {
            $this->checkNoCaptureWarning();
            $this->capture();
        } else {
            $this->refund();
        }

    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    public function checkNoCaptureWarning() {
        $this->moveOrderToStatus('confirmed');
        $this->waitForElement(".admintable .hikashop_order_status span");
        $messages = $this->getText('.admintable .hikashop_order_status span');
        $this->main_test->assertEquals('confirmed', $messages, "Not captured warning");
    }

    /**
     * @param $status
     *
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    public function moveOrderToStatus($status) {
        $this->waitForElement('.hkc-md-6 #hikashop_order_field_general a .fa-pen');
        $this->click('.hkc-md-6 #hikashop_order_field_general a .fa-pen');
        $this->waitForElement(".hikashop_order_status select");
        $this->selectValue(".hikashop_order_status select", "$status");
        $this->click(".hkc-md-6 #hikashop_order_field_general .fa-save");
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    public function capture() {
        $this->moveOrderToStatus('shipped');
        $this->waitForElement(".admintable .hikashop_order_status span");
        $messages = $this->getText(".admintable .hikashop_order_status span");
        $this->main_test->assertEquals('shipped', $messages, "Shipped");
    }

    /**
     *
     */
    public function captureMode() {
        $this->wd->executeScript('window.scrollTo(0,document.body.scrollHeight);');
        $this->click('#datapaymentpayment_paramsinstant_mode_chzn');
        $this->click("//*[contains(text(), '" . ucfirst($this->capture_mode) . "')]");
        $this->click('.button-save');
        $this->waitForElement(".alert-success");
    }


    /**
     *
     */
    public function addToCart() {
        $this->click('.hikacart');
        $this->waitForElement('.notifyjs-corner .notifyjs-wrapper');

    }

    /**
     *
     */
    public function proceedToCheckout() {
        $this->click(".hikashop_cart_proceed_to_checkout");
        $this->waitForElement("#username");
        $this->type('#username', $this->client_user);
        $this->type('#passwd', $this->client_pass);
        $this->click('.hikabtn_checkout_login_form');
        $this->waitForElement(".hika_address_display");
        $this->choosePaylike();


        $expectedAmount = $this->getText('.hikashop_checkout_cart_final_total');
        $expectedAmount = str_replace(",", '.', $expectedAmount);
        $expectedAmount = preg_replace("/[^0-9.]/", "", $expectedAmount);
        $expectedAmount = trim($expectedAmount, '.');
        $expectedAmount = round(round($expectedAmount, 3) * get_paylike_currency_multiplier($this->currency));


        $this->click('#hikabtn_checkout_next');
        $this->waitForElement('.paylike .payment');

        $amount = $this->wd->executeScript("return window.paylikeAmount");
        $this->main_test->assertEquals($expectedAmount, $amount, "Checking minor amount for " . $this->currency);
    }

    /**
     */
    public function choosePaylike() {
        $this->checkbox("//input[contains(@id,'paylike')]");
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function finalPaylike() {

        $this->popupPaylike();
        $this->waitForElement(".hikashop_paylike_end #paylike_paid");
        $completedValue = $this->getText(".hikashop_paylike_end #paylike_paid h3");
        // because the title of the page matches the checkout title, we need to use the order received class on body
        $this->main_test->assertEquals('You have completed your order!', $completedValue);
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function popupPaylike() {
        try {
            $this->type('.paylike.overlay .payment form #card-number', 41000000000000);
            $this->type('.paylike.overlay .payment form #card-expiry', '11/22');
            $this->type('.paylike.overlay .payment form #card-code', '122');
            $this->click('.paylike.overlay .payment form button');
        } catch (NoSuchElementException $exception) {
            $this->confirmOrder();
            $this->popupPaylike();
        }

    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function selectOrder() {
        $this->goToPage("index.php?option=com_hikashop&ctrl=order&order_type=sale", ".hikashop_order_number_value a",
            true);
        $this->click(".hikashop_order_number_value a");
        try {
            $this->waitForElement('.hikashop_order_ordernumber');
        } catch (TimeoutException $exception) {
            $this->click(".hikashop_order_number_value a");
        }
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    public function refund() {
        $this->moveOrderToStatus('refunded');
        $this->waitForElement(".admintable .hikashop_order_status span");
        $messages = $this->getText('.admintable .hikashop_order_status span');
        $this->main_test->assertEquals('refunded', $messages, "Refunded");
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    public function confirmOrder() {
        $this->waitForElement('#paylike-payment-button');
        $this->click('#paylike-payment-button');
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function settings() {

        $this->disableEmail();
        $this->changeMode();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeOutException
     * @throws UnexpectedTagNameException
     */
    private function go() {
        $this->changeWindow();
        $this->loginAdmin();
        if ($this->log_version) {
            $this->logVersionsRemotly();

            return $this;
        }
        $this->settings();
        $this->directPayment();

    }

    /**
     *
     */
    private function changeWindow() {
        $this->wd->manage()->window()->setSize(new WebDriverDimension(1600, 1024));
    }


}

