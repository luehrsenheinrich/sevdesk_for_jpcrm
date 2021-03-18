<?php
/**
 * The `wp_sdjpcrm()` function.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm;

/**
 * Provides access to all available functions of the plugin.
 *
 * When called for the first time, the function will initialize the plugin.
 *
 * @return Plugin_Functions Plugin functions instance exposing plugin function methods.
 */
function wp_sdjpcrm() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new Plugin();
		$plugin->initialize();
	}

	return $plugin->plugin_functions();
}
