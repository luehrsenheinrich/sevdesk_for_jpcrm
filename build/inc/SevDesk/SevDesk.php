<?php
/**
 * The main class to interact with sevDesk data.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;
use \WP_Error;

/**
 * Class SevDesk
 */
class SevDesk {

	/**
	 * The array where we store data from sevdesk.
	 *
	 * @var array
	 */
	private $data_store = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->contacts = new crawler\Contacts();
	}

	/**
	 * Get a specific sevdesk object.
	 *
	 * @param  string $object_name The object name.
	 * @param  int    $id          The object id.
	 * @param  bool   $from_api    If the object should be fetched from the api.
	 * @param  array  $data        Data to seed the array.
	 *
	 * @return object The sevdesk object.
	 */
	public function get( $object_name, $id = null, $from_api = false, $data = array() ) {
		$class_name = __NAMESPACE__ . '\\models\\' . $object_name;
		if ( ! class_exists( $class_name ) ) {
			/* translators: Object name */
			return new WP_Error( 'sevdesk_object_type_error', sprintf( __( 'Object type "%s" does not exist.', 'sdjpcrm' ), $object_name ) );
		}

		if ( isset( $id ) && is_numeric( $id ) && $id > 0 ) {
			if ( isset( $this->data_store[ $object_name ] ) && isset( $this->data_store[ $object_name ][ $id ] ) ) {
				return $this->data_store[ $object_name ][ $id ];
			}

			// Set the id in the data array.
			$data['id'] = $id;

			// Create the object.
			$this->data_store[ $object_name ][ $id ] = new $class_name( $data, $from_api );

			// Return the object.
			return $this->data_store[ $object_name ][ $id ];
		}

		// nothing yet.
	}

	/**
	 * [query description]
	 */
	public function query() {
		// nothing yet.
	}

	/**
	 * Store objects in the data store.
	 *
	 * @param  string $object_name The object name.
	 * @param  int    $id          The object id.
	 * @param  object $object      The object itself.
	 *
	 * @return void
	 */
	public function store( $object_name, $id, $object ) {
		$this->data_store[ $object_name ][ $id ] = $object;
	}

}
