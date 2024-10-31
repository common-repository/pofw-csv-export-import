<?php
if (!defined('ABSPATH')) exit;

class Pektsekye_PofwCsvExportImport_Model_Option {
           
  protected $_wpdb;          
   
      
  public function __construct(){
    global $wpdb;    
    $this->_wpdb = $wpdb;            		
  }	


  public function getProductOptionsData(){

      $select = "SELECT * FROM {$this->_wpdb->base_prefix}pofw_product_option_value ORDER BY sort_order, title";
      $result = $this->_wpdb->get_results($select, ARRAY_A);
      
      $result = apply_filters('pofw_csv_export_data_option_value_rows', $result);
     
      $values = array();
      foreach ($result as $r){
      
        $optionId = $r['option_id'];
        
        unset($r['value_id']);
        unset($r['option_id']);              
        unset($r['product_id']);
        
        $r['price'] = (float) $r['price'];
        $r['sort_order'] = (int) $r['sort_order'];
                               
        $values[$optionId][] = $r;
      }
    
      $select = "SELECT * FROM {$this->_wpdb->base_prefix}pofw_product_option ORDER BY sort_order, title";
      $result = $this->_wpdb->get_results($select, ARRAY_A);
            
      $result = apply_filters('pofw_csv_export_data_option_rows', $result);
      
      $options = array();
      foreach ($result as $r){
      
        $productId = $r['product_id'];
        $optionId = $r['option_id']; 
               
        unset($r['option_id']);              
        unset($r['product_id']);
        
        $r['price'] = (float) $r['price'];
        $r['required'] = (int) $r['required'];        
        $r['sort_order'] = (int) $r['sort_order'];      
                
        $r['values'] = isset($values[$optionId]) ? $values[$optionId] : array();
              
        $options[$productId][] = $r;
      }
      
      return $options;
  } 


  public function addProductOptions($productId, $options){    
    $productId = (int) $productId;
    
    foreach($options as $k => $option){
          
      $title = esc_sql($option['title']);
      $type = esc_sql($option['type']);
      $required = isset($option['required']) && $option['required'] == 1 ? 1 : 0;
      $sortOrder = isset($option['sort_order']) ? (int) $option['sort_order'] : 0;
      $price = isset($option['price']) ? (float) $option['price'] : 0;               
      
      $this->_wpdb->query("INSERT INTO {$this->_wpdb->base_prefix}pofw_product_option SET product_id = {$productId}, title = '{$title}', type = '{$type}', required = {$required}, sort_order = {$sortOrder}, price = {$price}");            
      $optionId = $this->_wpdb->insert_id;  

      if (isset($option['values'])){

        foreach($option['values'] as $kk => $value){
                             
          $title = esc_sql($value['title']);
          $price = isset($value['price']) ? (float) $value['price'] : 0;         
          $sortOrder = isset($value['sort_order']) ? (int) $value['sort_order'] : 0;
                      
          $this->_wpdb->query("INSERT INTO {$this->_wpdb->base_prefix}pofw_product_option_value SET product_id = {$productId}, option_id = {$optionId}, title = '{$title}', price = {$price}, sort_order = {$sortOrder}");                             
          $valueId = $this->_wpdb->insert_id;
          
          $options[$k]['values'][$kk]['value_id'] = $valueId;
        }        
      }
     
      $options[$k]['option_id'] = $optionId;     
    }
    
    do_action('pofw_csv_import_product_options_saved', $productId, $options);           
  }


  public function deleteProductOptions($productId){    
    $productId = (int) $productId;    
    $this->_wpdb->query("DELETE FROM {$this->_wpdb->base_prefix}pofw_product_option_value WHERE product_id = {$productId}");          
    $this->_wpdb->query("DELETE FROM {$this->_wpdb->base_prefix}pofw_product_option WHERE product_id = {$productId}");                                   
  }


  public function databaseTablesExist(){    
    $optionTable = $this->_wpdb->get_var("SHOW TABLES LIKE '{$this->_wpdb->base_prefix}pofw_product_option'");  
    $optionValueTable = $this->_wpdb->get_var("SHOW TABLES LIKE '{$this->_wpdb->base_prefix}pofw_product_option_value'");
    return !empty($optionTable) && !empty($optionValueTable);                                   
  }


}
