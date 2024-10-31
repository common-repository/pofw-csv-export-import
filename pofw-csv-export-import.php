<?php
/**
 * Plugin Name: POFW Csv Export-Import
 * Description: Csv export-import for the "Simple Product Options for WooCommerce" plugin. To export options you should use the WooCommerce product export feature. The exported .csv file will have a "Product Options" column. 
 * Version: 1.0.0
 * Author: Pektsekye
 * Author URI: http://hottons.com
 * License: GPLv2     
 * Requires at least: 4.7
 * Tested up to: 6.5.5
 *
 * Text Domain: pofw-csv-export-import
 *
 * WC requires at least: 3.0
 * WC tested up to: 8.8.5
 * 
 * @package PofwCsvExportImport
 * @author Pektsekye
 */
if (!defined('ABSPATH')) exit;

final class Pektsekye_PofwCsvExportImport {


  protected static $_instance = null;

  protected $_pluginPath;    
  protected $_pluginUrl;  


  public static function instance(){
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
      self::$_instance->initApp();
    }
    return self::$_instance;
  }


  public function __construct(){
    $this->_pluginPath = plugin_dir_path(__FILE__);
    $this->_pluginUrl  = plugins_url('/', __FILE__ );    
  }


  public function initApp(){
    $this->includes();
    $this->init_hooks();
  }
  
  
  public function includes(){  
    include_once('Model/Observer.php');              
  }
  

  private function init_hooks(){ 
    new Pektsekye_PofwCsvExportImport_Model_Observer(); 
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 11); // after WooCommerce             
  }    
  

  public function enqueue_admin_scripts() {
    if (isset($_GET['page']) && $_GET['page'] == 'product_importer' && isset($_GET['step']) && $_GET['step'] == 'mapping') {		
      wp_enqueue_style('pofw-csv-import-export', $this->_pluginUrl . 'view/adminhtml/web/import/main.css');
		}  
  }
    
  
  public function getPluginPath(){
    return $this->_pluginPath;
  }  
  
  
  public function getPluginUrl() {
    return $this->_pluginUrl;
  }
      
}


function Pektsekye_POEI(){
	return Pektsekye_PofwCsvExportImport::instance();
}


// If WooCommerce plugin is installed and active.
if (in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins', array())) || in_array('woocommerce/woocommerce.php', array_keys((array) get_site_option('active_sitewide_plugins', array())))){
  Pektsekye_POEI();
}









