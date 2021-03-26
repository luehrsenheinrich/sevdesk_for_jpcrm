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
class StaticCountry extends SevdeskModelObject {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = 'StaticCountry';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
		'nameEn'          => null,
		'translationCode' => null,
		'code'            => null,
		'locale'          => null,
	);
}
