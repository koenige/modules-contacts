<?php 

/**
 * Zugzwang Project
 * Confirm contact registration
 *
 * http://www.zugzwang.org/modules/contact
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contact($params, $settings) {
	global $zz_setting;
	if (empty($params)) return false;

	$sql = 'SELECT contact_id, contact, contact_short, contact_abbr,
			identifier, contacts.description, remarks
			, SUBSTRING_INDEX(path, "/", -1) AS scope
	    FROM contacts
	    LEFT JOIN categories
	    	ON contacts.contact_category_id = categories.category_id
	    WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape($params[0]));
	$data = wrap_db_fetch($sql);
	if (!$data) return false;
	if (!empty($settings['scope'])) {
		if ($settings['scope'] !== $data['scope']) return false;
		switch ($settings['scope']) {
		case 'person':
			$sql = 'SELECT person_id, first_name, name_particle, last_name
					, birth_name, sex, title_prefix, title_suffix
					, date_of_birth, date_of_death, country_id, country
				FROM persons
				LEFT JOIN countries
					ON persons.nationality_country_id = countries.country_id
				WHERE contact_id = %d';
			$sql = sprintf($sql, $data['contact_id']);
			$data += wrap_db_fetch($sql);
			$data = wrap_translate($data, 'countries', 'country_id');
			break;
		case 'organisation':
			break;
		}
	}

	// contact details
	$data += mf_contacts_contactdetails($data['contact_id']);
	
	// addresses
	$sql = 'SELECT address_id, address, postcode, place
			, country_id, country
			, latitude, longitude
			, category_id, category
		FROM addresses
		LEFT JOIN countries USING (country_id)
		LEFT JOIN categories
			ON categories.category_id = addresses.address_category_id
		WHERE contact_id = %d';
	$sql = sprintf($sql, $data['contact_id']);
	$data['addresses'] = wrap_db_fetch($sql, 'address_id');
	$data['addresses'] = wrap_translate($data['addresses'], 'countries', 'country_id');

	// contacts_media
	
	// contacts_contacts
	
	// participations
	// usergroups
	if (in_array('activities', $zz_setting['modules'])) {
		$sql = 'SELECT participation_id
				, usergroup_id, usergroup, identifier
				, date_begin, date_end, remarks
			FROM participations
			LEFT JOIN usergroups USING (usergroup_id)
			LEFT JOIN categories
				ON participations.status_category_id = categories.category_id
			WHERE contact_id = %d';
		$sql = sprintf($sql, $data['contact_id']);
		$data['participations'] = wrap_db_fetch($sql, 'participation_id');
	}

	$page['title'] = trim($data['title_prefix'].' '.$data['contact'].' '.$data['title_suffix']);
	$page['breadcrumbs'][] = $data['contact'];
	$page['dont_show_h1'] = true;
	$page['text'] = wrap_template('contact', $data);
	return $page;
}
