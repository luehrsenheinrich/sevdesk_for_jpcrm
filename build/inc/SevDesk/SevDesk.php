<?php
/**
 * The main class to interact with sevDesk data.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;
/**
 * Class SevDesk
 */
class SevDesk extends SevDeskAPI {


	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get contacts from the sevDesk API.
	 *
	 * @see https://5677.extern.sevdesk.dev/apiOverview/index.html#/doc-contacts
	 *
	 * @param  array $args An array of arguments for the api.
	 *
	 * @return array       An array of contacts.
	 */
	public function get_contacts( $args = array() ) {
		$url = add_query_arg(
			$args,
			$this->api_url . 'Contact'
		);

		$response = wp_remote_request(
			$url,
			array(
				'method'  => 'GET',
				'headers' => array(
					'Authorization' => $this->api_token,
				),
			)
		);

		$data = $this->validate_wp_response( $response );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$response_body = json_decode( $data['body'], true );

		$results = array(
			'data' => array(),
		);
		foreach ( $response_body['objects'] as $contact ) {
			$cache_key = 'sevdesk_' . strtolower( $contact['objectName'] ) . '_' . $contact['id'];
			wp_cache_set( $cache_key, $contact, 'sevdesk' );
			$results['data'][] = new models\Contact( $contact );
		}

		if ( isset( $response_body['total'] ) ) {
			$results['total'] = intval( $response_body['total'] );
		}

		if ( isset( $args['offset'] ) ) {
			$results['offset'] = intval( $args['offset'] );
		}

		if ( isset( $args['limit'] ) ) {
			$results['limit'] = intval( $args['limit'] );
		}

		return $results;
	}
}
