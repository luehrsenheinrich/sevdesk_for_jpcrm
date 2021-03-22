<?php
/**
 * _Lhpbp\Contacts\Component class
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\Contacts;
use WpMunich\sdjpcrm\Component_Interface;
use WpMunich\sdjpcrm\Sevdesk_Queue;

use function add_action;
use function load_plugin_textdomain;
use function WpMunich\sdjpcrm\wp_sdjpcrm;

/**
 * A class to handle the synchronisation of sevDesk Contacts into JetPack CRM.
 */
class Component extends Sevdesk_Queue implements Component_Interface {

	/**
	 * The current instance of the sevdesk api object.
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
		return 'contacts';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		$this->sevdesk = wp_sdjpcrm()->sevdesk();

		add_action( 'wp_footer', array( $this, 'get_next_contacts' ) );
	}

	/**
	 * Retrieve the next set of contacts from sevDesk that we want to sync into
	 * JetPack CRM.
	 *
	 * @return void
	 */
	public function get_next_contacts() {
		if ( $_GET['test'] !== '1' ) {
			return;
		}

		$args = wp_parse_args(
			$this->get_current_active_query( 'Contact' ),
			array(
				'embed'                      => 'parent,tags,category,communicationWays,addresses,addresses.category,addresses.country,hasChildren',
				'countAll'                   => true,
				'depth'                      => 1,
				'limit'                      => 100,
				'offset'                     => 0,
				'emptyState'                 => true,
				'distance'                   => 0,
				'current_active_query_start' => time(),
				'category[objectName]'       => 'Category',
				'category[id]'               => 3,
			)
		);

		$contacts = $this->sevdesk->get_contacts( $args );

		foreach ( $contacts['data'] as $c ) {
			var_dump( $c->get( 'id' ) );
			var_dump( $c->get( 'surename' ) . ' ' . $c->get( 'familyname' ) );
			var_dump( $c->get( 'name' ) );
		}

		// Iterate the offset.
		$args['offset'] = intval( $args['offset'] ) + intval( $args['limit'] );

		// If the new offset is larger than our total resultset we reset the query.
		if ( $args['offset'] >= $contacts['total'] ) {
			$args = array(
				'updateAfter' => 0,
			);
		}

		// Save the current query data.
		$this->set_current_active_query( 'Contact', $args );
	}
}
