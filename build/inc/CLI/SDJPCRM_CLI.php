<?php
/**
 * The base CLI class.
 *
 * @package sdjpcrm
 */

namespace WpMunich\sdjpcrm\CLI;
use \WP_CLI_Command;
use \WP_CLI;
use function WpMunich\sdjpcrm\wp_sdjpcrm;
use function \WP_CLI\Utils\make_progress_bar;
use function \WP_CLI\Utils\format_items;

/**
 * Functions to handle the synchronisation of data between sevDesk and Jetpack CRM.
 */
class SDJPCRM_CLI extends WP_CLI_Command {
	/**
	 * Crawl and ingest contacts from sevDesk into Jetpack CRM.
	 *
	 * @return void
	 */
	public function crawl_contacts() {
		global $zbs;
		// Get a new set of contacts from the crawler.
		$contacts = wp_sdjpcrm()->sevdesk()->contacts->crawl_contacts();

		// Generate a progress bar for WPCLI.
		$progress = make_progress_bar( 'Synchronising contacts', count( $contacts ) );

		// Initialise an empty table array.
		$table = array();

		// Loop through each contact.
		foreach ( $contacts as $c ) {

			$crm_id = $c->save_to_crm();

			if ( $crm_id ) {
				// Generate a table, mainly for debugging.
				$table[] = array(
					'id'    => $c->get( 'id' ),
					'name'  => empty( $c->get( 'name' ) ) ? $c->get( 'surename' ) . ' ' . $c->get( 'familyname' ) : $c->get( 'name' ),
					'zbsid' => $crm_id,
				);
			}

			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( 'These contacts have been synchronised:' );
		format_items( 'table', $table, array( 'id', 'name', 'zbsid' ) );
	}

	/**
	 * Reset the contacts crawler and restart the crawl from scratch.
	 */
	public function reset_contacts_crawler() {
		wp_sdjpcrm()->sevdesk()->contacts->reset_current_active_crawl();
		WP_CLI::success( 'The contacts crawler has been reset.' );
	}

	/**
	 * Helper function to dump some debug stuff. Should be deleted.
	 *
	 * @return void
	 */
	public function dump() {
		global $zbs;
	}
}
