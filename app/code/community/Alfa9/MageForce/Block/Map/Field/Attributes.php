<?php
class Alfa9_MageForce_Block_Map_Field_Attributes extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('magento', array(
            'label' => Mage::helper('adminhtml')->__('Magento Attributes'),
            'style' => 'width:200px',
        ));
        $this->addColumn('salesforce', array(
            'label' => Mage::helper('adminhtml')->__('SalesForce Fields'),
            'style' => 'width:200px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Field Mapping');
        parent::__construct();
    }
    
    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }
        
        return '<select id="' . $inputName . '" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
            ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
            (isset($column['class']) ? $column['class'] : 'input-text custgroup required-entry') . '"'.
            (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '>'
            .$this->getDataKeys($columnName, substr($inputName, strpos($inputName, 'groups[map_')+11, strpos($inputName, '][fields][ua_attr][value]')-11))
            .'<\/select><script>$("'.$inputName.'").value = "#{' . $columnName . '}";<\/script>';
    }
    
    public function getDataKeys($platform, $entity) {
      $attributes = array();
      try {
        $attributes = Mage::helper('a9mageforce')->{'getDataKeysFrom'.ucfirst($platform)}($entity);
        sort($attributes);
      } catch (Exception $e) {
          Mage::logException($e);
      }
      $options = '<option value=""><\/option>';;
      foreach($attributes as $attribute)
        $options .= '<option value="'.$attribute.'">'.$attribute.'<\/option>';
      return $options;
    }
}
