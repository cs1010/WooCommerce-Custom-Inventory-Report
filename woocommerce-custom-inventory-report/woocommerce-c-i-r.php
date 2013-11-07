<?php
/*
Plugin Name: WooCommerce Custom Inventory Report
Plugin URI: 
Description: WooCommerce Custom Inventory Report
Author: Corné
Author URI: 
Version: 1.0.0

	Copyright: © 2013  WooCommerce Custom Inventory Report (email : corne.schipper@gmail.com)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	if ( ! class_exists( 'WC_CIR' ) ) {
		
		/**
		 * Localisation
		 **/
		load_plugin_textdomain( 'wc_cir', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		class WC_CIR {
			public function __construct() {
				// called only after woocommerce has finished loading
				add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );
				
				// called after all plugins have loaded
				add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
				
				// called just before the woocommerce template functions are included
				add_action( 'init', array( &$this, 'include_template_functions' ), 20 );
				
				// indicates we are running the admin
				if ( is_admin() ) {
					add_action('admin_menu', 'register_custom_inv_report_submenu_page');

					function register_custom_inv_report_submenu_page() {
    					add_submenu_page( 'woocommerce', __( 'Custom Inventory Report', 'wc_cir' ), __( 'Custom Inventory Report', 'wc_cir' ), 'manage_options', 'tab-submenu-page', 'tab_submenu_report_page_callback' ); 
					}

					function tab_submenu_report_page_callback() {
					include('woocommerce-c-i-report.php');
					
					}

				}
				
				// indicates we are being served over ssl
				if ( is_ssl() ) {
					// ...
				}
    
				// take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor
				
				add_action( 'woocommerce_product_options_stock_fields', 'wc_custom_product_inventory_field' );
					function wc_custom_product_inventory_field() {
    				woocommerce_wp_text_input( array( 
						'id' => 'wc_cir_des_stock', 
						'label' => __( 'Preferred stock',  'wc_cir' ),
						'placeholder' => __( 'Leave blank for 0', 'wc_cir' ),
						'description' => __( 'How many are preferred to have on stock.', 'wc_cir' )
					) );
					woocommerce_wp_text_input( array( 
						'id' => 'wc_cir_inv_order', 
						'label' => __( 'Backorder', 'wc_cir' ),
						'placeholder' => __( 'Leave blank for 0', 'wc_cir' ) ,
						'description' => __( 'How many do you have in backorder.', 'wc_cir' )
					) );
					}
					
				add_action( 'save_post', 'wc_cir_save_product' );
					function wc_cir_save_product( $product_id ) {
    				// If this is a auto save do nothing, we only save when update button is clicked
					if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
						return;
					
						if ( is_numeric( $_POST['wc_cir_des_stock'] ) or is_null( $_POST['wc_cir_des_stock'] ) )
						update_post_meta( $product_id, 'wc_cir_des_stock', $_POST['wc_cir_des_stock'] );
						update_post_meta( $product_id, 'wc_cir_inv_order', $_POST['wc_cir_inv_order'] );	
					}	
			}
			
			/**
			 * Take care of anything that needs woocommerce to be loaded.  
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {
				// ...
			}
			
			/**
			 * Take care of anything that needs all plugins to be loaded
			 */
			public function plugins_loaded() {
				// ...
			}
			
			/**
			 * Override any of the template functions from woocommerce/woocommerce-template.php 
			 * with our own template functions file
			 */
			public function include_template_functions() {
				include( 'woocommerce-template.php' );
			}
		}

		// finally instantiate our plugin class and add it to the set of globals
		$GLOBALS['wc_cir'] = new WC_CIR();
	}
}