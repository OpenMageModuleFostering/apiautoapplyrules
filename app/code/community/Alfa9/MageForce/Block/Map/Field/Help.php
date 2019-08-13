<?php
class Alfa9_MageForce_Block_Map_Field_Help
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);

        $html.= Mage::getSingleton('core/layout')->createBlock('core/template')->setTemplate('a9mageforce/map/help.phtml')->toHtml();

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}
