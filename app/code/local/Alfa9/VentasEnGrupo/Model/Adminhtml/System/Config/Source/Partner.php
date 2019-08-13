<?php
class Alfa9_VentasEnGrupo_Model_Adminhtml_System_Config_Source_Partner extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
      if (is_null($this->_options)) {
        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $queryResult=$db->query("SELECT mageuserid, (SELECT value from customer_entity_varchar where entity_id = mageuserid and attribute_id = (select attribute_id from eav_attribute where attribute_code='compania' AND entity_type_id = '1')) as name FROM ".Mage::getConfig()->getTablePrefix()."marketplacepartner_entity_userdata as meu JOIN customer_entity AS ce WHERE meu.partnerstatus = 'Seller' AND ce.entity_id = meu.mageuserid ORDER BY firstname;");
        $rows = $queryResult->fetchAll();
        /*
         * Comentado por el Ticket #2190: En el BO el filtro de catálogo por productor YOCOMPROSANO
         * no funciona. http://soporte.alfa9.com/issues/2190
         *
        $this->_options[] = array(
          'label' => 'YoComproSano',
          'value' =>  ''
        );
        */
        foreach($rows as $row)
          $this->_options[] = array(
            'label' => $row['name'],
            'value' =>  $row['mageuserid']
          );
      }
      return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArrayWithEmpty()
    {
        $_options = $this->getOptionArray();
        $_options[''] = Mage::helper('catalog')->__('Any');
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

}
