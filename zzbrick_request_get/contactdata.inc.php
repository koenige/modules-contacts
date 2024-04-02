<?php 

/**
 * news module
 * get contact data per ID
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/news
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * get contact data per ID, pre-sorted
 * existing data is appended to contact data
 *
 * @param array $data
 * @param array $settings (optional)
 * @param string $id_field_name (optional, if key does not equal event_id)
 * @param string $lang_field_name (optional, if not current language shall be used)
 * @return array
 */
function mod_contacts_get_contactdata($data, $settings = [], $id_field_name = '', $lang_field_name = '') {
	if (!$data) return $data;
	require_once wrap_setting('core').'/data.inc.php';

	$ids = wrap_data_ids($data, $id_field_name);
	$langs = wrap_data_langs($data, $lang_field_name);

	$sql = 'SELECT contact_id, contact, contact_short, contact_abbr,
			contacts.identifier, contacts.description, remarks
			, SUBSTRING_INDEX(path, "/", -1) AS scope
			, categories.parameters
			, IF (ISNULL(end_date), 1, NULL) AS alive
			, start_date, end_date
			, IFNULL(
				TIMESTAMPDIFF(YEAR, start_date, IFNULL(CAST(IF(
					SUBSTRING(end_date, -6) = "-00-00",
					CONCAT(YEAR(end_date), "-01-01"), end_date) AS DATE
				), CURDATE())),
				YEAR(IFNULL(end_date, CURDATE())) - YEAR(start_date)
			) AS age
			, category_id, category
			, country_id, country
			%s
	    FROM contacts
	    LEFT JOIN countries USING (country_id)
	    LEFT JOIN categories
	    	ON contacts.contact_category_id = categories.category_id
	    WHERE contacts.contact_id IN (%s)
	    ORDER BY FIELD(contacts.contact_id, %s)';
	$sql = sprintf($sql
		, !empty($settings['extra_fields']) ? ','.implode(',', $settings['extra_fields']) : ''
		, implode(',', $ids)
		, implode(',', $ids)
	);
	$contactdata = wrap_db_fetch($sql, 'contact_id');
	foreach ($contactdata as $contact_id => $contact) {
		if (!$contact['parameters']) continue;
		parse_str($contact['parameters'], $contactdata[$contact_id]['parameters']);
	}

	foreach ($langs as $lang) {
		$contacts[$lang] = wrap_translate($contactdata, 'contacts', '', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'categories', 'category_id', true, $lang);
		$contacts[$lang] = wrap_translate($contacts[$lang], 'countries', 'country_id', true, $lang);
		foreach (array_keys($contacts[$lang]) as $contact_id) {
			$contacts[$lang][$contact_id][$lang] = true;
		}
	}

	// media
	$contacts = wrap_data_media($contacts, $ids, $langs, 'contacts', 'contact');

	// contact details
	// @todo translations (categories)
	$contactdetails[wrap_setting('lang')] = mf_contacts_contactdetails($ids);

	// addresses
	// @todo translations
	$addresses = mf_contacts_addresses($ids);
	foreach ($addresses as $contact_id => $contactaddresses)
		$data[$contact_id]['addresses'] = $contactaddresses;

	$identifiers[wrap_setting('lang')] = mf_contacts_identifiers($ids);
	$relations[wrap_setting('lang')] = mf_contacts_relations($ids);	

	$data = wrap_data_merge($data, $contacts, $id_field_name, $lang_field_name);
	$data = wrap_data_merge($data, $contactdetails, $id_field_name, $lang_field_name);
	$data = wrap_data_merge($data, $identifiers, $id_field_name, $lang_field_name);
	$data = wrap_data_merge($data, $relations, $id_field_name, $lang_field_name);

	$data = mod_contacts_contactdata_packages($data, $ids);
	
	foreach ($data as $contact_id => $line) {
		if (!is_numeric($contact_id)) continue;
		$data[$contact_id]['profiles'] = wrap_profiles($line);
	}
	
	return $data;
}	

/**
 * get identifiers per contact
 *
 * @param array $ids
 * @return array
 */
function mf_contacts_identifiers($ids) {
	$sql = 'SELECT contact_id, contact_identifier_id, identifier
			, IF(current = "yes", 1, NULL) AS current
			, category_id, category
			, IFNULL(
				SUBSTRING_INDEX(SUBSTRING_INDEX(categories.parameters, "&alias=identifiers/", -1), "&", 1),
				SUBSTRING_INDEX(categories.path, "/", -1)
			) AS path
		FROM contacts_identifiers
		LEFT JOIN categories
			ON contacts_identifiers.identifier_category_id = categories.category_id
		WHERE contact_id IN (%s)
		ORDER BY categories.sequence, categories.path, contacts_identifiers.identifier';
	$sql = sprintf($sql, implode(',', $ids));
	$identifiers = wrap_db_fetch($sql, 'contact_identifier_id');
	$identifiers = wrap_translate($identifiers, 'categories', 'category_id');
	
	$data = [];
	foreach ($identifiers as $contact_identifier_id => $identifier)
		$data[$identifier['contact_id']]['identifiers'][$contact_identifier_id] = $identifier;
	return $data;
}

/**
 * get relations (associations, parents, children) per contact
 *
 * @param array $ids
 * @return array
 */
function mf_contacts_relations($ids) {
	$sql = 'SELECT CONCAT(cc_id, "-", cc.main_contact_id) AS cc_id
			, cc.main_contact_id AS my_contact_id
			, cc.remarks, cc.sequence, contact
			, relations.category AS relation
			, IF(relations.parameters LIKE "%%&association=1%%"
				, "associations"
				, "children"
			) AS relation_type
			, SUBSTRING_INDEX(IFNULL(SUBSTRING_INDEX(SUBSTRING_INDEX(relations.parameters, "&alias=", -1), "&", 1), relations.path), "/", -1) AS relation_path
			, identifier
			, contact_categories.category_id AS contact_category_id
			, contact_categories.category AS category
			, contact_categories.parameters AS category_parameters
			, relations.parameters AS relation_parameters
			, IF(contacts.end_date, NULL, 1) AS alive
			, IF(persons.date_of_death, 1, NULL) AS dead
			, role
			, addresses.latitude
			, addresses.longitude
		FROM contacts_contacts cc
		LEFT JOIN categories relations
			ON cc.relation_category_id = relations.category_id
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN persons USING (contact_id)
		LEFT JOIN addresses USING (contact_id)
		LEFT JOIN categories contact_categories
			ON contacts.contact_category_id = contact_categories.category_id
		WHERE cc.main_contact_id IN (%s) 
		UNION SELECT CONCAT(cc_id, "-", cc.contact_id) AS cc_id
			, cc.contact_id AS my_contact_id
			, cc.remarks, cc.sequence, contact
			, relations.category AS relation
			, IF(relations.parameters LIKE "%%&association=1%%"
				, "associations"
				, "parents"
			) AS relation_type
			, SUBSTRING_INDEX(IFNULL(SUBSTRING_INDEX(SUBSTRING_INDEX(relations.parameters, "&alias=", -1), "&", 1), relations.path), "/", -1) AS relation_path
			, identifier
			, contact_categories.category_id AS contact_category_id
			, contact_categories.category AS category
			, contact_categories.parameters AS category_parameters
			, relations.parameters AS relation_parameters
			, IF(contacts.end_date, NULL, 1) AS alive
			, IF(persons.date_of_death, 1, NULL) AS dead
			, role
			, addresses.latitude
			, addresses.longitude
		FROM contacts_contacts cc
		LEFT JOIN categories relations
			ON cc.relation_category_id = relations.category_id
		LEFT JOIN contacts
			ON cc.main_contact_id = contacts.contact_id
		LEFT JOIN persons
			ON contacts.contact_id = persons.contact_id
		LEFT JOIN addresses
			ON contacts.contact_id = addresses.contact_id
		LEFT JOIN categories contact_categories
			ON contacts.contact_category_id = contact_categories.category_id
		WHERE cc.contact_id IN (%s) 
		ORDER BY sequence, contact';
	$sql = sprintf($sql
		, implode(',', $ids)
		, implode(',', $ids)
	);
	$relations = wrap_db_fetch($sql, 'cc_id');
	$relations = wrap_translate($relations, 'categories', 'contact_category_id');
	
	$data = [];
	$indices = [];
	$i = 0;
	foreach ($relations as $cc_id => $relation) {
		// set index, set relation
		$index = sprintf('%s-%s', $relation['relation_type'], $relation['relation']);
		if (!array_key_exists($index, $indices)) {
			$indices[$index] = $i++;
			$this_rel = [];
			$this_rel['relation'] = $relation['relation'];
			// relation parameters
			$rparams = [];
			if ($relation['relation_parameters'])
				parse_str($relation['relation_parameters'], $rparams);
			$inverse_relation = mf_contacts_relations_inverse($relation['relation_type']);
			if (!empty($rparams[$inverse_relation]['relation']))
				$this_rel['relation'] = $rparams[$inverse_relation]['relation'];
			$this_rel['relation_parameters'] = $rparams;
			$this_rel['relation_path'] = $relation['relation_path'];
			$data[$relation['my_contact_id']][$relation['relation_type']][$indices[$index]] = $this_rel;
		}
		$relation['profile_path'] = mf_contacts_relations_profile($relation);
		unset($relation['relation_parameters']);
		unset($relation['category_parameters']);
		$data[$relation['my_contact_id']][$relation['relation_type']][$indices[$index]]['contacts'][$cc_id] = $relation;
	}
	return $data;
}

/**
 * get contacts profile for relations
 *
 * @param array $relation
 * @return string
 */
function mf_contacts_relations_profile($relation) {
	$cparams = [];
	if (!$relation['category_parameters']) return '';
	
	parse_str($relation['category_parameters'], $cparams);
	if (!empty($cparams['type']) AND wrap_setting('contacts_profile_path['.$cparams['type'].']'))
		return wrap_setting('base').sprintf(
			wrap_setting('contacts_profile_path['.$cparams['type'].']'), $relation['identifier']
		);
	if (wrap_setting('contacts_profile_path[*]'))
		return wrap_setting('base').sprintf(
			wrap_setting('contacts_profile_path[*]'), $relation['identifier']
		);
	return '';
}

/**
 * get further contact data from modules
 *
 * @param array $data
 * @param array $ids
 * @return array
 */
function mod_contacts_contactdata_packages($data, $ids) {
	$files = wrap_include_files('contact');
	if (!$files) {
		$data['templates'] = [];
		return $data;
	}
	foreach (array_keys($files) as $package) {
		wrap_package_activate($package);
		$function = sprintf('mf_%s_contact', $package);
		if (!function_exists($function)) continue;
		$data = $function($data, $ids);
	}
	return $data;
}

/**
 * show inverse relation
 *
 * @param string $relation
 * @return string
 */
function mf_contacts_relations_inverse($relation) {
	switch ($relation) {
		case 'children': return 'parents';
		case 'parents': return 'children';
	}
	return $relation;
}
