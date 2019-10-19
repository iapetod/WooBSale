<?php


add_action( 'init', 'register_pack_product_type' );

function register_pack_product_type() {

  class WC_Product_Pack extends WC_Product {
    public function __construct( $product = null) {
        $this->product_type = 'pack';
        parent::__construct( $product );
    }
    public function get_price($context = 'view'){
      $price = get_post_meta( $this->get_id(), 'pack_product_price' ,true);
      return $price;
    }
    public function get_type(){
      return 'pack';
    }
  }
}
add_filter( 'product_type_selector', 'add_pack_product_type' );

function add_pack_product_type( $types ){
    $types[ 'pack' ] = __( 'Producto Pack', 'woocommerce' );
    return $types;
}
add_filter( 'woocommerce_product_data_tabs', 'pack_product_tab' );
function pack_product_tab( $tabs) {

    $tabs['pack'] = array(
      'label'    => __( 'Pack', 'dm_product' ),
      'target' => 'pack_product_options',
      'class'  => 'show_if_pack',
     );
    //$tabs['attribute']['class'][] = 'show_if_pack';
    //$tabs['inventory']['class'][] = 'show_if_pack';
    //$tabs['advanced']['class'][] = 'show_if_pack';

    return $tabs;
}
add_action( 'woocommerce_product_data_panels', 'pack_product_tab_product_tab_content' );
function pack_product_tab_product_tab_content() {
  global $post,$wpdb;
  $products = get_post_meta( $post->ID, 'pack_product_bundles' ,true);
 ?><div id='pack_product_options' class='panel woocommerce_options_panel'><?php
 ?>
 <div class='options_group'><?php

    woocommerce_wp_text_input(
      array(
        'id' => 'pack_product_price',
        'label' => __( 'Precio del paquete', 'woocommerce' ),
        'placeholder' => '',
        'desc_tip' => 'true',
        'description' => __( 'Enter Pack product Info.', 'dm_product' ),
        'type' => 'text'
      )
    );
 ?>
   <div style="padding:20px;">
     <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
          <td id="cb" class="manage-column column-cb check-column"></td>
          <th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><span>Nombre</span></th>
          <th scope="col" id="sku" class="manage-column column-sku sortable desc"><span>Tipo</span></th>
          <th scope="col" id="is_in_stock" class="manage-column column-is_in_stock">Cantidad</th>
          <th scope="col" id="price" class="manage-column column-price sortable desc"><span>Precio</span></th>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php
        foreach($products as $p):
          $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_id' AND meta_value='%s' LIMIT 1", $p['id'] ) );
          $_product = wc_get_product($product_id);
        ?>
        <tr class="iedit author-self level-0 type-product status-publish hentry">
            <th scope="row" class="check-column">
            </th>
            <td class="name column-name has-row-actions column-primary" data-colname="Nombre">
              <strong><?php echo $_product->get_name();?></strong>
              <div class="row-actions">
                <span class="edit"><a href="http://localhost:8080/wp-admin/post.php?post=2282&amp;action=edit" aria-label="Editar «Vírgen s/ Niño Jesús»">Ver</a> | </span>
                <span class="trash"><a href="http://localhost:8080/wp-admin/post.php?post=2282&amp;action=trash&amp;_wpnonce=a61e2bd530" class="submitdelete" aria-label="Mover «Vírgen s/ Niño Jesús» a la papelera">Eliminar</a> | </span>
                <span class="duplicate"><a href="http://localhost:8080/wp-admin/edit.php?post_type=product&amp;action=duplicate_product&amp;post=2282&amp;_wpnonce=9acf899ba8" aria-label="Hacer un duplicado de este producto." rel="permalink">Editar</a></span>
              </div>
              <button type="button" class="toggle-row"><span class="screen-reader-text">Mostrar más detalles</span></button>
            </td>
            <td class="sku column-sku" data-colname="SKU"><?php echo $_product->get_type();?></td>
            <td class="is_in_stock column-is_in_stock" data-colname="Inventario"><?php echo $p['quantity'];?></td>
            <td class="price column-price" data-colname="Precio">
              <span class="woocommerce-Price-amount amount">
                <?php echo $_product->get_price_html();?>
              </span>
            </td>
        </tr>
        <?php
        endforeach;
        ?>
        </tbody>
        <tfoot>
        <tr>
          <td class="manage-column column-cb check-column"></td>
          <th scope="col" class="manage-column column-name column-primary sortable desc"><span>Nombre</span></th>
          <th scope="col" class="manage-column column-sku sortable desc"><span>SKU</span></th>
          <th scope="col" class="manage-column column-is_in_stock">Cantidad</th>
          <th scope="col" class="manage-column column-price sortable desc"><span>Precio</span><span class="sorting-indicator"></span></th>
        </tr>
        </tfoot>

      </table>
   </div>
 </div>
 </div><?php
}
add_action( 'woocommerce_process_product_meta', 'save_pack_product_settings' );
function save_pack_product_settings( $post_id ){
    $pack_product_price = $_POST['pack_product_price'];
    if( !empty( $pack_product_price ) ) {
      update_post_meta( $post_id, 'pack_product_price', esc_attr( $pack_product_price ) );
    }
}

add_action( 'woocommerce_single_product_summary', 'pack_product_front' );
function pack_product_front () {
    global $product,$wpdb;
    if ( 'pack' == $product->get_type() ) {
       $products = ( get_post_meta( $product->get_id(), 'pack_product_bundles' ,true) );
       if($products!=null){
         foreach($products as $p){
           echo '<div style="border:1px solid black;padding:10px;border-radius:4px;margin:10px;">';
           $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_id' AND meta_value='%s' LIMIT 1", $p['id'] ) );
           $_product = wc_get_product($product_id);
           //var_dump($_product->product_type);
           if($_product->get_type()==='simple'){
             //echo '<h4>'.$_product->get_description().'</h4>';
             echo '<h4>'.$_product->get_description().'</h4>';
           }
           if($_product->get_type()==='variable'){
             echo '<h4>'.$_product->get_name().'</h4>';
             $variations = ($_product->get_available_variations());
             echo '<select class="form-control pack-selector" ref="pack-product-id-'.$_product->get_id().'">';
             foreach($variations as $variation){
               //var_dump($variation["sku"]);
               //var_dump($variation["attributes"]['attribute_bsalevar']);
               echo '<option value="'.$variation["variation_id"].'">'.strtoupper($variation["attributes"]['attribute_bsalevar']).'</option>';
             }
             echo '</select>';
           }
           echo '</div>';
         }
       }
    }
}


function load_custom_wp_admin_style() {
  global $post;
  if($post->post_type=='product'){
    wp_register_script('pack_product_script', BSALE_MAIN_URL. '/assets/js/pack.product.js', array('jquery'), '1', true );
    wp_enqueue_script('pack_product_script');
    wp_localize_script('pack_product_script','bsale_vars',['ajaxurl'=>admin_url('admin-ajax.php')]);
  }
}
add_action( 'wp_enqueue_scripts', 'load_custom_wp_admin_style' );


add_action('wp_ajax_nopriv_pack_product_search','pack_product_search');
add_action('wp_ajax_pack_product_search','pack_product_search');
function pack_product_search(){
  $search = $_POST['search'];
  $args = array("post_type" => "product", "s" => $search);
  $query = get_posts( $args );
  $data = [];
  foreach($query as $q){
    $pr = wc_get_product($q->ID);
    if($pr->product_type!="pack"){
      $data [] = ["id"=>$pr->get_id(),"name"=>$pr->get_name(),"product_type"=>$pr->product_type];
    }
  }
  echo json_encode($data);
  wp_die();
}


add_filter('woocommerce_product_class','pack_product_woocommerce_product_class',10,2);
function pack_product_woocommerce_product_class($classname,$product_type){
  if($product_type=='pack'){
    $classname = 'WC_Product_Pack';
  }
  return $classname;
}


add_action( 'woocommerce_pack_add_to_cart', 'bsale_pack_info_before_add_to_cart_form', 30 );

function bsale_pack_info_before_add_to_cart_form() {
  global $product;

  if ( ! $product->is_purchasable() ) {
  	return;
  }

  echo wc_get_stock_html( $product ); // WPCS: XSS ok.

  if ( $product->is_in_stock() ) : ?>

  	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

  	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
  		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
      <?php
      global $wpdb;
      $products = ( get_post_meta( $product->get_id(), 'pack_product_bundles' ,true) );
      if($products!=null){
        foreach($products as $p){
          $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_id' AND meta_value='%s' LIMIT 1", $p['id'] ) );
          $_product = wc_get_product($product_id);
          if($_product->get_type()=='simple'){
            ?>
            <input type="text" style="display:none" name="pack_selection[]" value="<?php echo $_product->get_id();?>">
            <?php
          }
          if($_product->get_type()=='variable'){
            $variations = ($_product->get_available_variations());
            ?>
            <input type="text" style="display:none" name="pack_selection[]" id="pack-product-id-<?php echo $_product->get_id();?>"value="<?php echo $variations[0]['variation_id'];?>">
            <?php
          }
        }
      }
      ?>
  		<?php
  		do_action( 'woocommerce_before_add_to_cart_quantity' );

  		woocommerce_quantity_input( array(
  			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
  			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
  			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
  		) );

  		do_action( 'woocommerce_after_add_to_cart_quantity' );
  		?>

  		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

  		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
  	</form>

  	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

  <?php endif;
}




// Store custom data in cart item
add_action( 'woocommerce_add_cart_item_data','pack_save_custom_data_in_cart', 20, 2 );
function pack_save_custom_data_in_cart( $cart_item_data, $product_id ) {
  if( isset( $_POST['pack_selection'] ) ){
    $cart_item_data['pack_selection'] = array(
        'label' => __('Pack'),
        'value' => json_encode( $_POST['pack_selection'] ),
    );
  }
  return $cart_item_data;
}

// Display item custom data in cart and checkout pages
add_filter( 'woocommerce_get_item_data', 'pack_render_custom_data_on_cart_and_checkout', 20, 2 );
function pack_render_custom_data_on_cart_and_checkout( $cart_data, $cart_item ){
    global $wpdb;
    $custom_items = array();

    if( !empty( $cart_data ) )
        $custom_items = $cart_data;

    if( isset( $cart_item['pack_selection'] ) ){
      $values = json_decode($cart_item['pack_selection']['value']);
      $pack = [];
      foreach($values as $val){
        $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_id' AND meta_value='%s' LIMIT 1", $val ) );
        $_product = wc_get_product($val);
        $pack[] = $_product->get_name();
      }
        $custom_items[] = array(
            'name'  => $cart_item['pack_selection']['label'],
            'value' => implode(' + ',$pack),
        );
    }


    return $custom_items;
}
