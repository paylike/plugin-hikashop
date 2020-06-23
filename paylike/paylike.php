<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

include_once('Paylike/Client.php');
include_once('Paylike/Currencies.php');
include_once('vendor/autoload.php');
include_once('helpers/Paylike_Keys_Validator.php');

class plgHikashoppaymentPaylike extends hikashopPaymentPlugin
{
    public $name = 'paylike';
    public $multiple = true;

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);

        $lang = JFactory::getLanguage();
        $plugins = "plg_hikashoppayment_paylike";
        $base_dir = JPATH_ADMINISTRATOR;
        $lang->load($plugins, $base_dir, $lang->getTag(), true);
    }

    public function onPaymentConfiguration(&$element)
    {
        parent::onPaymentConfiguration($element);
    }

    public function onAfterOrderConfirm(&$order, &$methods, $method_id)
    {
        parent::onAfterOrderConfirm($order, $methods, $method_id);

        $input = JFactory::getApplication()->input;
        $ip    = $input->server->get('REMOTE_ADDR');

        $method =& $methods[$method_id];
        $this->modifyOrder($order->order_id, $method->payment_params->order_status, false, false);

        $lang = &JFactory::getLanguage();
        $config = &JFactory::getConfig();
        $locale=strtoupper(substr($lang->get('tag'), 0, 2));

        $price = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
        if (strpos($price, '.')) {
            $price =rtrim(rtrim($price, '0'), '.');
        }


        $customs = array();
        $products = hikashop_get('class.product');
        foreach ($order->cart->cart_products as $item):
            $product = $products->get($item->product_id);
        $product->product_name = str_replace(array('"',"'"), array('\"',"\'"), $product->product_name);
        $customs[]="{ product: '$product->product_name ($product->product_code)', quantity: $item->cart_product_quantity },";
        endforeach;

        $vars = array(
        "currency" => $this->currency->currency_code,
        "amount" => $price,
        "paylike_amount" => get_paylike_amount($price, $this->currency->currency_code),
        "public_key" => $method->payment_params->public_key,
        "order_id" => $order->order_id,
        "order_number" => $order->order_number,
        "method_id"=>$method_id,
        "custom"=>implode($customs, "\n"),
        "sitename"=>$config->get("sitename"),
        "customer_name"=>$order->cart->billing_address->address_firstname." ".$order->cart->billing_address->address_lastname,
        "customer_email"=>$this->user->user_email,
        "customer_phone"=>$order->cart->billing_address->address_telephone,
        "customer_address"=>$order->cart->shipping_address->address_street." ".$order->cart->shipping_address->address_city." ".$order->cart->shipping_address->address_post_code." ".$order->cart->shipping_address->address_state->zone_name." ".$order->cart->shipping_address->address_state->zone_code_2,
        "customer_ip"=>$ip,
        "history_url"=>$this->orderHistoryURL(),
        );


        $this->vars = $vars;

        return $this->showPage('end');
    }

    public function getPaymentDefaultValues(&$element)
    {
        $element->payment_name = JText::_('HIKASHOP_PAY_LIKE_NAME');
        $element->payment_description = JText::_('HIKASHOP_PAY_LIKE_DESCRIPTION');
        $element->payment_images = 'VISA,Credit_card';

        $element->payment_params->order_status = 'pending';
        $element->payment_params->confirmed_status = 'confirmed';
        $element->payment_params->information = JText::_('HIKASHOP_PAY_LIKE_INFORMATION');
        ;
    }

    public function onPaymentConfigurationSave(&$element)
    {
        /* Initialize validator object */
        $validator = new PaylikeValidator();
        $errors = array();
        /* Read fields */
        $app_key = $element->payment_params->private_key;
        $public_key = $element->payment_params->public_key;
        $mode = $element->payment_params->test_mode?"Test":"Live";

        /* Validate module keys */
        $error = $validator->validateAppKeyField($app_key, $mode);
        if (strlen($error)) {
            /* Clean app key*/
            $element->payment_params->private_key = '';
            $errors[] = $error;
        }

        $error = $validator->validatePublicKeyField($public_key, $mode);
        if (strlen($error)) {
            /* Clean public key*/
            $element->payment_params->public_key = '';
            $errors[] = $error;
        }

        if (sizeof($errors)) {
            $app = JFactory::getApplication();
            foreach ($errors as $error) {
                /* Display error message if exists */
                $app->enqueueMessage($error, 'error');
            }
            return false;
        }

        parent::onPaymentConfigurationSave($element);
        return true;
    }

    public function onPaymentNotification(&$statuses)
    {
        $act = JRequest::getVar("act");

        switch ($act):
            case "savingTransaction":
                $this->savingTransaction();
        break;
        endswitch;

        return true;
    }


    public function savingTransaction()
    {

        /* save order detail after order is created from frontend */
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);

        // Insert columns.
        $columns = array(
            'order_id',
            'order_number',
            'paymentmethod_id',
            'amount',
            'created_on',
            'status',
            'mode',
            'txnid',
            'payment_name'
        );

        // Insert values.
        $values = array(
            $db->quote($_REQUEST['order_id']),
            $db->quote($_REQUEST['order_number']),
            $db->quote($_REQUEST['method_id']),
            $db->quote($_REQUEST['amount']),
            $db->quote(date('Y-m-d h:i:s')),
            $db->quote('created'),
            $db->quote($_REQUEST['currency']),
            $db->quote($_REQUEST['txnid']),
            $db->quote("Paylike")
        );

        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__hikashop_payment_plg_paylike'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);
        $db->execute();

        $history = new stdClass();
        $email = new stdClass();
        $history->notified = 1;
        $history->amount = $_REQUEST['amount'];
        $history->data = ob_get_clean();
        $email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paylike', 'Confirmed', $_REQUEST['order_number']);
        $body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Paylike', 'Confirmed')).' '.JText::sprintf('ORDER_STATUS_CHANGED', 'Confirmed')."\r\n\r\n";
        $email->body = $body;

        $order = $this->getOrder($_REQUEST['order_id']);
        $this->loadPaymentParams($order);

        $this->modifyOrder($_REQUEST['order_id'], $this->payment_params->confirmed_status, $history, $email);

        // try to clear cart
        $class = hikashop_get('class.cart');
        $class->cleanCartFromSession();

        // check if payment delayed will ignore capture
        if ($this->payment_params->instant_mode=="Delayed") {
            return;
        }

        if ($order->order_status != $this->payment_params->confirmed_status) {
            // capture payment
            if ($_REQUEST['amount'] > 0) {
                $data        = array(
                        'amount'   => get_paylike_amount($_REQUEST['amount'], $_REQUEST['currency']),
                        'currency' => $_REQUEST['currency']
                    );
                \Paylike\Client::setKey($this->payment_params->private_key);
                $response = \Paylike\Transaction::capture($_REQUEST['txnid'], $data);

                if ($response['transaction']['capturedAmount'] > 0):
                    // update status to capture
                    $sql ="update #__hikashop_payment_plg_paylike set status='captured' where order_id='$order->order_id'";
                $db->setQuery($sql);
                $db->execute();
                endif;
            }
        }
    }

    public function orderHistoryURL()
    {
        $db = & JFactory::getDBO();
        $db->setQuery("select * from #__menu where link like '%com_hikashop&view=user&layout=cpanel%'");
        $row = $db->loadObject();
        if ($row->id) {
            return JRoute::_('index.php?Itemid='.$row->id);
        } else {
            return JRoute::_('index.php?option=com_hikashop&view=user&layout=cpanel');
        }
    }
}
