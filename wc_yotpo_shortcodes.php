<?php
/*
* Plugin Name: Shortcodes for Yotpo
* Description: This plugin adds the ability to use shortcodes to control the placement of Yotpo widgets.
* Version: 1.2.3
* Author: Paul Glushak
* Author URI: http://paulglushak.com/
* Plugin URI: http://paulglushak.com/shortcodes-for-yotpo/
* WC requires at least: 3.1.0
* WC tested up to: 4.1.0
*/

/*
 * This plugin allows using shortcodes to display Yotpo widgets inside and oustide (applicable widgets only) of product pages e.g. page builders, sidebars, widgets etc.
*/

defined( 'ABSPATH' ) || die();

/**
 * Shortcodes!
 */
class Yotpo_Shortcodes {

	/**
	 * Run the init function on construction via action
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init_shortcodes' ) );
	}

	/**
	 * Set short transient for thank-you message
	 *
	 * @return void
	 * @since 1.2.1
	 */
	public function activate() {
		set_transient( 'yotpo_shortcodes_message', true, 5 );
	}

	/**
	 * Registers all the shortcodes in WP
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function init_shortcodes() {
		if ( class_exists( 'YRFW_Reviews' ) ) {
			if ( get_transient( 'yotpo_shortcodes_message' ) ) {
				new YRFW_Messages( 'Thank you for installing <strong>Shortcodes for Yotpo</strong>! Please consider leaving a <a href="https://wordpress.org/support/plugin/shortcodes-for-yotpo/reviews/" target="_blank">review</a>, it really helps!', 'success', true, true );
			}
			add_shortcode( 'yotpo_widget', array( $this, 'yotpo_widget' ) );
			add_shortcode( 'yotpo_bottomline', array( $this, 'yotpo_bottomline' ) );
			add_shortcode( 'yotpo_questions', array( $this, 'yotpo_questions' ) );
			add_shortcode( 'yotpo_product_gallery', array( $this, 'yotpo_product_gallery' ) );
			add_shortcode( 'yotpo_product_reviews_carousel', array( $this, 'yotpo_product_reviews_carousel' ) );
			add_shortcode( 'yotpo_badge', array( $this, 'yotpo_badge' ) );
			add_shortcode( 'yotpo_testimonials', array( $this, 'yotpo_testimonials' ) );
			add_shortcode( 'yotpo_highlights', array( $this, 'yotpo_highlights' ) );
		} else {
			add_action(
				'admin_notices',
				function() {
					?>
					<div class="notice notice-error">
						<p>Please install and/or activate <a href="https://wordpress.org/plugins/yotpo-reviews-for-woocommerce/" target="_blank">Yotpo Reviews for WooCommerce</a> in order for Shortcodes for Yotpo to work.</p>
					</div>
					<?php
				}
			);
		}
	}

	/**
	 * Show main widget
	 *
	 * @param array $args product_id argument.
	 * @return string
	 * @since 1.2.0
	 */
	public function yotpo_widget( $args ) {
		if ( isset( $args['product_id'] ) ) {
			$product_id = $args['product_id'];
		} elseif ( is_product() ) {
			global $product;
			$product_id = $product->get_id();
		} else {
			return;
		}
		$product_handler     = YRFW_Product_Cache::get_instance();
		$widget_product_data = $product_handler->get_cached_product( $product_id );
		$html                = "<div class='yotpo yotpo-main-widget'
							data-product-id='{$product_id}'
							data-name='{$widget_product_data['name']}'
							data-url='{$widget_product_data['url']}'
							data-image-url='{$widget_product_data['image']}'
							data-description='{$widget_product_data['description']}'
							data-lang='{$widget_product_data['lang']}'
							data-price='{$widget_product_data['price']}'
							data-currency='" . get_woocommerce_currency() . "'></div>";
		return $html;
	}

	/**
	 * Show star rating widget
	 *
	 * @param array $args product_id, noempty arguments.
	 * @return string
	 * @since 1.2.0
	 */
	public function yotpo_bottomline( $args ) {
		if ( ! class_exists( 'YRFW_API_Wrapper' ) ) {
			require_once YRFW_PLUGIN_PATH . 'inc/Helpers/class-yrfw-api-wrapper.php';
		}
		if ( ! array_key_exists( 'settings_instance', $GLOBALS ) ) {
			$settings_instance = ( YRFW_Settings_File::get_instance() )->get_settings();
		} else {
			global $settings_instance;
		}
		if ( isset( $args['product_id'] ) ) {
			$product_id = $args['product_id'];
		} elseif ( is_product() ) {
			global $product;
			$product_id = $product->get_id();
		} else {
			return;
		}
		$curl = YRFW_API_Wrapper::get_instance();
		$curl->init( $settings_instance['app_key'], $settings_instance['secret'] );
		$response = json_decode( $curl->get_product_bottomline( $product_id ) );
		$html     = '';
		if ( ! empty( $response ) ) {
			if ( 200 === $response->status->code && $response->response->bottomline->total_reviews > 0 ) {
				$product_handler     = YRFW_Product_Cache::get_instance();
				$widget_product_data = $product_handler->get_cached_product( $product_id );
				$html                = "<div class='yotpo bottomLine'
									data-product-id='{$product_id}'
									data-url='{$widget_product_data['url']}'
									data-lang='{$widget_product_data['lang']}'>
									</div>";
				if ( is_category() || is_archive() ) {
					$html = "<a href='{$widget_product_data['url']}'>{$html}</a>";
				}
			} elseif ( ! isset( $args['0'] ) || 'noempty' !== $args['0'] ) {
				return $this->show_empty_bottomline();
			} else {
				return;
			}
		}
		return $html;
	}

	/**
	 * Show questions widget
	 *
	 * @param array $args product_id argument.
	 * @return void | string
	 * @since 1.2.1
	 */
	public function yotpo_questions( $args ) {
		$product_id = '';
		if ( isset( $args['product_id'] ) ) {
			$product_id = $args['product_id'];
		} elseif ( is_product() ) {
			global $product;
			$product_id = $product->get_id();
		} else {
			return;
		}
		$html = "<div class='yotpo QABottomLine' data-product-id='$product_id'></div>";
		return $html;
	} 

	/**
	 * Show empty star rating code
	 *
	 * @return string
	 * @since 1.2.0
	 */
	private function show_empty_bottomline() {
		return "<div class='yotpo bottomline'>
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
				</div>";
	}

	/**
	 * Show gallery widget
	 *
	 * @param array $args arguments accepts 'gallery_id', 'product_id' and 'noproduct'.
	 * @return string
	 * @since 1.2.0
	 */
	public function yotpo_product_gallery( $args ) {
		if ( empty( $args['gallery_id'] ) ) { return 'Error - no gallery ID specified'; }
		$html = "<div class='yotpo yotpo-pictures-widget' data-gallery-id='{$args['gallery_id']}'";
		if ( ( ! isset( $args[0] ) || 'noproduct' !== $args[0] ) && is_product() ) {
			global $product;
			$html .= "data-product-id='{$product->get_id()}'";
		} elseif ( array_key_exists( 'product_id', $args ) ) {
			$html .= "data-product-id='{$args['product_id']}'";
		}
		$html .= '></div>';
		return $html;
	}

	/**
	 * Show reviews carousel widget
	 *
	 * @param array $args arguments.
	 * @return void
	 * @since 1.2.0
	 */
	public function yotpo_product_reviews_carousel( $args ) {
		extract(
			shortcode_atts(
				array(
					'background_color' => 'transparent', // transparent or #color
					'mode'             => 'top_rated', // top_rated or most_recent
					'type'             => 'per_product', // per_product, product, both or site
					'count'            => '9', // 3-9
					'show_bottomline'  => '1',
					'autoplay_enabled' => '1',
					'autoplay_speed'   => '3000',
					'show_navigation'  => '1',
				),
				$args
			)
		);
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
		} elseif ( 'manual' === $mode && isset( $args['review-ids'] ) ) {
			$html .= "data-review-ids='{$args['review-ids']}'";
		} elseif ( isset( $args[0] ) && 'noproduct' === $args[0] ) {
			$html .= '';
		} elseif ( is_product() ) {
			global $product;
			$html .= "data-product-id='{$product->get_id()}'";
		} else {
			return;
		}
		$html .= '></div>';
		return $html;
	}

	/**
	 * Show badge widget
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function yotpo_badge() {
		$html = "<div id='y-badges' class='yotpo yotpo-badge badge-init'>&nbsp;</div>";
		return $html;
	}

	/**
	 * Show testimonials widget
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function yotpo_testimonials() {
		$html = "<div id='yotpo-testimonials-custom-tab'></div>";
		return $html;
	}

	/**
	 * Show review highlights widget
	 *
	 * @param array $args arguments.
	 * @return void | string
	 * @since 1.2.1
	 */
	public function yotpo_highlights( $args ) {
		$product_id = '';
		if ( isset( $args['product_id'] ) ) {
			$product_id = $args['product_id'];
		} elseif ( is_product() ) {
			global $product;
			$product_id = $product->get_id();
		} else {
			return;
		}
		$html = "<div class='yotpo yotpo-shoppers-say' data-product-id='{$product_id}'>&nbsp;</div>";
		return $html;
	}
}

register_activation_hook( __FILE__, array( 'Yotpo_Shortcodes', 'activate' ) );

$shortcodes = new Yotpo_Shortcodes();
