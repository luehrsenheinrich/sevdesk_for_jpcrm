<?php
/**
 * Sdjpcrm\SevDesk\Component class
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;
use WpMunich\sdjpcrm\Component_Interface;

/**
 * A class to handle textdomains and other i18n related logic..
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'sevdesk';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
	}
}
