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
class ContactAddress extends SevdeskModelObject {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = 'ContactAddress';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
		'contact'  => array(),
		'street'   => null,
		'zip'      => null,
		'city'     => null,
		'country'  => null,
		'category' => null,
	);
}
