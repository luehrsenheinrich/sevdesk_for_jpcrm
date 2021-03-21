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
class Contact extends SevdeskModelObject {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = 'Contact';

	/**
	 * Specify which methods are allowed on the api.
	 *
	 * @var array
	 */
	protected $allowed_methods = array();

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'                        => 0,
		'objectName'                => 'Contact',
		'additionalInformation'     => null,
		'create'                    => null,
		'update'                    => null,
		'name'                      => null,
		'status'                    => null,
		'customerNumber'            => null,
		'surename'                  => null,
		'familyname'                => null,
		'titel'                     => null,
		'category'                  => array(),
		'description'               => null,
		'academicTitle'             => null,
		'gender'                    => null,
		'name2'                     => null,
		'vatNumber'                 => null,
		'birthday'                  => null,
		'vatNumber'                 => null,
		'bankAccount'               => null,
		'bankNumber'                => null,
		'defaultCashbackTime'       => null,
		'defaultDiscountAmount'     => null,
		'defaultDiscountPercentage' => 0,
		'buyerReference'            => null,
		'governmentAgency'          => 0,
		'hasChildren'               => array(),
		'communicationWays'         => array(),
		'addresses'                 => array(),
		'tags'                      => array(),
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
