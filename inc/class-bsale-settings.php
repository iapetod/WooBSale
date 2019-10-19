<?php

class BSale_Settings{
  public static function save(){
    //67cf603124e133c777586168f2bd5b805c575ee4
    if ( isset( $_POST['bsale_nonce'] ) &&  wp_verify_nonce( $_POST['bsale_nonce'], 'bsale_save_options' ) ) {
      if(isset($_POST['action'])){
        if($_POST['action']=='enable_branch_offices'){
          $branchs = [];
          if(get_option('bsale_branchs')!=''){
            $branchs = json_decode(get_option('bsale_branchs'));
          }
          foreach($_POST['branch_office'] as $office){
            $branchs[] = $office;
          }
          $branchs = array_unique($branchs);
          update_option( 'bsale_branchs', json_encode($branchs) );
        }
        if($_POST['action']=='disable_branch_offices'){
          $branchs = [];
          if(get_option('bsale_branchs')!=''){
            $branchs = json_decode(get_option('bsale_branchs'));
          }
          for($j=0;$j<count($_POST['branch_office']);$j++){
            $key = $_POST['branch_office'][$j];
            for($i=0;$i<count($branchs);$i++){
              if($branchs[$i]==$key){
                array_splice($branchs, $i, 1);
              }
            }
          }

          update_option( 'bsale_branchs', json_encode($branchs) );
        }
        if($_POST['action']=='bsale_request'){
          update_option( 'bsale_api_key', $_POST['bsale_api_key'] );
          update_option( 'bsale_debug', $_POST['bsale_debug'] );
        }
      }
    }

    if(isset($_GET['action'])){
      if($_GET['action']=='product_get'){
        BSale::get_products();
        wp_redirect(admin_url().'admin.php?page=bsale-settings&tab=products');
      }
      if($_GET['action']=='product_sync'){
        BSale::sync_products();
        wp_redirect(admin_url().'admin.php?page=bsale-settings&tab=products');
      }
      if($_GET['action']=='enable_branch_offices'){
        $branchs = [];
        if(get_option('bsale_branchs')!=''){
          $branchs = json_decode(get_option('bsale_branchs'));
        }
        $branchs[] = $_GET['office'];
        $branchs = array_unique($branchs);
        //print_r($branchs);
        update_option( 'bsale_branchs', json_encode($branchs) );
        wp_redirect(admin_url().'admin.php?page=bsale-settings&tab=branch_office');
      }
      if($_GET['action']=='disable_branch_offices'){
        $branchs = [];
        if(get_option('bsale_branchs')!=''){
          $branchs = json_decode(get_option('bsale_branchs'));
        }
        for($i=0;$i<count($branchs);$i++){
          if($branchs[$i]==$_GET['office']){
            $key = $i;
            break;
          }
        }
        array_splice($branchs, $key, 1);
        update_option( 'bsale_branchs', json_encode($branchs) );
        wp_redirect(admin_url().'admin.php?page=bsale-settings&tab=branch_office');
      }

    }
  }
  public static function general(){
    ?>
   <form method="post" id="mainform" action="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=general" enctype="multipart/form-data">
     <h1 class="screen-reader-text">General</h1>
     <?php if(get_option('bsale_api_key')==''){ ?>
       <div id="message" class="error woocommerce-message inline">
         <p>
           Ingresa la API Key de BSale.
         </p>
       </div>
       <?php
     }?>
     <h2>API Key</h2>
     <input type="hidden" name="action" value="bsale_request">
     <input type="text" value="<?php echo get_option('bsale_api_key');?>" name="bsale_api_key" placeholder="Escribe la API KEY" class="regular-text"> <?php if(get_option('bsale_api_key')!=''){ echo '(Activa)';}else{ echo '(Sin API Key)';}?>
     <h3>Debug</h3>
     <input name="bsale_debug" id="bsale_debug" type="checkbox" <?php if(get_option('bsale_debug')=="on"){ echo "checked";}?>><label for="bsale_debug">Habilitar Depuración</label>
     <p class="submit">
     <button name="save" class="button-primary" type="submit" value="Guardar los cambios">Guardar los cambios</button>
     <?php wp_nonce_field( 'bsale_save_options', 'bsale_nonce' ); ?>
     </p>
   </form>
    <?php
  }
  public static function branch_office(){
    ?>

    <?php if(get_option('bsale_api_key')!=''){ ?>
    <h2>Sucursales</h2>
    <div id="pricing_options-description">
      <p>Selecciona la sucursal de donde obtener la lista de precios</p>
    </div>
    <div class="">
      <form method="post" id="mainform" action="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=branch_office" enctype="multipart/form-data">
        <?php
        $ListTable = new BranchOffice_List_Table();
        $ListTable->prepare_items();
        $ListTable->display();
        ?>
        <?php wp_nonce_field( 'bsale_save_options', 'bsale_nonce' ); ?>
      </form>
    </div>
    <?php } ?>

    <?php
  }
  public static function products(){

    ?>
    <?php if(get_option('bsale_branchs')=='' || get_option('bsale_branchs')=='[]'):?>
    <div id="message" class="error woocommerce-message inline">
      <a class="woocommerce-message-close notice-dismiss" style="top:0;" href="/wp-admin/admin.php?page=wc-settings&amp;wc-hide-notice=store_notice_setting_moved&amp;_wc_notice_nonce=4a17d1313b">Descartar</a>
      <p>
        Debes seleccionar al menos una sucursal en <a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=branch_office">Sucursales</a>.
      </p>
    </div>
    <?php endif;?>
    <h1 class="wc-shipping-zones-heading">
      Productos<span id="bsale-sync-loader" style="width:400px;padding-left:28px" class="spinner"></span>
      <a href="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=products&action=product_sync"  id="bsale-sync-products" class="page-title-action">Sincronizar productos</a>
    </h1>
    <form method="post" id="mainform" action="<?php echo admin_url();?>admin.php?page=bsale-settings&tab=products" enctype="multipart/form-data">
    <?php
      $ListTable = new Product_List_Table();
      $ListTable->prepare_items();
      $ListTable->search_box('search', 'search_id');
      $ListTable->display();
    ?>
    </form>
    <?php
  }
  public static function clients(){
    ?>
    <h1 class="wc-shipping-zones-heading">
      Clientes
    </h1>
    <?php
    $ListTable = new Clients_List_Table();
    $ListTable->prepare_items();
    $ListTable->search_box('search', 'search_id');
    $ListTable->display();
  }
  public static function sales(){
    ?>
    <h1 class="wc-shipping-zones-heading">
      Ventas
    </h1>
    <?php
    $ListTable = new Sales_List_Table();
    $ListTable->prepare_items();
    $ListTable->search_box('search', 'search_id');
    $ListTable->display();
  }
  public static function documents(){
    ?>
    <h1 class="wc-shipping-zones-heading">
      Documentos
    </h1>
    <?php
    $ListTable = new Documents_List_Table();
    $ListTable->prepare_items();
    $ListTable->search_box('search', 'search_id');
    $ListTable->display();
  }
}
