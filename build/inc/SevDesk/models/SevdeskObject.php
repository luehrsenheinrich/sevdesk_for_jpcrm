<?php
/**
 * The base sevDesk Object.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\models;
use \WP_Error;

/**
 * The base sevDesk Object.
 */
class SevdeskObject implements \JsonSerializable {

	/**
	 * The sevdesk api url.
	 *
	 * @var string
	 */
	protected $api_url = 'https://my.sevdesk.de/api/v1/';

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = '';

	/**
	 * The sevdesk api token.
	 *
	 * @var string
	 */
	protected $api_token = '';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Object errors array.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Constructor.
	 *
	 * @param array $args An array of arguments for this invoice.
	 * @param bool  $load Do we want to take the args as is or load from API.
	 */
	public function __construct( $args = array(), $load = false ) {
		$this->api_token = get_option( 'sevdesk_api_token' );

		// Do we have an ID? If so, try to load that ID.
		if ( isset( $args['id'] ) && is_numeric( $args['id'] ) && $args['id'] > 0 && $load ) {
			$this->load( $args['id'] );
		}

		// Parse the data.
		$this->parse( $args );
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
	 * @param int $id The id of the invoice to load.
	 *
	 * @return mixed
	 */
	public function load( $id ) {
		$response = wp_remote_request(
			$this->api_url . $this->object_name . '/' . $id,
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
	 * @param  array $args An array of arguments for this object.
	 *
	 * @return void
	 */
	protected function parse( $args ) {

		var_dump( 'beforeParse', $args );

		// cast args to an array.
		$args = json_decode( json_encode( $args ), true );

		var_dump( 'afterParse', $args );

		$object_data = array();
		// Only import keys, that we have defined in our default array.
		foreach ( $this->data as $key => $default ) {
			if ( isset( $args[ $key ] ) && ! empty( $args[ $key ] ) ) {
				if (
					is_array( $args[ $key ] ) &&
					isset( $args[ $key ]['object_name'] ) &&
					class_exists( __NAMESPACE__ . '\\' . $args[ $key ]['object_name'] )
				) {
					// We have to handle a sevDesk Object.
					$class_mame          = __NAMESPACE__ . '\\' . $args[ $key ]['object_name'];
					$object_data[ $key ] = new $class_name( $args[ $key ] );
				} else {
					$object_data[ $key ] = $args[ $key ];
				}
			}
		}

		// Merge the parsed data into our default data.
		$this->data = array_merge(
			$this->data,
			$object_data
		);
	}

	/**
	 * Validates a response generated by the WP HTTP functions.
	 *
	 * @param  object $response The WP HTTP response.
	 *
	 * @return array|object     A data array or WP_Error
	 */
	protected function validate_wp_response( $response ) {
		if ( ! is_wp_error( $response ) ) {
			// The request went through successfully, check the response code against
			// what we're expecting.
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				// Do something with the response.
				$data = array(
					'body'    => wp_remote_retrieve_body( $response ),
					'headers' => wp_remote_retrieve_headers( $response ),
				);
			} else {
				// The response code was not what we were expecting, record the message.
				$data = new WP_Error( wp_remote_retrieve_response_message( $response ) );
			}
		} else {
			// There was an error making the request.
			$data = new WP_Error( $response->get_error_message() );
		}

		return $data;
	}
}
