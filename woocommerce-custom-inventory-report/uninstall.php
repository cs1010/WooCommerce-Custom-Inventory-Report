<?php 
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_post_meta_by_key( 'wc_cir_des_stock' ); 
delete_post_meta_by_key( 'wc_cir_inv_order' ); 

?>