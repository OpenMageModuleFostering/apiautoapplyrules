<?php
class Alfa9_VentasEnGrupo_Helper_Order extends Mage_Core_Helper_Abstract
{
  public function notify($order) {
    if (is_numeric($order)) $order = Mage::getModel('sales/order')->load($order);
		$notify = array();
		$items = $order->getItemsCollection();
		foreach ( $items as $item ) {
			$products = Mage::getModel('catalog/product')
				->getCollection()
				->addAttributeToSelect('partner')
				->addAttributeToFilter('entity_id', $item->getProductId())
				->load();
      foreach($products as $product) {
        if ($product->getPartner() and Mage::getModel('customer/customer')->load($product->getPartner())->getIsActive()) {
          if (!isset($notify[$product->getPartner()])) {
            $notify[$product->getPartner()] = array();
          }
          $notify[$product->getPartner()][] = array(
            $item->getProductId() => $item->getQtyOrdered()
          );
        }
      }
    }
    
		if (count($notify)) {
			foreach ( $notify as $partnerId => $items ) {
				$this->sendEmail($partnerId, $items, $order);
			}
		}
	}
  
	private function sendEmail($partnerId, $items, $order) {
		$html = '';
    
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$queryResult=$db->query("SELECT email, CONCAT(firstname, ' ',lastname) as name FROM ".Mage::getConfig()->getTablePrefix()."marketplacepartner_entity_userdata WHERE mageuserid='".$partnerId."';");
		$partner = $queryResult->fetch();
		
		foreach ( $items as $item ) {
      foreach ( $item as $product_id => $qty ) {
        $_product = Mage::getModel( 'catalog/product' )->load( $product_id );
        $html .= '<tr><td valign="top" style="font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA;">'. $_product->getName() .' ( '. $_product->getSku() .' )</td>';
        $html .= '<td align="center" valign="top" style="font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;">'. number_format( $qty, 2 ) .'</td></tr>';
      }
		}
		
		if ( $html != '' ) {
			$to_email = $partner['email'];
			$to_name = $partner['name'];
			
			$email_template = Mage::getModel( 'core/email_template' )->loadDefault('a9_partner_email');
			
			try {
				$email_content = utf8_decode($email_template->getProcessedTemplate( array(
        'partner' => Mage::getModel('customer/customer')->load($partnerId),
        'title_order' => 'Detalle del Pedido Número',
        'title_billing' => 'Datos de Facturación del Cliente:',
        'title_shipping' => 'Dirección del Grupo de Consumo:',
        'order' => $order, 
        'store' => Mage::app()->getStore(), 
        'items_html' => $html
        ) ));
        
				$email_subject = $email_template->getProcessedTemplateSubject( array( 'order' => $order, 'store' => Mage::app()->getStore(), 'items_html' => $html ) );
				$mail = Mage::getModel( 'core/email' );
				$mail->setToName( $to_name );
        $forcedTo = Mage::getStoreConfig('a9veg/notify/email');
				$mail->setToEmail( $forcedTo?$forcedTo:$to_email );
				$mail->setBody( $email_content );
				$mail->setSubject( $email_subject );
				$mail->setType( 'html' );
			  $mail->setFromName(Mage::app()->getStore()->getName());
			  $mail->setFromEmail(Mage::getStoreConfig('trans_email/ident_sales/email'));
				$mail->send();
			}
			catch( Exception $e ) {}
		}
	}
}