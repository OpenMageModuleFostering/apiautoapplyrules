<?php
/**
 * @category  	Alfa9
 * @package   	Alfa9_MageForce
 * @author 			ctubio (ctubio@alfa9.com)
 *
 */
class Alfa9_MageForce_ObjectController extends Mage_Adminhtml_Controller_Action
{
	
	public function syncAction()
	{
    if (!isset($_REQUEST['entity'])) {
      return;
    }
      
    Mage::helper('a9mageforce')->{'sync'.ucwords($_REQUEST['entity'])}($_REQUEST['entity']);
	}
}