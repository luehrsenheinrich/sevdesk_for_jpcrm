<?php
/**
 * _Lhpbp\Contacts\Component class
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\crawler;

/**
 * A class to handle the synchronisation of sevDesk Contacts into Jetpack CRM.
 */
class Contacts extends Sevdesk_Crawler {

	/**
	 * The object name we are currently crawling.
	 *
	 * @var string
	 */
	protected $object_name = 'Contact';

	/**
	 * Retrieve the next set of contacts from sevDesk that we want to sync into
	 * Jetpack CRM.
	 *
	 * @return array An array of contacts to sync.
	 */
	public function crawl_contacts() {
		$args = wp_parse_args(
			$this->get_current_active_crawl(),
			array(
				'embed'                      => 'parent,tags,category,communicationWays,addresses,addresses.category,addresses.country,hasChildren',
				'countAll'                   => true,
				'depth'                      => 1,
				'limit'                      => 10,
				'offset'                     => 0,
				'emptyState'                 => true,
				'distance'                   => 0,
				'current_active_query_start' => time(),
				'category[objectName]'       => 'Category',
				'category[id]'               => 3,
			)
		);

		// Retrieve the contacts from the sevDesk API.
		$contacts = $this->api->get_contacts( $args );

		// Iterate the offset.
		$args['offset'] = intval( $args['offset'] ) + intval( $args['limit'] );

		// If the new offset is larger than our total resultset we reset the query.
		if ( $args['offset'] >= $contacts['total'] ) {
			$args = array(
				'updateAfter' => $args['current_active_query_start'],
			);
		}

		// Save the current query data.
		$this->set_current_active_crawl( $args );

		// Return the found contacts.
		return $contacts['data'];
	}
}
