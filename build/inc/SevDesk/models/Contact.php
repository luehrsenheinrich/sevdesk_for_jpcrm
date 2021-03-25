<?php
/**
 * The contact object for sevDesk.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk\models;

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
	 * Some more arguments for the load request.
	 *
	 * @var array
	 */
	protected $load_request_arguments = array(
		'embed' => 'parent,tags,category,communicationWays,addresses,addresses.category,addresses.country,hasChildren',
	);

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
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
		'parent'                    => array(),
	);
}
