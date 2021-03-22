<?php
/**
 * Sdjpcrm\Sevdesk_Queue_Interface interface
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm;

/**
 * Interface for a plugin component that exposes plugin functions.
 */
class Sevdesk_Queue {

	/**
	 * Set and save the details about a currently active query.
	 *
	 * @param string $object     The object we are querying. E.g. Contact.
	 * @param array  $query_args The arguments about the query.
	 *
	 * @return bool True if the val
	 */
	public function set_current_active_query( $object, $query_args ) {
		$current_active_query            = is_array( get_option( 'sdjpcrm_current_active_queries' ) ) ? get_option( 'sdjpcrm_current_active_queries' ) : array();
		$current_active_query[ $object ] = $query_args;

		return update_option( 'sdjpcrm_current_active_queries', $current_active_query );
	}

	/**
	 * Get the details about a current active query.
	 *
	 * @param string $object The object we are querying. E.g. Contact.
	 *
	 * @return array         The details about the current active query.
	 */
	public function get_current_active_query( $object ) {
		$current_active_query = is_array( get_option( 'sdjpcrm_current_active_queries' ) ) ? get_option( 'sdjpcrm_current_active_queries' ) : array();
		return isset( $current_active_query[ $object ] ) ? $current_active_query[ $object ] : null;
	}
}
