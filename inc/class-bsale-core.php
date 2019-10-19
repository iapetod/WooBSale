<?php

class BSale{


  public static function url($section,$args){
    $_api_url="https://api.bsale.cl";
    $_urls=[
      "clients"=>"",
      "dte_codes"=>"",
      "sale_conditions"=>"",
      "discounts"=>"",
      "shippings"=>"",
      "returns"=>"",
      "documents"=>"/v1/documents",
      "document"=>"/v1/documents",
      "third_party_documents"=>"",
      "payment_types"=>"",
      "taxes"=>"",
      "price_lists"=>"/v1/price_lists",
      "coins"=>"",
      "payments"=>"",
      "products"=>"/v1/products",
      "stocks"=>"/v1/stocks",
      "offices"=>"/v1/offices",
      "shipping_types"=>"",
      "document_types"=>"",
      "book_types"=>"",
      "product_types"=>"/v1/product_types",
      "users"=>"",
      "variants"=>"/v1/variants",
      "consumptions"=>"/v1/stocks/consumptions"
    ];
    $url=$_api_url.$_urls[$section];
    if($args=="count"){
      $url.='/count.json';
    }
    else{
      if(!isset($args['id'])){
        $url.='.json';
      }
      else{
        if(isset($args['id'])){
          $url.='/'.$args['id'];
          if(isset($args['attr']) && $args['attr']=='details'){
            $url.='/details';
          }
          $url.='.json';
        }
      }
    }
    $params = [];
    if(isset($args['limit']) && $args['limit']<=50){
      $params[] = "limit=".$args['limit'];
    }
    if(isset($args['offset'])){
      $params[] = "offset=".$args['offset'];
    }
    if(isset($args['limit'])){
      $params[] = "limit=".$args['limit'];
    }
    if(isset($args['code'])){
      $params[] = "code=".$args['code'];
    }
    if(isset($args['expand'])){
      $params[] = "expand=".$args['expand'];
    }
    if(count($params)>0){
      $url.='?'.implode("&",$params);
    }
    return $url;
  }

  public static function request($section,$args){
    if($section=='document'){
      $url=BSale::url($section,[]);
      $access_token=get_option('bsale_api_key');
      $session = curl_init($url);
      $data =  json_encode( $args );
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($session, CURLOPT_POST, true);
      curl_setopt($session, CURLOPT_POSTFIELDS, $data);
      $headers = array(
          'access_token: ' . $access_token,
          'Accept: application/json',
          'Content-Type: application/json'
      );
      curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($session);
      $code = curl_getinfo($session, CURLINFO_HTTP_CODE);
      curl_close($session);
      return ($response);
    }
    else{
      $url=BSale::url($section,$args);
      $access_token=get_option('bsale_api_key');
      $session = curl_init($url);
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
      $headers = array(
          'access_token: ' . $access_token,
          'Accept: application/json',
          'Content-Type: application/json'
      );
      curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($session);
      $code = curl_getinfo($session, CURLINFO_HTTP_CODE);
      curl_close($session);
      return ($response);
    }

  }
  public static function documents(){
    $documents = array(
      array('ID' => 1,'booktitle' => 'Quarter Share', 'author' => 'Nathan Lowell',
            'isbn' => '978-0982514542'),
      array('ID' => 2, 'booktitle' => '7th Son: Descent','author' => 'J. C. Hutchins',
            'isbn' => '0312384378'),
      array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan',
            'isbn' => '978-1905548927'),
      array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan',
            'isbn' => '978-0979621130')
    );
    return $documents;
  }
  public static function product_types(){
    global $wpdb;
	  $table_name = $wpdb->prefix . 'bsale_categories';
    $results = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
    $product_types = [];
    $max =0;
    for($i=0;$i<count($results);$i++){
      $product_types[] = ["id"=>$results[$i]['id'],"name"=>$results[$i]['name']];
      if($max<$results[$i]){
        $max=$results[$i]['id'];
      }
    }
    $types=[];
    for($k=0;$k<=$max;$k++){
      $types[$k]=null;
    }
    for($k=0;$k<=$max;$k++){
      if(isset($product_types[$k])){
        $types[$product_types[$k]['id']]=$product_types[$k]['name'];
      }
    }
    return $types;
  }
  public static function get_product_types(){
    global $wpdb;
	  $table_name = $wpdb->prefix . 'bsale_categories';
    $wpdb->query('TRUNCATE TABLE '.$table_name);

    $offset = 0;
    $response = json_decode(BSale::request('product_types',[]));
    $count = $response->count;
    $pages = $count/25;
    $product_types = [];
    $max=0;
    for($j=0;$j<$pages;$j++){
      $response = json_decode(BSale::request('product_types',['offset'=>$offset]));
      for($i=0;$i<count($response->items);$i++){
        $product_types[] = ["id"=>$response->items[$i]->id,"name"=>$response->items[$i]->name];
        if($max<$response->items[$i]->id){
          $max=$response->items[$i]->id;
        }
        $wpdb->insert(
          $table_name,
          array(
            'id' => $response->items[$i]->id,
            "name"=>$response->items[$i]->name
          )
        );
      }
      $offset += 25;
      sleep(0.5);
    }
    $types=[];
    for($k=0;$k<=$max;$k++){
      $types[$k]=null;
    }
    for($k=0;$k<=$max;$k++){
      if(isset($product_types[$k])){
        $types[$product_types[$k]['id']]=$product_types[$k]['name'];
      }
    }
    return $types;
  }
  public static function get_product_types_page($page){
    global $wpdb;
	  $table_name = $wpdb->prefix . 'bsale_categories';
    $offset = $page * 25;
    $response = json_decode(BSale::request('product_types',['offset'=>$offset]));
    for($i=0;$i<count($response->items);$i++){
      $product_types[] = ["id"=>$response->items[$i]->id,"name"=>$response->items[$i]->name];
      $exist =$wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE id = {$response->items[$i]->id}" );
      if($exist){
        $wpdb->update(
          $table_name,
          array(
            'id' => $response->items[$i]->id,
            "name"=>$response->items[$i]->name
          ),
          array("id"=>$response->items[$i]->id)
        );
      }
      else{
        $wpdb->insert(
          $table_name,
          array(
            'id' => $response->items[$i]->id,
            "name"=>$response->items[$i]->name
          )
        );
      }
    }
    return $response;
  }
  public static function products(){
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bsale_products", ARRAY_A );
    return $results;
  }
  public static function products_page($page){
    global $wpdb;
    $offset = $page;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bsale_products LIMIT 1 OFFSET $offset", ARRAY_A );
    return $results;
  }
  public static function sync_products($page){
    global $wpdb;
    $attributes = array(
      array("name"=>'BSaleVar',"position"=>1,"visible"=>1,"variation"=>1),
    );
    foreach($attributes as $attribute){
      $attr_n = wc_sanitize_taxonomy_name(stripslashes($attribute["name"])); // remove any unwanted chars and return the valid string for taxonomy name
      if(!taxonomy_exists( wc_attribute_taxonomy_name( $attr_n ) )){
        BSale::create_product_attribute($attribute["name"]);
      }
    }
    $taxes = false;
    if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
      $taxes = true;
    }
    $results =  BSale::products_page($page);
    $product_types = BSale::product_types();
    for($i=0;$i<count($results);$i++){
      $item=$results[$i];
      if($item['name']==null || $item['variants']==null){
        continue;
      }
      $attr = [
        "name"=>$item['name'],
        "product_id"=>$item['product_id'],
        "pack_details"=>$item['pack_details'],
        "product_type"=>$product_types[$item['product_type']],
        "description"=>$item['description'],
        "stockControl"=>$item['stockControl'],
        "classification"=>$item['classification'],
        "url"=>$item['url'],
        "state"=>$item['state']
      ];
      $variants = json_decode($item['variants']);
      if(count($variants)>1){
        //Producto Variable
        $parent_id=BSale::create_variable_product($attr);
        $attr['wc_id'] = $parent_id;
        $options=[];
        for($j=0;$j<count($variants);$j++){
          if($variant->state == 1){
            //continue;
          }
          $attr['wc_id'] = null;
          $product_id=0;
          $variant = $variants[$j];
          //Producto Variante
          $attr['product_id'] = $variant->id;
          $attr['unlimitedStock'] = $variant->unlimitedStock;
          $attr['allowNegativeStock'] = $variant->allowNegativeStock;
          $attr['state'] = $variant->state;
          $attr['description'] = $item['name'].' '.$variant->description;
          $attr['barCode']=$variant->barCode;
          if($variant->code!=null && $variant->code!=''){
            $attr['sku']=$variant->code;
          }
          if(!$taxes){
            $attr['price']=BSale::get_price($variant->id)['regular_price'];
          }
          else{
            $attr['price']=BSale::get_price($variant->id)['price_tax'];
          }
          $at=["name"=>"BSaleVar","option"=>$variant->description];
          $attributes[]=$at;
          $attr['attributes']=[$at];
          $attr['parent_id'] = $parent_id;
          $product_id=BSale::create_variation_product($parent_id,$attr);
          $attr['wc_id'] = $product_id;
          $options[]=sanitize_title($at['option']);
          $options=array_unique($options);
        }

        $productAttributes[sanitize_title('BSaleVar')] = array(
          'name' => sanitize_title('BSaleVar'),
          'value' => implode('|',$options),
          'position' => '0',
          'is_visible' => '0',
          'is_variation' => '1',
          'is_taxonomy' => '0'
        );
        update_post_meta($parent_id,'_product_attributes',$productAttributes);

      }
      else{
        if(json_decode($attr['pack_details'])!=NULL){
          //Producto Pack
          $variant = $variants[0];
          $attr['description'] = $item['name'].' '.$variant->description;
          $attr['barCode']=$variant->barCode;
          if($variant->code!=null && $variant->code!=''){
            $attr['sku']=$variant->code;
          }
          if(!$taxes){
            $attr['price']=BSale::get_price($variant->id)['regular_price'];
          }
          else{
            $attr['price']=BSale::get_price($variant->id)['price_tax'];
          }
          $product_id=BSale::create_pack_product($attr);
          $attr['wc_id'] = $product_id;
          //echo '<div style="background:silver">';
          $categories = get_the_terms( $product_id, 'product_cat' );
        }
        else{
          //Producto Simple
          $variant = $variants[0];
          $attr['description'] = $item['name'].' '.$variant->description;
          $attr['barCode']=$variant->barCode;
          $attr['variant_id']=$variant->id;
          $attr['unlimitedStock']=$variant->unlimitedStock;
          if($variant->code!=null && $variant->code!=''){
            $attr['sku']=$variant->code;
          }
          if(!$taxes){
            $attr['price']=BSale::get_price($variant->id)['regular_price'];
          }
          else{
            $attr['price']=BSale::get_price($variant->id)['price_tax'];
          }
          $product_id=BSale::create_simple_product($attr);
          $attr['wc_id'] = $product_id;
          //echo '<div style="background:silver">';
          $categories = get_the_terms( $product_id, 'product_cat' );
        }
        /*
        echo '</div>';
        echo '<div style="background:blue;color:white;">';
        echo '</div>';*/
      }
    }
    return $results;

    /*$args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1
    );

    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post();
        global $product;
        echo '<br /><a href="'.get_permalink().'">'.$product->get_id().'-'.$product->get_name().'</a>';
    endwhile;
    wp_reset_query();
    die();*/
    //wp_redirect(admin_url().'admin.php?page=bsale-settings&tab=products');
  }

  public static function get_pages($context){
    if($context=="sync"){
      global $wpdb;
      $table_name = $wpdb->prefix . 'bsale_products';
      $count =$wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
      $pages = $count;
    }
    elseif($context=="price_list_details"){
      $id = 3;//get_option('bsale_price_list');
      $response = json_decode(BSale::request("price_lists",["id"=>$id,"expand"=>"details"]));
      $response = ($response->details);
      $count = $response->count;
      $pages = round($count/25 + 0.5);
    }
    else{
      $response = (BSale::request($context,[]));
      $response = json_decode($response);
      $count = $response->count;
      $pages = round($count/25 + 0.5);
    }

    return ["count"=>$count,"pages"=>$pages];
  }

  public static function get_product_page($page){
    global $wpdb;
    $table_name = $wpdb->prefix . 'bsale_products';
    $offset = $page * 25;
    $response = json_decode(BSale::request('products',["state"=>0,'offset'=>$offset,'expand'=>'variants']));
    $products = $response->items;
    for($i=0;$i<count($products);$i++){
      $variants = $products[$i]->variants->items;
      $exist =$wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE product_id = {$products[$i]->id}" );
      $pack_details = null;
      if($products[$i]->pack_details!=null){
        $pack_details = json_encode($products[$i]->pack_details);
      }

      if($exist){
        $wpdb->update(
          $table_name,
          array(
            'name' => $products[$i]->name,
            'product_id' => $products[$i]->id,
            'description' => $products[$i]->description,
            'classification' => $products[$i]->classification,
            'ledgerAccount' => $products[$i]->ledgerAccount,
            'costCenter' => $products[$i]->costCenter,
            'allowDecimal' => $products[$i]->allowDecimal,
            'stockControl' => $products[$i]->stockControl,
            'printDetailPack'=>$products[$i]->printDetailPack,
            'pack_details'=>$pack_details,
            'state'=>$products[$i]->state,
            'product_type'=>$products[$i]->product_type->id,
            'variants'=>json_encode($products[$i]->variants->items),
            'url'=>$products[$i]->href
          ),
          array('product_id' => $products[$i]->id)
        );
      }
      else{
        $wpdb->insert(
          $table_name,
          array(
            'name' => $products[$i]->name,
            'product_id' => $products[$i]->id,
            'description' => $products[$i]->description,
            'classification' => $products[$i]->classification,
            'ledgerAccount' => $products[$i]->ledgerAccount,
            'costCenter' => $products[$i]->costCenter,
            'allowDecimal' => $products[$i]->allowDecimal,
            'stockControl' => $products[$i]->stockControl,
            'printDetailPack'=>$products[$i]->printDetailPack,
            'pack_details'=>$pack_details,
            'state'=>$products[$i]->state,
            'product_type'=>$products[$i]->product_type->id,
            'variants'=>json_encode($products[$i]->variants->items),
            'url'=>$products[$i]->href
          )
        );
      }

    }
    return $response;
  }

  public static function get_products(){
    global $wpdb;
    die();
    $stocks = BSale::get_stocks();
    $product_types = BSale::get_product_types();
    $product_prices = BSale::get_price_list();
	  $table_name = $wpdb->prefix . 'bsale_products';
    $wpdb->query('TRUNCATE TABLE '.$table_name);
    $offset = 0;
    $pages = BSale::get_pages('products');
    $list_products = [];
    if (ob_get_level() == 0) ob_start();
    for($j=0;$j<$pages;$j++){
      $response = BSale::get_products_page($offset);//json_decode(BSale::request('products',['offset'=>$offset,'expand'=>'variants']));
      $offset += 25;
      ob_flush();
      flush();
    }
    ob_end_flush();
  }
  public static function stock($product){
    $sku = $product->get_sku();
    $response = json_decode(BSale::request('stocks',['code'=>$product->get_sku(),'expand'=>'variant']));
    $quantity = 0;
    if(count($response->items)>0){
      if($response->count>0){
        $items = $response->items;
        for($i=0;$i<count($items);$i++){
          $quantity += $items[$i]->quantity;
        }
      }
    }
    else{
      return null;
    }
    $data=false;
    if($quantity>0){
      $data = true;
    }
    return ["in_stock"=>$data,'quantity'=>$quantity];
  }
  public static function in_stock($sku){
    return false;
  }
  public static function branch_office(){
    $response = json_decode(BSale::request('offices',[]));
    $branchs = [];
    if(get_option('bsale_branchs')!=''){
      $branchs = json_decode(get_option('bsale_branchs'));
    }
    $branch_office = $response->items;
    $offices=[];
    for($i=0;$i<count($branch_office);$i++){
      $state = false;
      for($i=0;$i<count($branchs);$i++){
        if($branchs[$i]==$branch_office[$i]->id){
          $state=true;
          break;
        }
      }
      $offices[] =['ID' =>$branch_office[$i]->id,'name' => $branch_office[$i]->name,'status' => $state];
    }
    return $offices;
  }
  public static function document($args){
    if(!_BSALE_DEBUG){
      $request =  BSale::request('document',$args);
    }
    else{
      $request = ["debug"=>json_encode($args),"request"=>BSale::request('document',$args)];
    }
    return $request;
  }
  public static function get_price($id){
    global $wpdb;
    $data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}bsale_price_list WHERE product_id = $id" );
    if($data!=NULL){
      $prices = [
        "regular_price"=>$data->price,
        "price_tax"=>$data->price_tax
      ];
    }
    else{
      $prices = [
        "regular_price"=>0,
        "price_tax"=>0
      ];
    }
    return $prices;
  }
  public static function get_price_list(){
    global $wpdb;
	  $table_name = $wpdb->prefix . 'bsale_price_list';
    $wpdb->query('TRUNCATE TABLE '.$table_name);

    $offset = 0;
    $response = json_decode(BSale::request('price_lists',['expand'=>'coin']));
    $count = $response->count;
    $pages = $count/25;
    $price_list = [];
    $max=0;
    $coin = $response->items[0]->coin->name;
    if (ob_get_level() == 0) ob_start();
    for($j=0;$j<$pages;$j++){
      $response = json_decode(BSale::request('price_lists',['offset'=>$offset]));
      for($i=0;$i<count($response->items);$i++){
        $price_list[] = [
          'id'=>$response->items[$i]->id,
          'name'=>$response->items[$i]->name,
          'coin'=>$coin
        ];
      }
      $offset += 25;
      sleep(0.5);
      ob_flush();
      flush();
    }
    ob_end_flush();
    for($k=0;$k<count($price_list);$k++){
      $offset = 0;
      $response = json_decode(BSale::request('price_lists',["id"=>$price_list[$k]['id'],'attr'=>'details']));
      $count = $response->count;
      $pages = $count/25;
      $product_prices = [];
      for($j=0;$j<$pages;$j++){
        $response = json_decode(BSale::request('price_lists',["id"=>$price_list[$k]['id'],'attr'=>'details','offset'=>$offset]));
        for($i=0;$i<count($response->items);$i++){
          $wpdb->insert(
            $table_name,
            array(
              "list_name"=>$price_list[$k]['name'],
              "coin"=>$coin,
              "product_id"=>$response->items[$i]->variant->id,
              "price"=>$response->items[$i]->variantValue,
              "price_tax"=>$response->items[$i]->variantValueWithTaxes
            )
          );
          $product_prices[] = $response->items[$i];
        }
        $offset += 25;
        sleep(1);
      }
    }
    return $product_prices;
  }

  public static function get_coin(){

  }

  public static function get_price_list_detail_page($page){
    global $wpdb;
    $price_list = 3;
    $response = json_decode(BSale::request('price_lists',['expand'=>'coin']));
    $coin = $response->items[0]->coin->name;
    $table_name = $wpdb->prefix . 'bsale_price_list';
    $offset = $page * 25;
    $response = json_decode(BSale::request('price_lists',["id"=>$price_list,'attr'=>'details','offset'=>$offset]));
    for($i=0;$i<count($response->items);$i++){
      $exist =$wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE product_id = {$response->items[$i]->variant->id}" );
      if($exist > 0){
        $wpdb->update(
          $table_name,
          array(
            "list_name"=>"Lista de precios",
            "coin"=>$coin,
            "product_id"=>$response->items[$i]->variant->id,
            "price"=>$response->items[$i]->variantValue,
            "price_tax"=>$response->items[$i]->variantValueWithTaxes
          ),
          array("product_id"=>$response->items[$i]->variant->id)
        );
      }
      else{
        $wpdb->insert(
          $table_name,
          array(
            "list_name"=>"Lista de precios",
            "coin"=>$coin,
            "product_id"=>$response->items[$i]->variant->id,
            "price"=>$response->items[$i]->variantValue,
            "price_tax"=>$response->items[$i]->variantValueWithTaxes
          )
        );
      }

    }
    return $response;
  }
  public static function get_price_lists_page($page){
    global $wpdb;
    $table_name = $wpdb->prefix . 'bsale_price_list';
    $response = json_decode(BSale::request('price_lists',['offset'=>$offset]));
    for($i=0;$i<count($response->items);$i++){
      $price_list[] = [
        'id'=>$response->items[$i]->id,
        'name'=>$response->items[$i]->name,
        'coin'=>$coin
      ];
    }
    return $price_list;
  }

  public static function create_pack_product( $data ){
    //echo '<h1>Simple Product</h1>';
    $_created=false;

    if($data['sku']!=''){
      global $wpdb;
      $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data['sku'] ) );
      if ( $product_id ){
        $objProduct = wc_get_product( $product_id );
      }
      else{
        $objProduct = new WC_Product_Pack();
        $_created=true;
      }
    }
    else{
      $objProduct = new WC_Product_Pack();
      $_created=true;
    }
    $objProduct->set_name($data['name']);
    $objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
    $objProduct->set_catalog_visibility('visible'); // add the product visibility status
    //$objProduct->set_description("Product Description");
    if(isset($data['sku'])){
      $objProduct->set_sku($data['sku']);
    }
     //can be blank in case you don't have sku, but You can't add duplicate sku's
    //$objProduct->set_price($data['sku']); // set product price
    $objProduct->set_regular_price($data['price']); // set product regular price

    $objProduct->set_manage_stock(($data['stock_control']==1)); // true or false
    $objProduct->set_stock_status('instock');
    if($data['stock_control']=="1"){
      $stock = BSale::get_stock($data['id']);
      $objProduct->set_stock_quantity($stock);
      if($stock>0){
        $objProduct->set_stock_status('instock'); // in stock or out of stock value
      }
      else{
        $objProduct->set_stock_status('outstock');
      }
    }

    if(($data['sku'][0]=='P' && $data['sku'][1]=='B') || $data['unlimitedStock']==1 || ($data['sku'][0]=='L' && $data['sku'][1]=='S')){
      $objProduct->set_manage_stock(false);
      $objProduct->set_stock_status('instock');
    }

    $objProduct->set_backorders('no');
    $objProduct->set_reviews_allowed(false);
    $objProduct->set_sold_individually(false);
    $cat_id=0;


    $term = term_exists( $data['produc_type'], 'product_cat' );
    if ( $term !== 0 && $term !== null ) {
      $cat_id=$term['term_id'];
    }
    else{
      $cat_id=wp_insert_term(
        $data['category'],
        'product_cat',
        array('slug' => wc_sanitize_taxonomy_name($data['category']))
      );
    }
    $term = term_exists( 'PACK', 'product_cat' );
    if ( $term !== 0 && $term !== null ) {
      $pack=$term['term_id'];
    }
    else{
      $pack=wp_insert_term(
        'PACK',
        'product_cat',
        array('slug' => wc_sanitize_taxonomy_name('PACK'))
      );
    }
    $objProduct->set_category_ids(array($pack,$cat_id));
    if($data['state']=="1" || $data['state']==1){
      $objProduct->set_catalog_visibility('hidden');
    }
    else{
      $objProduct->set_catalog_visibility('visible');
    }
    $product_id = $objProduct->save();
    update_post_meta($product_id, '_bsale_id', $data['id']);
    update_post_meta( $product_id, 'pack_product_price' , $data['price'] );
    $pack_details = json_decode($data['pack_details']);
    $bundles = [];
    foreach($pack_details as $bundle){
      $bundles[] = ["id"=>$bundle->product->id,"quantity"=>$bundle->quantity];
    }
    update_post_meta( $product_id, 'pack_product_bundles' , $bundles );
    return $product_id;
  }
  public static function create_simple_product( $data ){
    $_created=false;
    if($data['sku']!=''){
      global $wpdb;
      $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data['sku'] ) );
      if ( $product_id ){
        $objProduct = wc_get_product( $product_id );
      }
      else{
        $objProduct = new WC_Product();
        $_created=true;
      }
    }
    else{
      $objProduct = new WC_Product();
      $_created=true;
    }
    $objProduct->set_name($data['name']);
    $objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
    $objProduct->set_catalog_visibility('visible'); // add the product visibility status
    $objProduct->set_description($data['description']);
    if(isset($data["sku"])){
      $objProduct->set_sku($data['sku']); //can be blank in case you don't have sku, but You can't add duplicate sku's
    }
    //$objProduct->set_price($data['sku']); // set product price
    $objProduct->set_regular_price($data['price']); // set product regular price

    $objProduct->set_manage_stock(($data['stockControl']==1 && $data['unlimitedStock']==0)); // true or false
    $objProduct->set_stock_status('instock');
    if($data['stockControl']==1 && $data['unlimitedStock']==0){
      $stock = BSale::get_stock($data['variant_id']);
      $objProduct->set_stock_quantity($stock);
      if($stock==0){
        $objProduct->set_stock_status('outstock'); // in stock or out of stock value
      }
    }
    else{
      $objProduct->set_stock_status('instock');
    }

    if(($data['sku'][0]=='P' && $data['sku'][1]=='B') || $data['unlimitedStock']==1 || ($data['sku'][0]=='L' && $data['sku'][1]=='S')){
      $objProduct->set_manage_stock(false);
      $objProduct->set_stock_status('instock');
    }
    $objProduct->set_backorders('no');
    $objProduct->set_reviews_allowed(false);
    $objProduct->set_sold_individually(false);
    $cat_id=0;
    $term = term_exists( $data['product_type'], 'product_cat' );
    if ( $term !== 0 && $term !== null ) {
      $cat_id=$term['term_id'];
    }
    else{
      $cat_id=wp_insert_term(
        $data['product_type'],
        'product_cat',
        array('slug' => wc_sanitize_taxonomy_name($data['product_type']))
      );
    }
    $objProduct->set_category_ids(array($cat_id));

    if($data['state']=="1" || $data['state']==1){
      $objProduct->set_catalog_visibility('hidden');
    }
    else{
      $objProduct->set_catalog_visibility('visible');
    }
    $product_id = $objProduct->save();
    update_post_meta($product_id, '_bsale_id', $data['product_id']);
    update_post_meta($product_id, '_bsale_variant_id', $data['variant_id']);
    return $product_id;
  }
  public static function create_variable_product( $data ){
    $_created=false;
    global $wpdb;
    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_id' AND meta_value='%s' LIMIT 1", $data['product_id'] ) );
    if ( $product_id ){
      $objProduct = wc_get_product( $product_id );
    }
    else{
      $objProduct = new WC_Product_Variable();
      $_created=true;
    }

    $objProduct->set_name($data['name']);
    $objProduct->set_status("publish");  // can be publish,draft or any wordpress post status
    $objProduct->set_catalog_visibility('visible'); // add the product visibility status
    //$objProduct->set_description("Product Description");
    //$objProduct->set_sku($data['sku']); //can be blank in case you don't have sku, but You can't add duplicate sku's
    //$objProduct->set_price(10.55); // set product price
    //$objProduct->set_regular_price(10.55); // set product regular price
    //$objProduct->set_manage_stock(true); // true or false
    //$objProduct->set_stock_quantity(10);
    //$objProduct->set_stock_status('instock'); // in stock or out of stock value
    $objProduct->set_backorders('no');
    $objProduct->set_reviews_allowed(true);
    $objProduct->set_sold_individually(false);
    $cat_id=0;
    $term = term_exists( $data['product_type'], 'product_cat' );
    if ( $term !== 0 && $term !== null ) {
      $cat_id=$term['term_id'];
    }
    else{
      $cat_id=wp_insert_term(
        $data['product_type'],
        'product_cat',
        array('slug' => wc_sanitize_taxonomy_name($data['product_type']))
      );
    }
    $objProduct->set_category_ids(array($cat_id));
    if(($data['sku'][0]=='P' && $data['sku'][1]=='B') || $data['unlimitedStock']==1 || ($data['sku'][0]=='L' && $data['sku'][1]=='S')){
      $objProduct->set_manage_stock(false);
      $objProduct->set_stock_status('instock');
    }
    if($data['state']=="1" || $data['state']==1){
      $objProduct->set_catalog_visibility('hidden');
    }
    else{
      $objProduct->set_catalog_visibility('visible');
    }
    $product_id = $objProduct->save();
    update_post_meta($product_id, '_bsale_id', $data['product_id']);
    return $product_id;
  }
  public static function create_variation_product( $parent_id, $data ){
    global $wpdb;
    $_created=false;
    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data['sku'] ) );
    //$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_bsale_variant_id' AND meta_value='%s' LIMIT 1", $data['variant_id'] ) );
    if ( $product_id ){
      $objVariation = wc_get_product( $product_id );
    }
    else{
      $objVariation = new WC_Product_Variation();
      $_created=true;
    }
    $objVariation->set_price($data["price"]);
    $objVariation->set_regular_price($data["price"]);
    if(isset($data["sku"])){
      $objVariation->set_sku($data["sku"]);
    }
    $objVariation->set_manage_stock(($data['stockControl']==1 && $data['unlimitedStock']==0)); // true or false
    $objVariation->set_stock_status('instock');
    $objVariation->set_description($data['description']);
    if($data['stockControl']==1 && $data['unlimitedStock']==0){
      $stock = BSale::get_stock($data['product_id']);
      $objVariation->set_stock_quantity($stock);
      if($stock==0){
        $objVariation->set_stock_status('outstock'); // in stock or out of stock value
      }
    }
    else{
      $objVariation->set_stock_status('instock');
    }
    if(($data['sku'][0]=='P' && $data['sku'][1]=='B') || $data['unlimitedStock']==1 || ($data['sku'][0]=='L' && $data['sku'][1]=='S')){
      $objVariation->set_manage_stock(false);
      $objVariation->set_stock_status('instock');
    }
    $var_attributes = array();
    foreach($data["attributes"] as $vattribute){
      $taxonomy = wc_sanitize_taxonomy_name(stripslashes($vattribute["name"])); // name of variant attribute should be same as the name used for creating product attributes
      $attr_val_slug =  wc_sanitize_taxonomy_name(stripslashes($vattribute["option"]));
      $var_attributes[$taxonomy]=$attr_val_slug;
    }
    $objVariation->set_attributes($var_attributes);
    $objVariation->set_parent_id($parent_id);
    if($data['state']=="1" || $data['state']==1){
      $objVariation->set_catalog_visibility('hidden');
    }
    else{
      $objVariation->set_catalog_visibility('visible');
    }
    $product_id=$objVariation->save();
    foreach($data["attributes"] as $vattribute){
      wp_set_object_terms($product_id,$vattribute["option"],'pa_'.wc_sanitize_taxonomy_name($vattribute["name"]),true);
    }
    update_post_meta($product_id, '_bsale_variant_id', $data['variant_id']);
    return $product_id;
  }


  public static function get_stocks(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'bsale_stocks';
    $wpdb->query('TRUNCATE TABLE '.$table_name);
    $offset = 0;
    $response = json_decode(BSale::request('stocks',[]));
    $count = $response->count;
    $pages = $count/25;
    $list_stocks = [];

    if (ob_get_level() == 0) ob_start();
    for($j=0;$j<$pages;$j++){
      $response = json_decode(BSale::request('stocks',['offset'=>$offset]));
      for($i=0;$i<count($response->items);$i++){
        $wpdb->insert(
          $table_name,
          array(
            "product_id"=>$response->items[$i]->variant->id,
            "quantity"=>$response->items[$i]->quantity,
            "quantity_reserved"=>$response->items[$i]->quantityReserved,
            "quantity_available"=>$response->items[$i]->quantityAvailable,
            "office"=>$response->items[$i]->office->id
          )
        );
      }
      $offset+=25;
      ob_flush();
      flush();
      sleep(0.1);
    }
    ob_end_flush();
  }
  public static function get_stocks_page($page){
    global $wpdb;
    $table_name = $wpdb->prefix . 'bsale_stocks';
    $offset = $page * 25;
    $response = json_decode(BSale::request('stocks',['offset'=>$offset]));
    for($i=0;$i<count($response->items);$i++){
      $exist =$wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE product_id = {$response->items[$i]->variant->id}" );
      if($exist > 0){
        $wpdb->update(
          $table_name,
          array(
            "product_id"=>$response->items[$i]->variant->id,
            "quantity"=>$response->items[$i]->quantity,
            "quantity_reserved"=>$response->items[$i]->quantityReserved,
            "quantity_available"=>$response->items[$i]->quantityAvailable,
            "office"=>$response->items[$i]->office->id
          ),
          array("product_id"=>$response->items[$i]->variant->id)
        );
      }
      else{
        $wpdb->insert(
          $table_name,
          array(
            "product_id"=>$response->items[$i]->variant->id,
            "quantity"=>$response->items[$i]->quantity,
            "quantity_reserved"=>$response->items[$i]->quantityReserved,
            "quantity_available"=>$response->items[$i]->quantityAvailable,
            "office"=>$response->items[$i]->office->id
          )
        );
      }
    }
    return $response;
  }
  public static function get_stock($id){
    $stock = 0;
    global $wpdb;
    $data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}bsale_stocks WHERE product_id = $id" );
    if($data!=NULL){
      $stock = $data->quantity;
    }
    return $stock;
  }
  public static function create_product_attribute($name){
    global $wpdb;
    $label = $name;
    $name = wc_sanitize_taxonomy_name(stripslashes($name));
    $attribute = array('attribute_name' => $name, 'attribute_label' => $label, 'attribute_type' => 'text', 'attribute_orderby' => 'menu_order', 'attribute_public' => false);
    if (empty($attr['attribute_type'])) { $attr['attribute_type'] = 'text';}
    if (empty($attr['attribute_orderby'])) { $attr['attribute_orderby'] = 'menu_order';}
    if (empty($attr['attribute_public'])) { $attr['attribute_public'] = 0;}
    $wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );
    do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
    flush_rewrite_rules();
    delete_transient( 'wc_attribute_taxonomies' );
  }



  public static function _check_stock($product_id){
    $product = wc_get_product($product_id);
    $sku = $product->get_sku();
    $query =(BSale::request('stocks',['code'=>$sku]));
    if(!$product->managing_stock()){
      return true;
    }
    if($query!=false){
      $response = json_decode($query);
      if(count($response->items)>0){
        $quantity = $response->items[0]->quantity;
        if($quantity>0){
          $stock_status = 'instock';
          update_post_meta($product_id, '_stock', $quantity);
          update_post_meta( $product_id, '_stock_status', wc_clean( $stock_status ) );
          wp_set_post_terms( $product_id, 'instock', 'product_visibility', true );
          wc_delete_product_transients( $product_id );
          return true;
        }
        else{
          $out_of_stock_staus = 'outofstock';
          update_post_meta($product_id, '_stock', 0);
          update_post_meta( $product_id, '_stock_status', wc_clean( $out_of_stock_staus ) );
          wp_set_post_terms( $product_id, 'outofstock', 'product_visibility', true );
          wc_delete_product_transients( $product_id );
          return false;
        }
      }
    }
    return true;
  }
}
