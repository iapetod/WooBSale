<?php

if( ! class_exists( 'BSale_List_Table' ) ) {
    require( plugin_dir_path(__FILE__) . 'class-wp-list-table.php' );
}

class Product_List_Table extends BSale_List_Table {
  function get_columns(){
    $columns = array(
      'cb'        => '<input type="checkbox" />',
      'name' => 'Nombre',
      'variants'    => 'Variantes',
      'updater'    => '¿Sincronizar producto?',
      'stockControl'      => '¿Control de stock?'
    );
    return $columns;
  }

  function prepare_items() {
    $table_data = BSale::products();
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    usort( $table_data, array( &$this, 'usort_reorder' ) );
    $per_page = 300;
    $current_page = $this->get_pagenum();
    $total_items = count($table_data);

    // only ncessary because we have sample data
    $found_data = array_slice($table_data,(($current_page-1)*$per_page),$per_page);

    $this->set_pagination_args( array(
      'total_items' => $total_items,                  //WE have to calculate the total number of items
      'per_page'    => $per_page                     //WE have to determine how many items to show on a page
    ) );
    $this->items = $found_data;
  }
  function usort_reorder( $a, $b ) {
    // If no sort, default to title
    $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';
    // If no order, default to asc
    $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
    // Determine sort order
    $result = strcmp( $a[$orderby], $b[$orderby] );
    // Send final sort direction to usort
    return ( $order === 'asc' ) ? $result : -$result;
  }
  function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'name':
        return $item[ $column_name ];
      case 'variants':
        $variants = json_decode($item[ $column_name ]);
        for($i=0;$i<count($variants);$i++){
          $description="";
          if($variants[$i]->description!=null){
            $description=' ('.($variants[$i]->description).')';
          }
          $variant[] = $variants[$i]->code.$description;
        }
        return implode('<br>',$variant);
      case 'updater':
      $updater='No';
      if($item[ $column_name ]=='1'){
        $updater='Si';
      }
      return $updater;
      case 'stockControl':
        $stockControl='No';
        if($item[ $column_name ]=='1'){
          $stockControl='Si';
        }
        return $stockControl;
      default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }
  function column_updater($item) {
    $actions = array(
              'edit'      => sprintf('<a href="?page=%s&action=%s&tab=products&product=%s">Sincronizar</a>',$_REQUEST['page'],'updater',$item['id']),
              'delete'    => sprintf('<a href="?page=%s&action=%s&tab=products&product=%s">No sincronizar</a>',$_REQUEST['page'],'noupdater',$item['id']),
          );
    $updater='No sincronizar';
    if($item['updater']=='1'){
      $updater='Sincronizar';
    }
    return sprintf('%1$s %2$s', $updater, $this->row_actions($actions) );
  }
  function get_bulk_actions() {
    $actions = array(
      'updater'    => 'Sincronizar',
      'noupdater' => 'No sincronizar'
    );
    return $actions;
  }
  function column_cb($item) {
      return sprintf(
          '<input type="checkbox" name="book[]" value="%s" />', $item['id']
      );
  }
  function get_sortable_columns() {
    $sortable_columns = array(
      'name'  => array('name',false),
      'updater' => array('description',false),
      'stockControl'   => array('stockControl',false)
    );
    return $sortable_columns;
  }
}
