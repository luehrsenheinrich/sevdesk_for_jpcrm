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

			// Check if the current contact is a company or a person.
			$is_company = ! empty( $c->get( 'name' ) );

			if ( $is_company ) {

				// Compose the data for Jetpack CRM.
				$company = array(
					'data' => array(
						'status'          => $c->get( 'category' )->get( 'name' ),
						'name'            => $c->get( 'name' ),
						'created'         => strtotime( $c->get( 'create' ) ),
						'externalSources' => array(
							array(
								'source' => 'sevdesk',
								'uid'    => $c->get( 'id' ),
							),
						),
						'notes'           => $c->get( 'description' ),
					),
				);

				// Does this company already exist in the database?
				// phpcs:ignore
				$existing_company = $zbs->DAL->companies->getCompany(
					-1,
					array(
						'externalSource'    => 'sevdesk',
						'externalSourceUID' => $c->get( 'id' ),
					)
				);

				// Check if the returned value means a company already exists.
				if (
					$existing_company &&
					is_array( $existing_company ) &&
					! empty( $existing_company ) &&
					isset( $existing_company['id'] )
				) {
					$company['id'] = $existing_company['id'];
				}

				/**
				 * Find the first address and add it to jpcrm.
				 */
				if ( is_array( $c->get( 'addresses' ) ) && count( $c->get( 'addresses' ) ) > 0 ) {
					$address       = $c->get( 'addresses' )[0];
					$jpcrm_address = array(
						'addr1'    => $address->get( 'street' ),
						'city'     => $address->get( 'city' ),
						'country'  => $address->get( 'country' )->get( 'name' ),
						'postcode' => $address->get( 'zip' ),
					);

					// Merge the address into the data array.
					$company['data'] = array_merge( $company['data'], $jpcrm_address );
				}

				/**
				 * Find the first email address and add it to jpcrm.
				 */
				if ( is_array( $c->get( 'communicationWays' ) ) && count( $c->get( 'communicationWays' ) ) > 0 ) {
					foreach ( $c->get( 'communicationWays' ) as $cw ) {
						if ( $cw->get( 'type' ) === 'EMAIL' ) {
							$company['data']['email'] = $cw->get( 'value' );
							break;
						}
					}
				}

				// phpcs:ignore
				$res = $zbs->DAL->companies->addUpdateCompany( $company );

				// Generate a table, mainly for debugging.
				$table[] = array(
					'id'    => $c->get( 'id' ),
					'name'  => empty( $c->get( 'name' ) ) ? $c->get( 'surename' ) . ' ' . $c->get( 'familyname' ) : $c->get( 'name' ),
					'zbsid' => $res,
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
