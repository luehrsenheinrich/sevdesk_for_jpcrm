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
class CommunicationWay extends SevdeskModelObject {

	/**
	 * The sevDesk Object name this object represents.
	 *
	 * @var string
	 */
	protected $object_name = 'CommunicationWay';

	/**
	 * Object data array.
	 *
	 * @var array
	 */
	protected $data = array(
		'contact' => array(),
		'type'    => null,
		'value'   => null,
	);
}
