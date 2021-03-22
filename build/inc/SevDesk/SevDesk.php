<?php
/**
 * The main class to interact with sevDesk data.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\SevDesk;
/**
 * Class SevDesk
 */
class SevDesk {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->contacts = new crawler\Contacts();
	}
}
