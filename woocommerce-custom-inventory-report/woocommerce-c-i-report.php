<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $start_date, $end_date, $woocommerce, $wpdb;

		$args = array(
		'post_type'			=> 'product',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			array(
				'key' 		=> '_manage_stock',
				'value' 	=> 'yes'
			),
		),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'name',
				'terms' 	=> array('simple'),
				'operator' 	=> 'IN'
			)
		),
		'fields' => 'id=>parent'
	);

	$low_stock_products = (array) get_posts($args);
	
	// Get low stock product variations
	$args = array(
		'post_type'			=> 'product_variation',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			array(
				'key' 		=> '_stock',
				'value' 	=> array( '', false, null ),
				'compare' 	=> 'NOT IN'
			)
		),
		'fields' => 'id=>parent'
	);

	$low_stock_variations = (array) get_posts($args);

	// Get low stock variable products (where stock is set for the parent)
	$args = array(
		'post_type'			=> array('product'),
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' 		=> '_manage_stock',
				'value' 	=> 'yes'
			),
		),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'name',
				'terms' 	=> array('variable'),
				'operator' 	=> 'IN'
			)
		),
		'fields' => 'id=>parent'
	);

	$low_stock_variable_products = (array) get_posts($args);
	
	// Get products marked out of stock
	$args = array(
		'post_type'			=> array( 'product' ),
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> -1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' 		=> '_stock_status',
				'value' 	=> 'outofstock'
			)
		),
		'fields' => 'id=>parent'
	);

	$out_of_stock_status_products = (array) get_posts($args);
	
	// Merge results
	$low_in_stock = apply_filters( 'woocommerce_reports_stock_overview_products', $low_stock_products + $low_stock_variations + $low_stock_variable_products + $out_of_stock_status_products );
	
	?>
    <div class="wrap">
		<div id="icon-options-general" class="icon32"></div><h2><?php _e( 'Custom Inventory Report', 'wc_cir' ); ?></h2>
        
		<div id="poststuff" class="woocommerce-reports-wrap halved">
		<div class="woocommerce-reports-left">
			<div class="postbox">
				<h3><span><?php _e( 'Low stock', 'woocommerce' ); ?></span></h3>
				<div class="inside">
                <table width="100%" border="0"><tr><td><strong><?php _e( 'Product', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Stock', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Preferred stock', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Backorder', 'wc_cir' ); ?></strong></td></tr>
					<?php
					if ( $low_in_stock ) {
						//echo '<ul class="stock_list">';
						foreach ( $low_in_stock as $product_id => $parent ) {

							$stock 	= (int) get_post_meta( $product_id, '_stock', true );
							$sku	= get_post_meta( $product_id, '_sku', true );
							$ds	= (int) get_post_meta( $product_id, 'wc_cir_des_stock', true );
							$io	= (int) get_post_meta( $product_id, 'wc_cir_inv_order', true );

							if ( $stock <= $nostockamount || in_array( $product_id, array_keys( $out_of_stock_status_products ) ) )
								continue;
							if ($stock >= ($ds-$io))
								continue;

							$title = esc_html__( get_the_title( $product_id ) );

							if ( $sku )
								$title .= ' (' . __( 'SKU', 'woocommerce' ) . ': ' . esc_html( $sku ) . ')';

							if ( get_post_type( $product_id ) == 'product' )
								$product_url = admin_url( 'post.php?post=' . $product_id . '&action=edit' );
							else
								$product_url = admin_url( 'post.php?post=' . $parent . '&action=edit' );
								
							printf( '<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td></tr>', $product_url, $title, $stock, $ds, $io );

						}

					} else {
						echo '<p>'.__( 'No products are low in stock.', 'woocommerce' ).'</p>';
					}
					?>
                    </table>
				</div>
			</div>
		</div>
		<div class="woocommerce-reports-right">
			<div class="postbox">
				<h3><span><?php _e( 'Out of stock', 'woocommerce' ); ?></span></h3>
				<div class="inside">
                <table width="100%" border="0"><tr><td><strong><?php _e( 'Product', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Stock', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Preferred stock', 'wc_cir' ); ?></strong></td><td><strong><?php _e( 'Backorder', 'wc_cir' ); ?></strong></td></tr>
					<?php
					if ( $low_in_stock ) {
						echo '<ul class="stock_list">';
						foreach ( $low_in_stock as $product_id => $parent ) {

							$stock 	= get_post_meta( $product_id, '_stock', true );
							$sku	= get_post_meta( $product_id, '_sku', true );
							$io	= (int) get_post_meta( $product_id, 'wc_cir_inv_order', true );

							if ( $stock > $nostockamount && ! in_array( $product_id, array_keys( $out_of_stock_status_products ) ) )
								continue;

							$title = esc_html__( get_the_title( $product_id ) );

							if ( $sku )
								$title .= ' (' . __( 'SKU', 'woocommerce' ) . ': ' . esc_html( $sku ) . ')';

							if ( get_post_type( $product_id ) == 'product' )
								$product_url = admin_url( 'post.php?post=' . $product_id . '&action=edit' );
							else
								$product_url = admin_url( 'post.php?post=' . $parent . '&action=edit' );

							if ( $stock == '' )
								printf( '<li><a href="%s"><small>' .  __('Marked out of stock', 'woocommerce') . '</small> %s</a></li>', $product_url, $title );
							else
								printf( '<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td></tr>' , $product_url, $title, $stock, $io);

						}
						echo '</ul>';
					} else {
						echo '<p>'.__( 'No products are out in stock.', 'woocommerce' ).'</p>';
					}
					?>
                </table>
				</div>
			</div>
		</div>
	</div>
    </div>