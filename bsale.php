<?php
/**
 * Plugin Name: WooCommerce BSale
 * Description: Plugin para la integración BSALE
 * Version: 1.1.0
 * Author: Feroz Digital Agency - Jesus Marcano
 * Author URI: http://ferozdigital.cl/
 * Developer: Jesus Marcano
 * Developer URI: http://iapeto.com/
 * Text Domain: woocommerce-bsale
 * Domain Path: /languages
 *
 * WC requires at least: 2.2
 * WC tested up to: 3.5
 *
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
 /**
  * Check if WooCommerce is active
  **/


  $debug = (get_option('bsale_debug')=='on');
  define('_BSALE_DEBUG',$debug);
  define('BSALE_MAIN_URL',plugin_dir_url(__FILE__));


  if ( ! defined( 'ABSPATH' ) ) {
      exit; // Exit if accessed directly
  }
  if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    include(plugin_dir_path(__FILE__).'/inc/class-pack-product.php');
    function bsale_install() {
    	global $wpdb;
    	$table_name = $wpdb->prefix . 'bsale_products';
    	$charset_collate = $wpdb->get_charset_collate();
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		name text,
    		product_id text NOT NULL,
        description text,
        classification mediumint(9),
        ledgerAccount text,
        costCenter text,
        allowDecimal mediumint(1),
        stockControl mediumint(1),
        printDetailPack text,
        state mediumint(9),
        product_type mediumint(9),
        pack_details text,
        variants text,
    		url varchar(55) DEFAULT '' NOT NULL,
        updater mediumint(1) DEFAULT 0 NOT NULL,
    		PRIMARY KEY  (id)
    	) $charset_collate;";
    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    	dbDelta( $sql );
      $table_name = $wpdb->prefix . 'bsale_documents';
    	$charset_collate = $wpdb->get_charset_collate();
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		user text NOT NULL,
        bill_id text,
        info text NOT NULL,
        url text NOT NULL,
    		PRIMARY KEY  (id)
    	) $charset_collate;";
    	dbDelta( $sql );
      $table_name = $wpdb->prefix . 'bsale_categories';
    	$charset_collate = $wpdb->get_charset_collate();
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		name text NOT NULL,
    		PRIMARY KEY  (id)
    	) $charset_collate;";
    	dbDelta( $sql );
      $table_name = $wpdb->prefix . 'bsale_price_list';
    	$charset_collate = $wpdb->get_charset_collate();
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		list_name text NOT NULL,
        coin text NOT NULL,
    		product_id text NOT NULL,
        price text NOT NULL,
        price_tax text NOT NULL,
    		PRIMARY KEY  (id)
    	) $charset_collate;";
    	dbDelta( $sql );
      $table_name = $wpdb->prefix . 'bsale_stocks';
    	$charset_collate = $wpdb->get_charset_collate();
    	$sql = "CREATE TABLE $table_name (
    		id mediumint(9) NOT NULL AUTO_INCREMENT,
    		product_id mediumint(9) NOT NULL,
    		quantity mediumint(9) NOT NULL,
    		quantity_reserved mediumint(9) NOT NULL,
    		quantity_available mediumint(9) NOT NULL,
    		office mediumint(9) NOT NULL,
    		PRIMARY KEY  (id)
    	) $charset_collate;";
    	dbDelta( $sql );
    }


    register_activation_hook( __FILE__, 'bsale_install' );

    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-products-list-table.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-branch-office-list-table.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-clients-list-table.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-sales-list-table.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-documents-list-table.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-settings.php' );
    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-core.php' );

    require( plugin_dir_path(__FILE__) . 'inc/class-bsale-hooks.php' );

     add_action('admin_menu', 'register_bsale_submenu_page');

     function register_bsale_submenu_page() {
         add_submenu_page( 'woocommerce', 'BSale', 'BSale', 'manage_options', 'bsale-settings', 'bsale_settings_page' );
     }


     function bsale_settings_page() {
       BSale_Settings::save();
       ?>
       <div class="wrap">
         <nav class="nav-tab-wrapper">
           <a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=general" class="nav-tab <?php if(isset($_GET['tab']) && $_GET['tab']=='general'){echo ' nav-tab-active';}?>">General</a>
           <?php if(get_option('bsale_api_key')!=''){ ?>
           <a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=branch_office" class="nav-tab <?php if(isset($_GET['tab']) && $_GET['tab']=='branch_office'){echo ' nav-tab-active';}?>">Sucursales</a>
           <a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=products" class="nav-tab <?php if(isset($_GET['tab']) && $_GET['tab']=='products'){echo ' nav-tab-active';}?>">Productos</a>
           <!--<a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=documents" class="nav-tab <?php if(isset($_GET['tab']) && $_GET['tab']=='documents'){echo ' nav-tab-active';}?>">Documentos</a>-->
           <?php } ?>
         </nav>
         <?php
           $tab = 'general';
           if(isset($_GET['tab'])){
             $tab = $_GET['tab'];
           }
           switch($tab){
             case 'general':
             BSale_Settings::general();
             break;
             case 'branch_office':
             BSale_Settings::branch_office();
             break;
             case 'products':
             BSale_Settings::products();
             break;
             case 'payment':
             break;
             case 'documents':
             BSale_Settings::documents();
             break;
             case 'stock':
             break;
           }
         ?>
       </div>
       <?php
    }
    add_option('bsale_api_key', '');

    add_action('init', 'bsale_assets_js');
    function bsale_assets_js(){
      wp_register_script('bsale_script', BSALE_MAIN_URL. '/assets/js/bsale.min.js', array('jquery'), '1.5.1', true );
      wp_enqueue_script('bsale_script');
      wp_localize_script('bsale_script','bsale_vars',['ajaxurl'=>admin_url('admin-ajax.php')]);
    }

    add_action('wp_ajax_nopriv_bsale_request','bsale_request');
    add_action('wp_ajax_bsale_request','bsale_request');
    function bsale_request(){
      $trigger = $_POST['trigger'];
      $resource = $_POST['resource'];
      switch($resource){
        case 'branch_office':
          echo 'Sucursales';
        break;
        default:
          echo 'Sin recurso';
      }
    	wp_die();
    }


    add_action('wp_ajax_nopriv_bsale_selector','bsale_selector');
    add_action('wp_ajax_bsale_selector','bsale_selector');

    function bsale_selector(){
      WC()->session->set("bsale_selector_bill",$_POST['selection']);
    }
    add_action('wp_ajax_nopriv_bsale_sync','bsale_sync');
    add_action('wp_ajax_bsale_sync','bsale_sync');
    function bsale_sync(){
      global $wpdb;
      if(!isset($_POST['skip'])){
        if($_POST['context']=='stock'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('stocks');
          }
          else{
            $page = $_POST['page'];
            $data = BSale::get_stocks_page($page);
          }
        }
        if($_POST['context']=='price_list'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('price_lists');
          }
          else{
            $page = $_POST['page'];
            $data = ["price_list"];
          }
        }
        if($_POST['context']=='price_list_details'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('price_list_details');
          }
          else{
            $page = $_POST['page'];
            $data = BSale::get_price_list_detail_page($page);
          }
        }
        if($_POST['context']=='product_types'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('product_types');
          }
          else{
            $page = $_POST['page'];
            $data = BSale::get_product_types_page($page);
          }
        }
        if($_POST['context']=='products'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('products');
          }
          else{
            $page = $_POST['page'];
            $data = BSale::get_product_page($page);
          }
        }
        if($_POST['context']=='sync'){
          if($_POST['info']=="true"){
            $data = BSale::get_pages('sync');
          }
          else{
            $page = $_POST['page'];
            $data = BSale::sync_products($page);
          }
        }
      }
      else{
        $data = BSale::sync_products();
      }
      echo json_encode($data);
      wp_die();
    }







  }

add_filter( 'wc_product_has_unique_sku', '__return_false' );

function bsale_best_selling(){
  ob_start();
  $args = array(
      'post_type' => 'product',
      'meta_key' => 'total_sales',
      'orderby' => 'meta_value_num',
      'posts_per_page' => 3,
  );
  $loop = new WP_Query( $args );
  ?>
  <ul class="bsale products columns-3">
  <?php
  while ( $loop->have_posts() ) : $loop->the_post();
    global $product;
    wc_get_template_part( 'content', 'product' );
  endwhile; ?>
  </ul>
  <?php wp_reset_query();
  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}

add_shortcode( 'bsale-best-selling', 'bsale_best_selling' );



add_action( 'init', 'stop_heartbeat', 1 );
function stop_heartbeat() {
  if(isset($_REQUEST['page']) && isset($_REQUEST['tab']) && $_REQUEST['page']=='bsale-settings' && $_REQUEST['tab']=='products'){
    wp_deregister_script('heartbeat');
  }
}
