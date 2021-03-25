<?php
/**
 * Sdjpcrm\Sevdesk_Queue_Interface interface
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\crawler;
use WpMunich\sdjpcrm\SevDesk\SevDeskAPI;
use \DateTime;

/**
 * Interface for a plugin component that exposes plugin functions.
 */
class Sevdesk_Crawler {

	/**
	 * The object name we are currently crawling.
	 *
	 * @var string
	 */
	protected $object_name = '';

	/**
	 * The instance of the sevdesk api object.
	 *
	 * @var null|object
	 */
	protected $api = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->api = new SevDeskAPI();
	}

	/**
	 * Set and save the details about a currently active crawl.
	 *
	 * @param array $crawl_args The arguments about the crawl.
	 *
	 * @return bool True if the val
	 */
	public function set_current_active_crawl( $crawl_args ) {
		$object = $this->object_name;

		$current_active_crawls            = is_array( get_option( 'sdjpcrm_current_active_crawls' ) ) ? get_option( 'sdjpcrm_current_active_crawls' ) : array();
		$current_active_crawls[ $object ] = $crawl_args;

		return update_option( 'sdjpcrm_current_active_crawls', $current_active_crawls );
	}

	/**
	 * Get the details about a current active crawl.
	 *
	 * @return array         The details about the current active query.
	 */
	public function get_current_active_crawl() {
		$object = $this->object_name;

		$current_active_crawls = is_array( get_option( 'sdjpcrm_current_active_crawls' ) ) ? get_option( 'sdjpcrm_current_active_crawls' ) : array();
		return isset( $current_active_crawls[ $object ] ) ? $current_active_crawls[ $object ] : null;
	}

	/**
	 * Reset the current active crawl.
	 */
	public function reset_current_active_crawl() {
		$this->set_current_active_crawl( array() );
	}
}
