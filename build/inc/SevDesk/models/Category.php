<?php
/**
 * The contact object for sevDesk.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\models;
use \WP_Error;
use \DateTime;

/**
 * The contact class.
 */
class Category extends SevdeskModelObject {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = 'Category';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'                    => 0,
		'objectName'            => 'Category',
		'additionalInformation' => null,
		'create'                => null,
		'update'                => null,
		'name'                  => null,
		'objectType'            => null,
		'priority'              => null,
		'code'                  => null,
		'color'                 => null,
		'postingAccount'        => null,
		'translationCode'       => null,
	);

	/**
	 * Parse and validate the data.
	 *
	 * @param  array $args An array of arguments for this object.
	 *
	 * @return void
	 */
	protected function parse( $args ) {
		parent::parse( $args );

		if ( empty( $this->data['create'] ) ) {
			$datetime             = new DateTime();
			$this->data['create'] = $datetime->format( DateTime::ISO8601 );
		}

		if ( empty( $this->data['update'] ) ) {
			$datetime             = new DateTime();
			$this->data['update'] = $datetime->format( DateTime::ISO8601 );
		}
	}
}
