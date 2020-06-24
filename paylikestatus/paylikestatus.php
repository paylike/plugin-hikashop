<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

include_once( JPATH_SITE.DS.'plugins/hikashoppayment/paylike/Paylike/Client.php' );

class plgHikashopPaylikeStatus extends JPlugin
{
	var $message = '';

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->order = hikashop_get('class.order');
		$this->db = JFactory::getDBO();
	}


	function onAfterOrderUpdate(&$order,&$send_email){

		$db = JFactory::getDbo();

		$o = clone $order;
		if(!empty($order->old)) {
			if($order->old->order_status!=$order->order_status && $order->order_status=="refunded")
			{
				$this->payLikeRefunded($order);
			}
			if($order->old->order_status!=$order->order_status && $order->order_status=="shipped")
			{
				$this->payLikeCaptured($order);
			}
		}
	}

	function payLikeRefunded($order) {

		$db = JFactory::getDbo();

		$sql ="select * from #__hikashop_payment_plg_paylike where order_id='$order->order_id' limit 1";
		$db->setQuery($sql);
		$row = $db->loadObject();

		$txtid = $row->txnid;

		$params = $this->getPayLikeConfig();


		\Paylike\Client::setKey( $params->private_key );

		$response    = \Paylike\Transaction::fetch( $txtid );

		/* refund payment if already captured */
		if ( $response['transaction']['capturedAmount'] > 0 ) {
				$amount   = $response['transaction']['capturedAmount'];
				$data     = array(
					'amount'     => $amount,
					'descriptor' => ""
				);
				$response = \Paylike\Transaction::refund( $txtid, $data );
				// update payment to refunded
				$sql ="update #__hikashop_payment_plg_paylike set status='refunded' where order_id='$order->order_id'";
		} else {
				/* void payment if not already captured */
				$data     = array(
					'amount' => $response['transaction']['amount']
				);
				$response = \Paylike\Transaction::void( $txtid, $data );
				// update payment to voided
				$sql ="update #__hikashop_payment_plg_paylike set status='voided' where order_id='$order->order_id'";
		}

		$db->setQuery( $sql );
		$db->execute();

	}

	function payLikeCaptured($order) {

		$db = JFactory::getDbo();

		$sql ="select * from #__hikashop_payment_plg_paylike where order_id='$order->order_id' limit 1";
		$db->setQuery($sql);
		$row = $db->loadObject(); 

		$txtid = $row->txnid;

		$params = $this->getPayLikeConfig(); 


		\Paylike\Client::setKey( $params->private_key );

		$response    = \Paylike\Transaction::fetch( $txtid );



		/* refund payment if already captured */
		if ( $response['transaction']['capturedAmount'] > 0 ) {
				// already captured
		} else {
			$data        = array(
				'amount'   => get_paylike_amount( $row->amount, $row->mode),
				'currency' => $row->mode
			);
			\Paylike\Client::setKey( $params->private_key );
			$response = \Paylike\Transaction::capture( $txtid, $data );

			if($response['transaction']['capturedAmount'] > 0):
				$sql ="update #__hikashop_payment_plg_paylike set status='captured' where order_id='$order->order_id'";
				$db->setQuery( $sql );
				$db->execute();
			endif;
		}

		
	}

	function getPayLikeConfig() {

		$db = JFactory::getDbo();

		$db->setQuery("select * from #__hikashop_payment where payment_type='paylike' ");
		$row = $db->loadObject();

		$params = hikashop_unserialize($row->payment_params);

		return $params;


	}



}
