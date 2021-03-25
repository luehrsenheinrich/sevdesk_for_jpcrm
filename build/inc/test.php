<?php
/**
 * Test file.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm;
$c = wp_sdjpcrm()->sevdesk()->contacts->get_contact( array( 'id' => 28273658 ) );
var_dump( $c );
