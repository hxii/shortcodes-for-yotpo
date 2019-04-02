<?php
/*
* Plugin Name: Shortcodes for Yotpo
* Description: This plugin adds the ability to use shortcodes to control the placement of Yotpo widgets.
* Version: 1.1.4
* Author: Paul Glushak
* Author URI: http://paulglushak.com/
* Plugin URI: http://paulglushak.com/shortcodes-for-yotpo/
* WC requires at least: 3.1.0
* WC tested up to: 3.6.0
*/

/*
 * This plugin allows using shortcodes to display Yotpo widgets inside and oustide (applicable widgets only) of product pages e.g. page builders, sidebars, widgets etc.
 * See example usage at the bottom.
*/

// if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
defined( 'ABSPATH' ) or die();

/**
 * Shortcodes!
 */
class Yotpo_Shortcodes
{

	function __construct()
	{
		$this->init_shortcodes();
	}

	function init_shortcodes() {
		add_shortcode( 'yotpo_widget', array( $this, 'yotpo_widget' ) );
		add_shortcode( 'yotpo_bottomline', array( $this, 'yotpo_bottomline' ) );
		add_shortcode( 'yotpo_product_gallery', array( $this, 'yotpo_product_gallery' ) );
		add_shortcode( 'yotpo_product_reviews_carousel', array( $this, 'yotpo_product_reviews_carousel' ) );
		add_shortcode( 'yotpo_badge', array( $this, 'yotpo_badge' ) );
		add_shortcode( 'yotpo_testimonials', array( $this, 'yotpo_testimonials' ) );
	}
	/**
	 * Basic dependency check, to be replaced with class requirement
	 *
	 * @return void
	 **/
	private function basic_check() {
		if ( !class_exists( 'woocommerce' ) ) {
			return;
		} elseif ( !function_exists( 'wc_yotpo_get_product_data' ) ) {
			require_once( ABSPATH . 'wp-content/plugins/yotpo-social-reviews-for-woocommerce/wc_yotpo.php' ) or die();
		}
	}

	public function yotpo_widget( $args ) {
		$this->basic_check();
		if ( isset( $args['product_id'] ) ) { $product_id = $args['product_id']; } elseif ( is_product() ) { global $product; $product_id = $product->get_id(); } else { return; }
		$_product = wc_get_product( $product_id );
		if ( is_null( $_product ) || !$_product ) { return; }
		$widget_product_data = wc_yotpo_get_product_data( $_product );
		$html = "<div class='yotpo yotpo-main-widget'
	   				data-product-id='{$product_id}'
	   				data-name='{$widget_product_data['title']}' 
	   				data-url='{$widget_product_data['url']}' 
	   				data-image-url='{$widget_product_data['image-url']}' 
	  				data-description='{$widget_product_data['description']}' 
	  				data-lang='{$widget_product_data['lang']}'
	                data-price='{$_product->get_price()}'
	                data-currency='".get_woocommerce_currency()."'></div>";
		return $html;
	}

	public function yotpo_bottomline( $args ) {
		$this->basic_check();
		if ( !class_exists( 'Yotpo' ) ) { require_once( ABSPATH . 'wp-content/plugins/yotpo-social-reviews-for-woocommerce/lib/yotpo-api/Yotpo.php' ); }
		$yotpo_settings = get_option( 'yotpo_settings' );
		if ( isset( $args['product_id'] ) ) {
			$product_id = $args['product_id'];
		} elseif ( is_product() ) {
			global $product;
			$product_id = $product->get_id();
		} else {
			return;
		}
		$data = array();
		$data['product_id'] = $product_id;
		$data['app_key'] = $yotpo_settings['app_key'];
		$yotpo = new Yotpo( $yotpo_settings['app_key'], $yotpo_settings['secret'] );
		$response = $yotpo->get_product_bottom_line( $data );
		if ( !empty( $response ) ) {
			if ( $response['status']['code'] && $response['response']['bottomline']['total_reviews'] > 0 ) {
				$widget_product_data = wc_yotpo_get_product_data( wc_get_product( $product_id ) );
				$html = "<div class='yotpo bottomLine'
							data-product-id='{$product_id}'
			   				data-url='{$widget_product_data['url']}'
			   				data-lang='{$widget_product_data['lang']}'>
		   				</div>";
			} elseif ( ( $response['response']['bottomline']['total_reviews'] == 0 ) && ( !isset( $args['0'] ) || $args['0'] != "noempty" ) ) {
				$html = "<div class='yotpo bottomline'>
							<div class='yotpo-bottomline pull-left star-clickable'>
								<span class='yotpo-stars'>
									<span class='yotpo-icon yotpo-icon-empty-star pull-left'></span>
									<span class='yotpo-icon yotpo-icon-empty-star pull-left'></span>
									<span class='yotpo-icon yotpo-icon-empty-star pull-left'></span>
									<span class='yotpo-icon yotpo-icon-empty-star pull-left'></span>
									<span class='yotpo-icon yotpo-icon-empty-star pull-left'></span>
								</span>
								<div class='yotpo-clr'></div>
							</div>
						</div><br>";
			} else {
				return;
			}
		}
		return $html;
	}

	public function yotpo_product_gallery( $args ) {
		$this->basic_check();
		if ( empty( $args['gallery_id'] ) ) { return 'Error - no gallery ID specified'; }
		$html = "<div class='yotpo yotpo-pictures-widget' data-gallery-id='{$args['gallery_id']}'";
		if ( ( !isset($args[0]) || $args[0] != 'noproduct' ) && is_product() ) {
			global $product;
			$html .= "data-product-id='{$product->get_id()}'";
		} elseif ( array_key_exists('product_id', $args) ) {
			$html .= "data-product-id='{$args['product_id']}'";
		}
		$html .= "></div>";
		return $html;
	}

	public function yotpo_product_reviews_carousel( $args ) {
		$this->basic_check();
		extract( shortcode_atts( array(
			'background_color' => 'transparent', // transparent or #color
			'mode' => 'top_rated', // top_rated or most_recent
			'type' => 'per_product', // per_product, product, both or site
			'count' => '9', // 3-9
			'show_bottomline' => '1', 
			'autoplay_enabled' => '1',
			'autoplay_speed' => '3000',
			'show_navigation' => '1'), $args ) );
		$html = "<div
			class='yotpo yotpo-reviews-carousel'
			data-background-color='{$background_color}' 
			data-mode='{$mode}' 
			data-type='{$type}' 
			data-count='{$count}' 
			data-show-bottomline='{$show_bottomline}' 
			data-autoplay-enabled='{$autoplay_enabled}' 
			data-autoplay-speed='{$autoplay_speed}' 
			data-show-navigation='{$show_navigation}'";
		if ( isset( $args['product_id'] ) ) {
			$html .= "data-product-id='{$args['product_id']}'";
		} elseif ( $mode == 'manual' && isset( $args['review-ids'] ) ) {
			$html .= "data-review-ids='{$args['review-ids']}'";
		} elseif ( isset($args[0]) && $args[0] == 'noproduct' ) {
			$html .= "";
		} elseif ( is_product() ) {
			global $product;
			$html .= "data-product-id='{$product->get_id()}'";
		} else {
			return;
		}
		$html .= '></div>';
		return $html;
	}

	public function yotpo_badge() {
		$html = "<div id='y-badges' class='yotpo yotpo-badge badge-init'>&nbsp;</div>";
		return $html;
	}

	public function yotpo_testimonials() {
		$html = "<div id='yotpo-testimonials-custom-tab'></div>";
		return $html;
	}
}

new Yotpo_Shortcodes;

?>