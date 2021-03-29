<?php 

/**
 * Zugzwang Project
 * Contact profile
 *
 * http://www.zugzwang.org/modules/contact
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contact($params, $settings) {
	global $zz_setting;
	if (count($params) !== 1) return false;

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
					, IFNULL(
						TIMESTAMPDIFF(YEAR, date_of_birth, IFNULL(CAST(IF(
							SUBSTRING(date_of_death, -6) = "-00-00",
							CONCAT(YEAR(date_of_death), "-01-01"), date_of_death) AS DATE
						), CURDATE())),
						YEAR(IFNULL(date_of_death, CURDATE())) - YEAR(date_of_birth)
					) AS age
				FROM persons
				LEFT JOIN countries
					ON persons.nationality_country_id = countries.country_id
				WHERE contact_id = %d';
			$sql = sprintf($sql, $data['contact_id']);
			$data += wrap_db_fetch($sql);
			if ($data['sex']) $data[$data['sex']] = true;
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
	$data['addresses'] = wrap_translate($data['addresses'], 'categories', 'category_id');

	// contacts_media
	
	// contacts_contacts
	// @todo associations, depending on relations.parameters
	$sql = 'SELECT cc_id, contact, cc.remarks, cc.sequence, relations.category AS relation
			, IF(relations.parameters LIKE "%%&relation=association%%"
				, "associations"
				, IF(cc.main_contact_id = %d, "parents", "children")
			) AS relation_type
			, identifier
			, contact_categories.category AS category
			, contact_categories.parameters AS category_parameters
			, IF(persons.date_of_death, 1, NULL) AS dead
			, role
		FROM contacts_contacts cc
		LEFT JOIN categories relations
			ON cc.relation_category_id = relations.category_id
		LEFT JOIN contacts
			ON (IF(cc.main_contact_id = %d, cc.contact_id, cc.main_contact_id)) = contacts.contact_id
		LEFT JOIN persons
			ON contacts.contact_id = persons.contact_id
		LEFT JOIN categories contact_categories
			ON contacts.contact_category_id = contact_categories.category_id
		WHERE cc.main_contact_id = %d
		OR cc.contact_id = %d
		ORDER BY cc.sequence, contact';
	$sql = sprintf($sql
		, $data['contact_id']
		, $data['contact_id']
		, $data['contact_id']
		, $data['contact_id']
	);
	$data['relations'] = wrap_db_fetch($sql, ['relation', 'cc_id'], 'list relation contacts');
	$data['relations'] = array_values($data['relations']);
	foreach ($data['relations'] as $index => $relation_types) {
		foreach ($relation_types['contacts'] as $cc_id => $contactrelation) {
			parse_str($contactrelation['category_parameters'], $cparams);
			if (!empty($cparams['type'])) {
				if (empty($zz_setting['contacts_profile_path'][$cparams['type']])) continue;
				$data['relations'][$index]['contacts'][$cc_id]['profile_path'] = sprintf(
					$zz_setting['contacts_profile_path'][$cparams['type']], $contactrelation['identifier']
				);
			}
		}
	}
	
	// participations
	// usergroups
	if (in_array('activities', $zz_setting['modules'])) {
		$sql = 'SELECT participation_id
				, usergroup_id, usergroup, identifier
				, date_begin, date_end, remarks, role
			FROM participations
			LEFT JOIN usergroups USING (usergroup_id)
			LEFT JOIN categories
				ON participations.status_category_id = categories.category_id
			WHERE contact_id = %d';
		$sql = sprintf($sql, $data['contact_id']);
		$data['participations'] = wrap_db_fetch($sql, 'participation_id');
		foreach ($data['participations'] as $participation_id => $participation) {
			$data['participations'][$participation_id]['profile_path']
				= mf_activities_group_path(['identifier' => $participation['identifier']]);
		}
		$data['participation_contact_path']
			= mf_activities_contact_path([
				'identifier' => $data['identifier']
				, 'category_parameters' => 'type=person'
			]);
	}

	// duplicates?
	$sql = 'SELECT contact_id, identifier
		FROM contacts
		WHERE contact LIKE "%%%s%%"
		AND contact_id != %d';
	$sql = sprintf($sql, wrap_db_escape($data['contact']), wrap_db_escape($data['contact_id']));
	$data['duplicates'] = wrap_db_fetch($sql, 'contact_id');

	if ($data['scope'] === 'person') {
		$page['title'] = trim($data['title_prefix'].' '.$data['contact'].' '.$data['title_suffix']);
	} else {
		$page['title'] = $data['contact'];
	}
	$page['breadcrumbs'][] = $data['contact'];
	$page['dont_show_h1'] = true;
	$page['text'] = wrap_template('contact', $data);
	return $page;
}
