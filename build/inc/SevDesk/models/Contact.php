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

	/**
	 * If this object is a company or a contact.
	 *
	 * @return boolean If this object is a company.
	 */
	public function is_company() {
		return ! empty( $this->get( 'name' ) );
	}

	/**
	 * Save the current object to the CRM.
	 *
	 * @return bool|int The id of the ZBS Contact on success, false on failure.
	 */
	public function save_to_crm() {
		global $zbs;

		// Check if the current contact is a company or a person.
		$is_company = ! empty( $this->get( 'name' ) );

		$zbs_id = false;

		if ( $is_company ) {

			// Compose the data for Jetpack CRM.
			$company = array(
				'data' => array(
					'status'          => $this->get( 'category' )->get( 'name' ),
					'name'            => $this->get( 'name' ),
					'created'         => strtotime( $this->get( 'create' ) ),
					'externalSources' => array(
						array(
							'source' => 'sevdesk',
							'uid'    => $this->get( 'id' ),
						),
					),
					'notes'           => $this->get( 'description' ),
				),
			);

			// Does this company already exist in the database?
			// phpcs:ignore
			$existing_company = $zbs->DAL->companies->getCompany(
				-1,
				array(
					'externalSource'    => 'sevdesk',
					'externalSourceUID' => $this->get( 'id' ),
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
			if ( is_array( $this->get( 'addresses' ) ) && count( $this->get( 'addresses' ) ) > 0 ) {
				$address       = $this->get( 'addresses' )[0];
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
			if ( is_array( $this->get( 'communicationWays' ) ) && count( $this->get( 'communicationWays' ) ) > 0 ) {
				foreach ( $this->get( 'communicationWays' ) as $cw ) {
					if ( $cw->get( 'type' ) === 'EMAIL' ) {
						$company['data']['email'] = $cw->get( 'value' );
						break;
					}
				}
			}

			// phpcs:ignore
			$zbs_id = $zbs->DAL->companies->addUpdateCompany( $company );
		}

		return $zbs_id;
	}
}
