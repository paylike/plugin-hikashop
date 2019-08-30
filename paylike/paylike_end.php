<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
$v = new JVersion();
$version = $v->getShortVersion();

$v2 =& hikashop_config();
$hikashop_version=$v2->get('version');

$lang = JFactory::getLanguage();
$locale = substr($lang->getTag(),0,2);

?>
<div class="hikashop_paylike_end" id="hikashop_paylike_end">
	<div class="paylike" id="paylike_paying">
		<h3><?php echo Jtext::_('HIKASHOP_ORDER_CREATED');?></h3>
		<p><?php echo Jtext::_('HIKASHOP_ORDER_CREATED_INFO');?></p>
		<p><a class="btn btn-default" id="PayNow"><?php echo Jtext::_('HIKASHOP_ORDER_CREATED_BUTTON');?></a></p>
		<div id="token">
		<?php echo JHtml::_('form.token'); ?> 
		</div>
	</div>
	<div class="paylike" id="paylike_paid" style="display: none;">
		<h3><?php echo Jtext::_('HIKASHOP_ORDER_COMPLETED');?></h3>
		<p><a href="<?php echo $this->vars["history_url"];?>"><?php echo Jtext::_('HIKASHOP_ORDER_HISTORY');?></a></p>
	</div>
</div>

<script src="https://sdk.paylike.io/3.js"></script>
<script type="text/javascript">

	jQuery(document).ready(function(){
		var paylike = Paylike('<?php echo $this->vars["public_key"];?>');
		function pay(){
			paylike.popup({
				// locale: 'da',  // pin popup to a locale
				title: '<?php echo $this->vars["sitename"];?>',
				currency: '<?php echo $this->vars["currency"];?>',
				amount: (<?php echo $this->vars["paylike_amount"];?>),
				locale: '<?php echo $locale;?>',	
				// saved on transaction for retrieval from dashboard or API
				custom: {
					email: '<?php echo $this->vars["customer_email"];?>',
					orderId: '<?php echo $this->vars["order_id"];?>',
					// arrays are fine
					products: [
						// nested objects will do
						<?php echo $this->vars["custom"];?>

						],
					customer: {
                            name: '<?php echo $this->vars["customer_name"];?>',
                            email: '<?php echo $this->vars["customer_email"];?>',
                            phoneNo: '<?php echo $this->vars["customer_phone"];?>',
                            address: '<?php echo $this->vars["customer_address"];?>',
                            IP: '<?php echo $this->vars["customer_ip"];?>'
                            },
                    platform: {
                            name: 'Joomla',
                            version: '<?php echo $version;?>'
							},
					ecommerce: {
							name: 'Hikashop',
							version: '<?php echo $hikashop_version;?>'
							},	
					}
				
			}, function( err, res ){
				if (err)
					return console.log(err);

				//console.log(res);
				savingTransaction(res.transaction.id);
		
			});
		}

		jQuery("#PayNow").on("click",function(){
			pay();
		});

		pay();

		function savingTransaction(transaction_id) {

			var id='<?php echo $this->vars["order_id"];?>';
			var number='<?php echo $this->vars["order_number"];?>';
			var amount='<?php echo $this->vars["amount"];?>';
			var method_id='<?php echo $this->vars["method_id"];?>';
			var token = jQuery("#token input").attr("name");
			var currency = '<?php echo $this->vars["currency"];?>';
			var url = '<?php echo HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=paylike&tmpl=component&act=savingTransaction';?>';
			jQuery.ajax({
				type: "POST",
				url: url,
				data: {'order_id':id,'order_number':number,'txnid':transaction_id,'amount':amount,'method_id':method_id,token:1,'currency':currency},
				success:function(){
					jQuery("#paylike_paying").hide();
					jQuery("#paylike_paid").show();
				}
			})

		}


	});
		
</script>