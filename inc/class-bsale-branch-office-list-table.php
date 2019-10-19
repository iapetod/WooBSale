<?php

if( ! class_exists( 'BSale_List_Table' ) ) {
    require( plugin_dir_path(__FILE__) . 'class-wp-list-table.php' );
}

class BranchOffice_List_Table extends BSale_List_Table {
  function get_columns(){
    $columns = array(
      'cb'        => '<input type="checkbox" />',
      'name' => 'Sucursal',
      'status'      => 'Estado'
    );
    return $columns;
  }

  function prepare_items() {
    $table_data = BSale::branch_office();
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    usort( $table_data, array( &$this, 'usort_reorder' ) );
    $per_page = 5;
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
      case 'status':
        return $item[ $column_name ];
      default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }
  function column_status($item) {
    $actions = array(
              'edit'      => sprintf('<a href="?page=%s&action=%s&tab=branch_office&office=%s">Habilitar</a>',$_REQUEST['page'],'enable_branch_offices',$item['ID']),
              'delete'    => sprintf('<a href="?page=%s&action=%s&tab=branch_office&office=%s">Deshabilitar</a>',$_REQUEST['page'],'disable_branch_offices',$item['ID']),
          );
    $state = "Deshabilitada";
    if( $item[ 'status' ] ){
      $state = 'Habilitada';
    }
    return sprintf('%1$s %2$s', $state, $this->row_actions($actions) );
  }
  function get_bulk_actions() {
    $actions = array(
      'enable_branch_offices'    => 'Habilitar',
      'disable_branch_offices'   => 'Deshabilitar'
    );
    return $actions;
  }
  function column_cb($item) {
      return sprintf(
          '<input type="checkbox" name="branch_office[]" value="%s" />', $item['ID']
      );
  }
  function get_sortable_columns() {
    $sortable_columns = array(
      'name'  => array('name',false),
      'status'   => array('status',false)
    );
    return $sortable_columns;
  }
}
