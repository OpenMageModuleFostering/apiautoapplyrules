<?php
class Alfa9_VentasEnGrupo_Block_Adminhtml_Catalog_Product_Grid extends TBT_Enhancedgrid_Block_Catalog_Product_Grid
{

    protected function _prepareColumns() {
        // Loads all the column options for each applicable column.
        foreach ($this->columnSettings as $col => $true) {
            $this->loadColumnOptions( $col );
        }
        
        $store = $this->_getStore();
        if ( $this->colIsVisible( 'id' ) ) {
            $this->addColumn( 'id', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'ID' ), 
                    'width' => '50px', 
                    'type' => 'number', 
                    'index' => 'entity_id'
                ) );
        }
        
        $imgWidth = Mage::getStoreConfig( 'enhancedgrid/images/width' ) + "px";
        
        if ( $this->colIsVisible( 'thumbnail' ) ) {
            $this->addColumn( 'thumbnail', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Thumbnail' ), 
                    'type' => 'image', 
                    'width' => $imgWidth, 
                    'index' => 'thumbnail'
                ) );
        }
        if ( $this->colIsVisible( 'small_image' ) ) {
            $this->addColumn( 'small_image', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Small Img' ), 
                    'type' => 'image', 
                    'width' => $imgWidth, 
                    'index' => 'small_image'
                ) );
        }
        if ( $this->colIsVisible( 'image' ) ) {
            $this->addColumn( 'image', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Image' ), 
                    'type' => 'image', 
                    'width' => $imgWidth, 
                    'index' => 'image'
                ) );
        }
        
        if ( $this->colIsVisible( 'name' ) ) {
            $this->addColumn( 'name', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Name' ), 
                    'index' => 'name'
                )//                    'width' => '150px'
                 );
        }
        if ( $this->colIsVisible( 'name' ) ) {
            if ( $store->getId() ) {
                $this->addColumn( 'custom_name', 
                    array(
                        'header' => Mage::helper( 'catalog' )->__( 'Name In %s', $store->getName() ), 
                        'index' => 'custom_name', 
                        'width' => '150px'
                    ) );
            }
        }
        
        if ( $this->colIsVisible( 'type_id' ) ) {
            $this->addColumn( 'type', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Type' ), 
                    'width' => '60px', 
                    'index' => 'type_id', 
                    'type' => 'options', 
                    'options' => Mage::getSingleton( 'catalog/product_type' )->getOptionArray()
                ) );
        }
        
        if ( $this->colIsVisible( 'attribute_set_id' ) ) {
            $sets = Mage::getResourceModel( 'eav/entity_attribute_set_collection' )->setEntityTypeFilter( 
                Mage::getModel( 'catalog/product' )->getResource()
                    ->getTypeId() )
                ->load()
                ->toOptionHash();
            
            $this->addColumn( 'set_name', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Attrib. Set Name' ), 
                    'width' => '100px', 
                    'index' => 'attribute_set_id', 
                    'type' => 'options', 
                    'options' => $sets
                ) );
        }
        
        if ( $this->colIsVisible( 'sku' ) ) {
            $this->addColumn( 'sku', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'SKU' ), 
                    'width' => '80px', 
                    'index' => 'sku'
                ) );
        }
        
        if ( $this->colIsVisible( 'price' ) ) {
            $this->addColumn( 'price', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Price' ), 
                    'type' => 'price', 
                    'currency_code' => $store->getBaseCurrency()
                        ->getCode(), 
                    'index' => 'price'
                ) );
        }
        
        if ( $this->colIsVisible( 'qty' ) ) {
            $this->addColumn( 'qty', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Qty' ), 
                    'width' => '100px', 
                    'type' => 'number', 
                    'index' => 'qty'
                ) );
        }
        
        if ( $this->colIsVisible( 'visibility' ) ) {
            $this->addColumn( 'visibility', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Visibility' ), 
                    'width' => '70px', 
                    'index' => 'visibility', 
                    'type' => 'options', 
                    'options' => Mage::getModel( 'catalog/product_visibility' )->getOptionArray()
                ) );
        }
        
        if ( $this->colIsVisible( 'status' ) ) {
            $this->addColumn( 'status', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Status' ), 
                    'width' => '70px', 
                    'index' => 'status', 
                    'type' => 'options', 
                    'options' => Mage::getSingleton( 'catalog/product_status' )->getOptionArray()
                ) );
        }
        
        if ( $this->colIsVisible( 'websites' ) ) {
            if ( ! Mage::app()->isSingleStoreMode() ) {
                $this->addColumn( 'websites', 
                    array(
                        'header' => Mage::helper( 'catalog' )->__( 'Websites' ), 
                        'width' => '100px', 
                        'sortable' => false, 
                        'index' => 'websites', 
                        'type' => 'options', 
                        'options' => Mage::getModel( 'core/website' )->getCollection()
                            ->toOptionHash()
                    ) );
            }
        }
        
        if ( $this->colIsVisible( 'categories' ) ) {
            $this->addColumn( 'categories', 
                array(
                    'header' => Mage::helper( 'catalog' )->__( 'Categories' ), 
                    'width' => '100px', 
                    'sortable' => true, 
                    'index' => 'categories', 
                    'sort_index' => 'category', 
                    'filter_index' => 'category'
                ) );
        }
        
        $this->_addVariableColumns();
           
           if (Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleId()<5 and $this->getCollection())
        $this->addColumn( 'action', 
            array(
                'header' => Mage::helper( 'catalog' )->__( 'Action' ), 
                'width' => '50px', 
                'type' => 'action', 
                'getter' => 'getId', 
                'actions' => array(
                    array(
                        'caption' => Mage::helper( 'catalog' )->__( 'Edit' ), 
                        'id' => "editlink", 
                        'url' => array(
                            'base' => 'adminhtml/*/edit', 
                            'params' => array(
                                'store' => $this->getRequest()
                                    ->getParam( 'store' )
                            )
                        ), 
                        'field' => 'id'
                    )
                ), 
                'filter' => false, 
                'sortable' => false, 
                'index' => 'stores'
            ) );
        
        $this->addRssList( 'rss/catalog/notifystock', Mage::helper( 'catalog' )->__( 'Notify Low Stock RSS' ) );

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    public function getRowUrl($row) {
        //@nelkaake -m 16/11/10: Changed to use _getStore function
        return (Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleId()<5)?$this->getUrl( 'adminhtml/catalog_product/edit', 
            array(
                'store' => $this->_getStore(), 
                'id' => $row->getId()
            ) ):NULL;
    }
    
    
    protected function _prepareMassaction()
    {
        if (Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleId()<5) {
          return parent::_prepareMassaction();
        }
      
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $visibility = Mage::getSingleton('catalog/product_visibility')->getOptionArray();
        $visibility =  array_merge(array_slice($visibility,0,1),array_slice($visibility,-1));
        array_unshift($visibility, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('visibility', array(
             'label'=> Mage::helper('catalog')->__('Change visibility'),
             'url'  => $this->getUrl('*/*/massVisibility', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'visibility',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Visibility'),
                         'values' => $visibility
                     )
             )
        ));
        
        return $this;
    }
}