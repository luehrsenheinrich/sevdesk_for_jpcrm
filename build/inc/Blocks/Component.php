<?php
/**
 * Lhplugin\Blocks\Component class
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\Blocks;
use WpMunich\sdjpcrm\Component_Interface;
use function add_action;
use function acf_register_block_type;

/**
 * A class to handle the plugins blocks.
 */
class Component implements Component_Interface {
	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'blocks';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		if ( function_exists( 'acf_register_block_type' ) ) {
			add_action( 'acf/init', array( $this, 'register_acf_block_types' ) );
		}
		add_filter( 'block_categories', array( $this, 'add_block_categories' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register ACF driven blocks.
	 *
	 * @return void
	 */
	public function register_acf_block_types() {
		acf_register_block_type(
			array(
				'name'            => 'acf-demo-block',
				'title'           => __( 'Demo Block', 'sdjpcrm' ),
				'description'     => __( 'A demo block to show that everything is working.', 'sdjpcrm' ),
				'category'        => 'sdjpcrm-blocks',
				'icon'            => 'screenoptions',
				'keywords'        => array( __( 'ACF', 'sdjpcrm' ), __( 'Demo', 'sdjpcrm' ), __( 'Block', 'sdjpcrm' ) ),
				'render_template' => apply_filters( 'lh_acf_block_template_path', SDJPCRM_PATH . 'blocks/acf/template.php', 'acf-demo-block' ),
				'mode'            => 'auto',
				'supports'        => array(
					'align' => array( 'wide', 'full' ),
					'mode'  => 'auto',
				),
			)
		);
	}

	/**
	 * Register the plugins custom block category.
	 *
	 * @param array   $categories The block categories.
	 * @param WP_Post $post     The current post that is edited.
	 */
	public function add_block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'sdjpcrm-blocks',
					'title' => __( 'Luehrsen // Heinrich', 'sdjpcrm' ),
				),
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		$script_asset = include( SDJPCRM_PATH . 'blocks/block-helper.min.asset.php' ); // phpcs:ignore
		wp_enqueue_script( 'sdjpcrm-block-helper', SDJPCRM_PATH . 'blocks/block-helper.min.js', array_merge( $script_asset['dependencies'], array() ), SDJPCRM_VERSION, true );
	}
}
