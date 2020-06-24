<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][private_key]"><?php
			echo JText::_( 'HIKASHOP_PRIVATE_KEY' );
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][private_key]" value="<?php echo $this->escape(@$this->element->payment_params->private_key); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][public_key]"><?php
			echo JText::_( 'HIKASHOP_PUBLIC_KEY' );
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][public_key]" value="<?php echo $this->escape(@$this->element->payment_params->public_key); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][order_status]"><?php
			echo JText::_('HIKASHOP_PENDING_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][order_status]", @$this->element->payment_params->order_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][confirmed_status]"><?php
			echo JText::_('HIKASHOP_CONFIRMED_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][confirmed_status]", @$this->element->payment_params->confirmed_status);
	?></td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][instant_mode]"><?php echo JText::_('HIKASHOP_INSTANT_MODE');?></label>
	</td>
	<td><?php
		$arr = array(
			JHTML::_('select.option', 'Instant', 'Instant' ),
			JHTML::_('select.option', 'Delayed', 'Delayed' ),
		);
		echo JHTML::_('hikaselect.genericlist',  $arr, "data[payment][payment_params][instant_mode]", '', 'value', 'text', @$this->element->payment_params->instant_mode);
		?>
		</td>
</tr>

<tr>
	<td class="key">
		<label for="data[payment][payment_params][test_mode]"><?php echo JText::_('TEST_MODE');?></label>
	</td>
	<td><?php
		if(!isset($this->element->payment_params->test_mode))
			$this->element->payment_params->test_mode = 1;
		echo JHTML::_('hikaselect.booleanlist', "data[payment][payment_params][test_mode]" , '', $this->element->payment_params->test_mode);
	?></td>
</tr>
