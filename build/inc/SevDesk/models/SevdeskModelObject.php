<?php
/**
 * The base sevDesk Object.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\models;
use WpMunich\sdjpcrm\SevDesk\SevDeskAPI;
use function WpMunich\sdjpcrm\wp_sdjpcrm;
use \WP_Error;
use \DateTime;

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
	 * Some more arguments for the load request.
	 *
	 * @var array
	 */
	protected $load_request_arguments = array();

	/**
	 * Some basic data that is in every object.
	 *
	 * @var array
	 */
	protected $base_data = array(
		'id'                    => 0,
		'additionalInformation' => null,
		'create'                => null,
		'update'                => null,
		'objectName'            => null,
		'name'                  => null,
	);

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
	 * @param array $args An array of arguments for this item.
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

		$url = add_query_arg(
			$this->load_request_arguments,
			$this->api_url . $this->object_name . '/' . $args['id']
		);

		if ( $response_data === false ) {
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

		if ( isset( $external_data['id'] ) && is_numeric( $external_data['id'] ) && $external_data['id'] > 0 ) {
			wp_sdjpcrm()->sevdesk()->store( $this->object_name, $external_data['id'], $this );
		}

		// cast external data to an array.
		$external_data = json_decode( json_encode( $external_data ), true );

		// Merge the base data into the data object.
		$this->data = array_merge(
			$this->base_data,
			$this->data
		);

		$object_data = array();
		// Only import keys, that we have defined in our default array.
		foreach ( $this->data as $key => $default ) {
			$data = $this->maybe_cast_object( $external_data, $key );
			if ( ! empty( $data ) ) {
				$object_data[ $key ] = $data;
			}
		}

		if ( empty( $this->data['create'] ) ) {
			$datetime             = new DateTime();
			$this->data['create'] = $datetime->format( DateTime::ISO8601 );
		}

		if ( empty( $this->data['update'] ) ) {
			$datetime             = new DateTime();
			$this->data['update'] = $datetime->format( DateTime::ISO8601 );
		}

		// Merge the parsed data into our default data.
		$this->data = array_merge(
			$this->data,
			$object_data
		);
	}

	/**
	 * Parse the external data and maybe cast sevdesk objects.
	 *
	 * @param  array $external_data The external data.
	 * @param  mixed $key           The current key.
	 *
	 * @return mixed                The parsed data.
	 */
	private function maybe_cast_object( $external_data, $key ) {
		if ( isset( $external_data[ $key ] ) && ! empty( $external_data[ $key ] ) ) {
			if (
				is_array( $external_data[ $key ] ) &&
				isset( $external_data[ $key ]['objectName'] ) &&
				class_exists( __NAMESPACE__ . '\\' . $external_data[ $key ]['objectName'] )
			) {
				// We have to handle a sevDesk Object.
				$class_name   = __NAMESPACE__ . '\\' . $external_data[ $key ]['objectName'];
				$return_value = wp_sdjpcrm()->sevdesk()->get( $external_data[ $key ]['objectName'], $external_data[ $key ]['id'], false, $external_data[ $key ] );
			} elseif (
				is_array( $external_data[ $key ] )
			) {
				$data_array = array();
				foreach ( $external_data[ $key ] as $key2 => $value ) {
					$data_array[ $key2 ] = $this->maybe_cast_object( $external_data[ $key ], $key2 );
				}
				$return_value = $data_array;
			} else {
				$return_value = $external_data[ $key ];
			}

			return $return_value;
		}
	}
}
