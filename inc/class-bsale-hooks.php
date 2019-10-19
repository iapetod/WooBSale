<?php


add_filter('woocommerce_before_checkout_billing_form', 'bsale_woocommerce_add_checkout_selector');

function bsale_woocommerce_add_checkout_selector(){
  $bill = (WC()->session->get("bsale_selector_bill"));
  ?>
  <div class="container">
    <div class="row">
      <div class="col-4" style="display:block;margin:3px;border:1px solid silver;padding:10px;">
        <div class="form-check">
          <input class="form-check-input bsale-check-type" type="radio" name="bsale_selector" value="boleta" <?php if($bill=='boleta'){echo 'checked';}?>>
          <label class="form-check-label">
            Boleta
          </label>
        </div>
      </div>
      <div class="col-4" style="display:block;margin:3px;border:1px solid silver;padding:10px;">
        <div class="form-check">
          <input class="form-check-input bsale-check-type" type="radio" name="bsale_selector" value="factura"<?php if($bill=='factura'){echo 'checked';}?>>
          <label class="form-check-label">
            Factura
          </label>
        </div>
      </div>
    </div>
  </div>
  <?php
}

add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );

// Our hooked in function - $address_fields is passed via the filter!
function custom_override_default_address_fields( $address_fields ) {
     $address_fields['address_1']['label'] = "Dirección de la calle (sin puntos ni guión)";
     return $address_fields;
}

add_filter( 'woocommerce_billing_fields' , 'bsale_woocommerce_billing_fields' );
function bsale_woocommerce_billing_fields($fields){
  if(WC()->session->get("bsale_selector_bill")=='factura'){
    $fields['billing_rut'] = array(
        'label' => __('RUT', 'woocommerce'), // Add custom field label
        'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'priority' => 1,
        'class' => array('my-css')    // add class name
    );
    $fields['billing_concept'] = array(
        'label' => __('Giro', 'woocommerce'), // Add custom field label
        'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'priority' => 2,
        'class' => array('my-css')    // add class name
    );
    $fields['billing_name'] = array(
        'label' => __('Razón Social', 'woocommerce'), // Add custom field label
        'placeholder' => _x('', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'clear' => false, // add clear or not
        'type' => 'text', // add field type
        'priority' => 3,
        'class' => array('my-css')    // add class name
    );
  }
  $fields['billing_address_1']['label'] = "Dirección de la calle (sin puntos ni guión)";
    return $fields;
}

add_action('woocommerce_checkout_process', 'customize_checkout_field_process');
function customize_checkout_field_process(){
  $chosen = WC()->session->get('type_choice');
  $chosen = empty( $chosen ) ? WC()->checkout->get_value('type_choice') : $chosen;
  $chosen = empty( $chosen ) ? 'personal' : $chosen;
  if($chosen=='company'){
    if (!$_POST['bsale_field_rut']) wc_add_notice(__('Por favor ingresa el RUT') , 'error');
    if (!$_POST['bsale_field_name']) wc_add_notice(__('Por favor ingresa la Razón Social') , 'error');
    if (!$_POST['bsale_field_concept']) wc_add_notice(__('Por favor ingresa el giro') , 'error');
  }
}

add_action('woocommerce_checkout_update_order_meta', 'customize_checkout_field_update_order_meta');
function customize_checkout_field_update_order_meta($order_id){
  $chosen = WC()->session->get('type_choice');
  $chosen = empty( $chosen ) ? WC()->checkout->get_value('type_choice') : $chosen;
  $chosen = empty( $chosen ) ? 'personal' : $chosen;
  if($chosen=='company'){
    if (!empty($_POST['bsale_field_rut'])) {
      update_post_meta($order_id, 'bsale_field_rut', sanitize_text_field($_POST['bsale_field_rut']));
    }
    if (!empty($_POST['bsale_field_name'])) {
      update_post_meta($order_id, 'bsale_field_name', sanitize_text_field($_POST['bsale_field_name']));
    }
    if (!empty($_POST['bsale_field_concept'])) {
      update_post_meta($order_id, 'bsale_field_giro', sanitize_text_field($_POST['bsale_field_giro']));
    }
  }
}

function check_bsale_validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
    $product = wc_get_product($product_id);
    $stock=BSale::_check_stock($product_id);
    if($stock){
      $passed = true;
    }
    else{
      $passed = false;
      wc_add_notice( 'Disculpa! No puedes agregar "'.$product->get_name().'" porque ya que no se encuentra en stock'  , 'error' );
    }

    return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'check_bsale_validate_add_cart_item', 10, 5 );


add_action('woocommerce_checkout_create_order', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order( $order, $data ) {
    $percent = 0;
    foreach( $order->get_used_coupons() as $coupon_code ){
        $coupon_post_obj = get_page_by_title($coupon_code, OBJECT, 'shop_coupon');
        $coupon_id       = $coupon_post_obj->ID;
        $coupon = new WC_Coupon($coupon_id);
        if ( $coupon->get_discount_type() == 'percent' ){
            $percent = $coupon->get_amount();
        }
    }
    foreach ( $order->get_items() as $item_id => $item ) {
        if( $item['variation_id'] > 0 ){
            $product_id = $item['variation_id']; // variable product
        } else {
            $product_id = $item['product_id']; // simple product
        }
        $product = wc_get_product( $product_id );
        $variantId = get_post_meta($product_id,'_bsale_id',true);
        $products[]=[
          "variantId"=>$variantId,
          "quantity"=>$item['quantity']];
        $details[]=[
          "variantId"=>$variantId,
          "netUnitValue"=>wc_get_price_excluding_tax($product,[]),
          "quantity"=>$item['quantity'],
          "taxId"=>"[1]",
          "comment"=>$product->get_title(),
          "discount"=>$percent
        ];
    }

    /*$d=$this->post("consumptions",[
      "note"=>"test api consumptions",
      "officeId"=>get_option('bsale-office_id'),
      "details"=>$products
    ]);*/

    $documentType=1;
    if($bsale_field_rut!=""){
      $documentType=6;
      $client = [
        "name"=>$data["billing_first_name"].' '.$data["billing_last_name"],
        "code"=>$data['billing_rut'],
        "city"=>$data['billing_city'],
        "company"=> ($data['billing_company']!='')?$data['billing_company']:$data["billing_first_name"].' '.$data["billing_last_name"],
        "municipality"=>$data['billing_city'],
        "activity"=>$data['billing_concept'],
        "address"=>$data['billing_address_1']." ".$data['billing_address_2'],
        "email"=>$data['billing_email'],
        "isForeigner"=> 1
      ];
    }
    else{
      $documentType=1;
      $client = [
        "name"=>$data["billing_first_name"].' '.$data["billing_last_name"],
        "code"=> $data['billing_rut'],
        "city"=> $data['billing_city'],
        "company"=> ($data['billing_company']!='')?$data['billing_company']:$data["billing_first_name"].' '.$data["billing_last_name"],
        "municipality"=> $data['billing_city'],
        "address"=> $data['billing_address_1']." ".$data['billing_address_2'],
        "email"=> $data['billing_email'],
        "isForeigner"=> 1
      ];
      if($bsale_field_giro!=""){
        $client['activity'] = 'giro';
      }

    }
    $time =   time();
    $args=[
      "documentTypeId"=>$documentType,
      "officeId"=>1,
      "priceListId"=>3,
      "emissionDate"=>time(),
      "expirationDate"=>time(),
      "declareSii"=>1,
      "client"=>$client,
      "details"=>$details,
      "sendEmail"=>1,
      "payments"=>[
        [
          "paymentTypeId"=>10,
          "amount"=>$data["total"],
          "recordDate"=>$time
        ]
      ]
    ];
    $order->update_meta_data( '_bsale_document_id', json_encode($args) );
}

add_action('woocommerce_thankyou', 'bsale_order_document', 10, 1);
function bsale_order_document($order_id){
  if ( ! $order_id )
      return;
  $order = wc_get_order( $order_id );
  if($order->is_paid()){
    $paid = 'yes';
  }
  else{
    $paid = 'no';
  }
  $metas = $order->get_meta_data();
  $state = false;
  $_bsale_document='';
  $_document='';
  $flag=false;
  foreach($metas as $meta){
    if($meta->get_data()['key']=='_bsale_document_id'){
      $state = true;
      $_bsale_document = $meta->get_data()['value'];
    }
    if($meta->get_data()['key']=='_bsale_document_request'){
      $flag=true;
      $_document = json_decode($meta->get_data()['value']);
    }
  }

  $args = json_decode($_bsale_document);
  if(!$flag){
    $bsale = BSale::document($args);
    $_document = json_decode($bsale['request']);
    $order->update_meta_data( '_bsale_document_request', $bsale['request'] );
    $order->save();
  }
  if(!_BSALE_DEBUG){
    if($state):
    ?>
    <section class="woocommerce-order-details">
      <h2 class="woocommerce-order-details__title">Detalles de Boleta Electronica</h2>
      <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <thead>
          <tr>
            <th class="woocommerce-table__product-name product-name">Boleta <?php echo $_document->number;?></th>
          </tr>
        </thead>
        <tbody>
          <tr class="woocommerce-table__line-item order_item">
            <td class="woocommerce-table__product-name product-name">
              <a href="<?php echo $_document->urlPdfOriginal;?>"> Ver Boleta</a>
            </td>
          </tr>
        </tbody>
        <tfoot>
        </tfoot>
      </table>

      </section>
    <?php
    endif;
  }
  else{
    ?>
    <section class="woocommerce-order-details">
      <h2 class="woocommerce-order-details__title">Detalles de Boleta Electronica</h2>
      <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <thead>
          <tr>
            <th class="woocommerce-table__product-name product-name">Boleta <?php echo $_document->number;?></th>
          </tr>
        </thead>
        <tbody>
          <tr class="woocommerce-table__line-item order_item">
            <td class="woocommerce-table__product-name product-name">
              <a href="<?php echo $_document->urlPdfOriginal;?>"> Ver Boleta</a>
            </td>
          </tr>
        </tbody>
        <tfoot>
        </tfoot>
      </table>

      </section>
    <?php

  }

}
