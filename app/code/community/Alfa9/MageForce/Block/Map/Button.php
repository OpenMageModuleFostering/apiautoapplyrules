<?php
class Alfa9_MageForce_Block_Map_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
		protected $entity;
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->entity = strtolower ( substr (
        		get_class ( $this ),
        		1 + strrpos ( get_class ( $this ), '_' )
        ) );
        Mage::getDesign()->setTheme('template', 'alfa9');
        $this->setTemplate('a9mageforce/map/button.phtml');
    }
    
    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
    	$button = $this->getLayout()->createBlock('adminhtml/widget_button')
    	->setData(array(
    			'id'        => 'a9mageforce_map_'.$this->entity.'_a9mageforce_entity_create_object',
    			'label'     => $this->helper('adminhtml')->__('Create Object in SalesForce'),
    			'onclick'   => 'javascript:'.$this->entity.'_sync(); return false;'
    	));
    
    	return $button->toHtml();
    }
    
    /**
     * Remove scope label
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getSyncUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/object/sync');
    }
}
