<?php
/**
 * The base sevDesk Object.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\models;
use WpMunich\sdjpcrm\SevDesk\SevDeskAPI;
use \WP_Error;

/**
 * The base sevDesk Object.
 */
abstract class SevdeskModelObject extends SevDeskAPI implements \JsonSerializable {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = '';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * If we want to load data directly from the API.
	 *
	 * @var bool
	 */
	protected $from_api = false;

	/**
	 * Constructor.
	 *
	 * @param array $args An array of arguments for this invoice.
	 * @param bool  $from_api Do we want to take the args as is or load from API.
	 */
	public function __construct( $args = array(), $from_api = false ) {
		parent::__construct();
		$this->from_api = $from_api;

		// Do we have an ID? If so, try to load that ID.
		if ( isset( $args['id'] ) && is_numeric( $args['id'] ) && $args['id'] > 0 && $from_api ) {
			$data = $this->load( $args );
		} else {
			$data = $args;
		}

		$this->parse( $data );
	}

	/**
	 * The json serialize function.
	 *
	 * @return array The array cast data.
	 */
	public function jsonSerialize() {
		return (array) $this->data;
	}

	/**
	 * The json serialize function.
	 *
	 * @return array The array cast data.
	 */
	public function __serialize() {
		return (array) $this->data;
	}

	/**
	 * Magic getter for our data.
	 *
	 * @param  string $name The name of the data item to get.
	 *
	 * @return mixed        The content of the data item.
	 */
	public function get( $name ) {
		if ( array_key_exists( $name, $this->data ) ) {
			return $this->data[ $name ];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
		return null;
	}

	/**
	 * Magic setter for our data
	 *
	 * @param string $name The name of the data to set.
	 * @param mixed  $value The value of the data to set.
	 *
	 * @return bool The success state.
	 */
	public function set( $name, $value ) {
		if ( array_key_exists( $name, $this->data ) ) {
			$this->data[ $name ] = $value;
			return true;
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via set(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);

		return false;
	}

	/**
	 * Load an item from the sevDesk API
	 *
	 * @param array $args An array of arguments for the api.
	 *
	 * @return mixed
	 */
	public function load( $args = array() ) {
		$cache_key     = 'sevdesk_' . strtolower( $this->object_name ) . '_' . $args['id'];
		$response_data = wp_cache_get( $cache_key, 'sevdesk' );

		if ( $response_data === false ) {
			$response = wp_remote_request(
				$this->api_url . $this->object_name . '/' . $args['id'],
				array(
					'method'  => 'GET',
					'headers' => array(
						'Authorization' => $this->api_token,
					),
				)
			);

			$data = $this->validate_wp_response( $response );

			if ( is_wp_error( $data ) ) {
				$this->errors[] = $data;
				return;
			}

			$response_body = json_decode( $data['body'], true );
			$response_data = $response_body['objects'][0];
			wp_cache_set( $cache_key, $response_data, 'sevdesk' );
		}

		$this->parse( $response_data );
	}

	/**
	 * Save the current state of the data to the API.
	 *
	 * @return mixed
	 */
	public function save() {
		$data = $this->data;
		if ( is_int( $this->data['id'] ) && $this->data['id'] > 0 ) {
			// Update existing.
			$method = 'PUT';
			$url    = $this->api_url . $this->object_name . '/' . $data['id'];
		} else {
			// Create new, but we have to unset the id.
			unset( $data['id'] );
			$method = 'POST';
			$url    = $this->api_url . $this->object_name;
		}

		$response = wp_remote_request(
			$url,
			array(
				'headers' => array(
					'Authorization' => $this->api_token,
				),
				'body'    => json_decode( json_encode( $data ), true ),
			)
		);

		$response_data = $this->validate_wp_response( $response );

		if ( is_wp_error( $response_data ) ) {
			$this->errors[] = $data;
			return;
		}

		$response_body = json_decode( $response_data['body'] );

		if ( ! empty( $response_body->objects ) ) {
			$this->parse( $response_body->objects );
		}
	}

	/**
	 * Parse and validate the data.
	 *
	 * @param  array $external_data An array of external data to parse.
	 *
	 * @return void
	 */
	protected function parse( $external_data ) {

		// cast external data to an array.
		$external_data = json_decode( json_encode( $external_data ), true );

		$object_data = array();
		// Only import keys, that we have defined in our default array.
		foreach ( $this->data as $key => $default ) {
			if ( isset( $external_data[ $key ] ) && ! empty( $external_data[ $key ] ) ) {
				if (
					is_array( $external_data[ $key ] ) &&
					isset( $external_data[ $key ]['objectName'] ) &&
					class_exists( __NAMESPACE__ . '\\' . $external_data[ $key ]['objectName'] )
				) {
					// We have to handle a sevDesk Object.
					$class_name          = __NAMESPACE__ . '\\' . $external_data[ $key ]['objectName'];
					$object_data[ $key ] = new $class_name( $external_data[ $key ], $this->from_api );
				} else {
					$object_data[ $key ] = $external_data[ $key ];
				}
			}
		}

		// Merge the parsed data into our default data.
		$this->data = array_merge(
			$this->data,
			$object_data
		);
	}
}
