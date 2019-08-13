<?php
/**
 * Order Observer Model
 *
 * @category    Model
 * @package     Alfa9
 * @author      ctubio <ctubio@alfa9.com>
 */

class Alfa9_VentasEnGrupo_Model_Observer_Product extends Mage_Payment_Model_Method_Abstract {

	public function catalogProductIsSalableAfter($observer) {
    if ($observer->getSalable()->getIsSalable())
      $observer->getSalable()->setIsSalable(
        Mage::helper('checkout')->canOnepageCheckout()
      );
    return $this;
	}
  
}
?>