<?php
if (!defined('ABSPATH')) exit;

class Pektsekye_PofwCsvExportImport_Model_Observer {  

  protected $_poeiOption;               

  protected $_productOptionsData;
  
  protected $_databaseTablesExist;  
        
        
  public function __construct(){ 
    include_once(Pektsekye_POEI()->getPluginPath() . 'Model/Option.php' );
    $this->_poeiOption = new Pektsekye_PofwCsvExportImport_Model_Option();
      		  
    add_filter("woocommerce_product_export_product_default_columns", array($this, 'add_ymm_column_to_wc_csv_columns'));   
    add_filter("woocommerce_product_export_row_data", array($this, 'add_ymm_to_wc_csv_row_data'), 10, 2);
    add_filter("woocommerce_csv_product_import_mapping_options", array($this, 'add_ymm_to_wc_csv_mapping_options'), 10, 2);
    add_filter("woocommerce_csv_product_import_mapping_default_columns", array($this, 'add_ymm_to_wc_csv_default_columns'));   
    add_filter("woocommerce_product_importer_formatting_callbacks", array($this, 'save_ymm_from_wc_csv_import_skip_formatting'), 10, 2);
    add_action("woocommerce_product_import_inserted_product_object", array($this, 'save_ymm_from_wc_csv_import_inserted_product'), 10, 2); 	          		
  }	  

 
  public function add_ymm_column_to_wc_csv_columns($columns) {
    return array_slice($columns, 0, 4, true) + array('pofw_options' => __('Product Options', 'pofw-csv-export-import')) + array_slice($columns, 4, NULL, true);  	
  }


  public function add_ymm_to_wc_csv_row_data($row, $product) {
    if (!isset($row['pofw_options'])){
      return $row;
    }
    
    $options = $this->getDatabaseTablesExist() ? $this->getProductOptionsData() : array();
    if (isset($options[$product->get_id()])){
      $row['pofw_options'] = json_encode($options[$product->get_id()]);
    }
    	   
    return $row;
  }
  
  
  public function add_ymm_to_wc_csv_mapping_options($options, $item) {
    return array_slice($options, 0, 4, true) + array('pofw_options' => __('Product Options', 'pofw-csv-export-import')) + array_slice($options, 4, NULL, true);  
  }


  public function add_ymm_to_wc_csv_default_columns($columns) {
    $key = __('Product Options', 'pofw-csv-export-import');
  	$columns[$key] = 'pofw_options';
    return $columns;  
  }
   

  public function save_ymm_from_wc_csv_import_skip_formatting($callbacks, $object) {  
		$mapped_keys = $object->get_mapped_keys();
		$column_index = array_search('pofw_options', $mapped_keys, true);
		if ($column_index !== false){
      $callbacks[$column_index] = array($object, 'parse_skip_field');
    }
    return $callbacks;  	
  }
  
  
  public function save_ymm_from_wc_csv_import_inserted_product($object, $data) {

    $productId = $object->get_id();

    if (isset($data['pofw_options'])){

      $optionsJson = $data['pofw_options'];
      
      $options = array();
      if (!empty($optionsJson)){
        $options = json_decode($optionsJson, true);// it returns null on error
      }
 
      if ($this->getDatabaseTablesExist() && (empty($optionsJson) || !empty($options))){
        $this->_poeiOption->deleteProductOptions($productId);
      }
      
      if (!empty($options)){
      
        if (!$this->getDatabaseTablesExist()) {
          throw new Exception(__('Product options database table does not exist. The "Simple Product Options for WooCommerce" plugin should be installed and activated.', 'pofw-csv-export-import'));     
        }     

        if (!isset($options[0]['title'])) {
          throw new Exception(__('Product Options field. Option title is required parameter in the JSON string. Example: [{"title":"Choose date","price":10.00,"type":"radio","required":1,"sort_order":2,"values":[{"title":"10:00-10:30","price":10.00,"sort_order":1}]}]', 'pofw-csv-export-import'));     
        }

        if (!isset($options[0]['type'])) {
          throw new Exception(__('Product Options field. Option type is required parameter in the JSON string. Valid option types are: drop_down, radio, checkbox, multiple, field, area. Example: [{"title":"Choose date","price":10.00,"type":"radio","required":1,"sort_order":2,"values":[{"title":"10:00-10:30","price":10.00,"sort_order":1}]}]', 'pofw-csv-export-import'));     
        }

        if (isset($options[0]['values'][0]) && !isset($options[0]['values'][0]['title'])) {
          throw new Exception(__('Product Options field. Option value title is required parameter in the JSON string. Example: [{"title":"Choose date","price":10.00,"type":"radio","required":1,"sort_order":2,"values":[{"title":"10:00-10:30","price":10.00,"sort_order":1}]}]', 'pofw-csv-export-import'));     
        }
      
        $this->_poeiOption->addProductOptions($productId, $options);
        
      } elseif (!empty($optionsJson)){
        throw new Exception(__('Product Options field should contain a valid JSON string. Example: [{"title":"Choose date","price":10.00,"type":"radio","required":1,"sort_order":2,"values":[{"title":"10:00-10:30","price":10.00,"sort_order":1}]}]', 'pofw-csv-export-import'));        
      }      
    }

  }
  
  
  public function getProductOptionsData(){    
    if (!isset($this->_productOptionsData)){
      $this->_productOptionsData = $this->_poeiOption->getProductOptionsData();          
    }
    return $this->_productOptionsData;
  } 
  		

  public function getDatabaseTablesExist(){    
    if (!isset($this->_databaseTablesExist)){
      $this->_databaseTablesExist = $this->_poeiOption->databaseTablesExist();          
    }
    return $this->_databaseTablesExist;
  }
		
}
