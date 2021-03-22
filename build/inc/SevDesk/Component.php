<?php
/**
 * Sdjpcrm\SevDesk\Component class
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;
use WpMunich\sdjpcrm\Component_Interface;
use WpMunich\sdjpcrm\Plugin_Function_Interface;

/**
 * A class to provide access to the sevdesk api object in a managable way.
 *
 * <code>
 * <?php
 *     $sevdesk = wp_sdjpcrm()->sevdesk();
 *     $contacts = $sevdesk->get_contacts( $args );
 * ?>
 * </code>
 */
class Component implements Component_Interface, Plugin_Function_Interface {

	/**
	 * The currently active instance for the sevdesk api object.
	 *
	 * @var null|object
	 */
	private $sevdesk = null;

	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'sevdesk';
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_sdjpcrm()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function plugin_functions() {
		return array(
			'sevdesk' => array( $this, 'get_instance' ),
		);
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		$this->sevdesk = new SevDesk();
		add_filter( 'zbs_approved_sources', array( $this, 'add_external_source' ) );
	}

	/**
	 * Return the active instance.
	 *
	 * @return object The current sevdesk instance.
	 */
	public function get_instance() {
		return $this->sevdesk;
	}

	public function add_external_source( $external_sources = array() ) {
			$external_sources['sevdesk'] = array( 'sevDesk', 'ico' => 'fa-stripe' );
			return $external_sources;
	}
}
