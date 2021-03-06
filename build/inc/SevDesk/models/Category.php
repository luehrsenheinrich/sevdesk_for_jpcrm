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
		'name'            => null,
		'priority'        => null,
		'code'            => null,
		'color'           => null,
		'postingAccount'  => null,
		'translationCode' => null,
	);
}
