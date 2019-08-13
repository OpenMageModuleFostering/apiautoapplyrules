<?php
/**
 * Order Observer Model
 *
 * @category    Model
 * @package     Alfa9
 * @author      ctubio <ctubio@alfa9.com>
 */

class Alfa9_VentasEnGrupo_Model_Observer_Order extends Mage_Payment_Model_Method_Abstract {

	public function salesOrderSaveAfter( $observer ) {
		if ( Mage::getStoreConfig( 'a9veg/notify/active' ) ) {
			$order = $observer->getEvent()->getOrder();
			if ($order->getStatus() == Mage::getStoreConfig('a9veg/notify/status')){
				Mage::helper('a9veg/order')->notify($order);
      }
		}
	}
  
}
?>