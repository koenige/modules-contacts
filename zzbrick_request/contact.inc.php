<?php 

/**
 * contacts module
 * Contact profile
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contact($params, $settings) {
	if (isset($_GET['sendlogin']) AND isset($_POST['sendlogin'])) {
		return brick_format('%%% make sendlogin '.implode(' ', $params).' %%%');
	}

	$sql = 'SELECT contact_id, contact, contact_short, contact_abbr,
			identifier, contacts.description, remarks
			, SUBSTRING_INDEX(path, "/", -1) AS scope
	    FROM contacts
	    LEFT JOIN categories
	    	ON contacts.contact_category_id = categories.category_id
	    WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape(implode('/', $params)));
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
			if (!empty($data['sex'])) $data[$data['sex']] = true;
			$data = wrap_translate($data, 'countries', 'country_id');
			break;
		case 'organisation':
			break;
		}
	}

	// contact details
	$data += mf_contacts_contactdetails($data['contact_id']);
	
	// addresses
	$data['addresses'] = mf_contacts_addresses($data['contact_id']);

	// contacts_media
	
	// contacts_contacts
	// @todo associations, depending on relations.parameters
	$sql = 'SELECT cc_id, contact, cc.remarks, cc.sequence, relations.category AS relation
			, IF(relations.parameters LIKE "%%&association=1%%"
				, "associations"
				, IF(cc.main_contact_id = %d, "parents", "children")
			) AS relation_type
			, relations.path AS relation_path
			, identifier
			, contact_categories.category AS category
			, contact_categories.parameters AS category_parameters
			, relations.parameters AS relation_parameters
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
		$relation = [];
		foreach ($relation_types['contacts'] as $cc_id => $contactrelation) {
			$rparams = [];
			if ($contactrelation['relation_parameters'])
				parse_str($contactrelation['relation_parameters'], $rparams);
			if (!empty($rparams[$contactrelation['relation_type']]['relation'])) {
				$data['relations'][$index]['relation']
					= $rparams[$contactrelation['relation_type']]['relation'];
			}
			$cparams = [];
			if ($contactrelation['category_parameters'])
				parse_str($contactrelation['category_parameters'], $cparams);
			if (!empty($cparams['type'])) {
				if (!wrap_setting('contacts_profile_path['.$cparams['type'].']')) continue;
				$data['relations'][$index]['contacts'][$cc_id]['profile_path'] = wrap_setting('base').sprintf(
					wrap_setting('contacts_profile_path['.$cparams['type'].']'), $contactrelation['identifier']
				);
			}
			if (!$relation) {
				$relation = $contactrelation;
				$relation['relation_parameters'] = $rparams;
			}
		}
		if ($relation['relation_type'] !== 'parents') continue;
		$type = !empty($relation['relation_parameters']['alias'])
			? $relation['relation_parameters']['alias'] : $relation['relation_path'];
		$type = substr($type, strrpos($type, '/') + 1);
		if (!wrap_setting('contacts_relations_path['.$type.']'))
			$success = wrap_setting_path('contacts_relations_path['.$type.']', 'forms contacts-contacts', ['scope' => $type]);
		if (wrap_setting('contacts_relations_path['.$type.']'))
			$data['relations'][$index]['relations_path'] = wrap_setting('base').sprintf(
				wrap_setting('contacts_relations_path['.$type.']'), $params[0]
			);
	}
	
	// participations
	// usergroups
	if (in_array('activities', wrap_setting('modules'))) {
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
				, 'category_parameters' => 'type='.$data['scope']
			]);
	}

	// duplicates?
	$sql = 'SELECT contact_id, identifier
		FROM contacts
		WHERE contact LIKE "%%%s%%"
		AND contact_id != %d';
	$sql = sprintf($sql, wrap_db_escape($data['contact']), wrap_db_escape($data['contact_id']));
	$data['duplicates'] = wrap_db_fetch($sql, 'contact_id');

	// logins
	if ($data['scope'] === 'person'
		AND wrap_setting('login_with_contact_id')
		AND wrap_access('contacts_login')
	) {
		$data['logindata'] = true;
		if (wrap_setting('login_with_login_rights')) {
			$sql = 'SELECT login_id, login_rights
					, FROM_UNIXTIME(last_click) AS last_click
					, IF(logged_in = "yes", IF((last_click + 60 * %d >= UNIX_TIMESTAMP()), 1, NULL), NULL) AS logged_in
					, IF(active = "yes", 1, NULL) as active
				FROM logins
				WHERE contact_id = %d';
		} else {
			$sql = 'SELECT login_id
					, FROM_UNIXTIME(last_click) AS last_click
					, IF(logged_in = "yes", IF((last_click + 60 * %d >= UNIX_TIMESTAMP()), 1, NULL), NULL) AS logged_in
					, IF(active = "yes", 1, NULL) as active
				FROM logins
				WHERE contact_id = %d';
		}
		$sql = sprintf($sql
			, wrap_setting('logout_inactive_after')
			, $data['contact_id']
		);
		$login = wrap_db_fetch($sql);
		if ($login) {
			$data += $login;
			$data['masquerade_link'] = wrap_path('default_masquerade', $data['contact_id']);
		}
	}
	if (count($params) !== 1) {
		$data['deep'] = str_repeat('../', count($params) -1);
	}

	if ($data['scope'] === 'person') {
		$page['title'] = trim((!empty($data['title_prefix']) ? $data['title_prefix'].' ' : '')
			.$data['contact']
			.(!empty($data['title_suffix']) ? ' '.$data['title_suffix'] : ''));
	} else {
		$page['title'] = $data['contact'];
	}
	$page['query_strings'] = ['sendlogin'];
	if (isset($_GET['sendlogin'])) $data['sendlogin'] = true;
	$page['breadcrumbs'][] = $data['contact'];
	$page['dont_show_h1'] = true;
	$page['text'] = wrap_template('contact', $data);
	return $page;
}
