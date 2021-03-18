<?php
/**
 * The base sevDesk Object.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;

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
	 * Constructor.
	 *
	 * @param array $args An array of arguments for this invoice.
	 * @param bool  $load Do we want to take the args as is or load from API.
	 */
	public function __construct( $args = array(), $load = false ) {
		$this->api_token = \XF::options()->sevdesk_api_token;

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
	 * @return void
	 */
	public function load( $id ) {
		$response = $this->client()->get(
			$this->api_url . $this->object_name . '/' . $id,
			array(
				'headers' => array(
					'Authorization' => $this->api_token,
				),
			)
		);

		$response_body = json_decode( $response->getBody(), true );
		$response_data = $response_body['objects'][0];

		$this->parse( $response_data );
	}

	/**
	 * Save the current state of the data to the API.
	 *
	 * @return void
	 */
	public function save() {
		if ( is_int( $this->data['id'] ) && $this->data['id'] > 0 ) {
			// Update existing.
			$data = $this->data;

			$response = $this->client()->put(
				$this->api_url . $this->object_name . '/' . $id,
				array(
					'headers'     => array(
						'Authorization' => $this->api_token,
					),
					'form_params' => json_decode( json_encode( $data ), true ),
				)
			);
		} else {
			// Create new, but we have to unset the id.
			$data = $this->data;
			unset( $data['id'] );

			$response = $this->client()->post(
				$this->api_url . $this->object_name,
				array(
					'headers'     => array(
						'Authorization' => $this->api_token,
					),
					'form_params' => json_decode( json_encode( $data ), true ),
				)
			);
		}

		$response_body = json_decode( $response->getBody() );

		if ( ! empty( $response_body->objects ) ) {
			$this->parse( $response_body->objects );
		}
	}

	/**
	 * Parse and validate the data.
	 *
	 * @param  array $args An array of arguments for this invoice.
	 *
	 * @return void
	 */
	protected function parse( $args ) {
		// cast args to an array.
		$args = json_decode( json_encode( $args ), true );

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
}
