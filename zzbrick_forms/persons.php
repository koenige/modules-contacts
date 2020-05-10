<?php 

/**
 * Zugzwang Project
 * Form with persons
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2019-2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$sql = 'SELECT category_id, category, parameters 
	FROM categories
	WHERE main_category_id = %d
	ORDER BY sequence, path';
$sql = sprintf($sql, wrap_category_id('provider'));
$contactdetails = wrap_db_fetch($sql, 'category_id');
$values['contactdetails'] = [];
$i = 50;
foreach ($contactdetails as $category_id => $provider) {
	parse_str($provider['parameters'], $parameters);
	if (!empty($parameters['separate']))
		$key = $provider['category_id'];
	else
		$key = $parameters['type'];
	$values['contactdetails'][$key]['categories'][$provider['category_id']] = $provider;
	$values['contactdetails'][$key]['category'] = (count($values['contactdetails'][$key]['categories']) === 1)
		? $provider['category']
		: (!empty($parameters['category']) ? $parameters['category'] : ucfirst($parameters['type']));
	$values['contactdetails'][$key]['category'] = wrap_text($values['contactdetails'][$key]['category']);
	$values['contactdetails'][$key]['type'] = $parameters['type'];
	$values['contactdetails'][$key]['category_id'] = $provider['category_id'];
	if (empty($values['contactdetails'][$key]['parameters']))
		$values['contactdetails'][$key]['parameters'] = [];
	$values['contactdetails'][$key]['parameters'] += $parameters;
	$values['contactdetails'][$key]['field_sequence'] = $i;
	$i++;
}

$zz = zzform_include_table('contacts', $values);
$zz['title'] = 'Persons';

// contact
$zz['fields'][2]['type'] = 'hidden';
$zz['fields'][2]['hide_in_form'] = true;
$zz['fields'][2]['hide_in_list'] = true;
$zz['fields'][2]['function'] = 'mod_contacts_edit_contact_name';
$zz['fields'][2]['fields'] = [
	'persons.first_name', 'persons.name_particle', 'persons.last_name'
];
$zz['fields'][2]['export'] = false;
unset($zz['fields'][2]['add_details_destination']);
$zz['fields'][2]['field_sequence'] = 2;

// identifier
$zz['fields'][3]['export'] = false;
$zz['fields'][3]['unique'] = true;
$zz['fields'][3]['conf_identifier']['concat'] = '.';
$zz['fields'][3]['conf_identifier']['exists'] = '.';
$zz['fields'][3]['fields'] = [
	'persons.first_name', 'persons.name_particle', 'persons.last_name',
	'identifier'
];
$zz['fields'][3]['merge_ignore'] = true;
$zz['fields'][3]['field_sequence'] = 41;
$zz['fields'][3]['separator'] = true;
$zz['fields'][3]['separator_before'] = true;

if (brick_access_rights()) {
	// make it possible to change identifier
	$zz['fields'][3]['read_options'] = 17;
	$zz['fields'][3]['separator'] = false;

	$zz['fields'][17]['field_name'] = 'change_identifier';
	$zz['fields'][17]['title'] = 'Change identifier?';
	$zz['fields'][17]['explanation'] = 'To change the identifier. Important: this is the login username, please inform the person of the change.';
	$zz['fields'][17]['type'] = 'option';
	$zz['fields'][17]['type_detail'] = 'select';
	$zz['fields'][17]['enum'] = ['yes', 'no'];
	$zz['fields'][17]['options'] = [
		'yes' => ['fields' => ['persons.first_name', 'persons.name_particle', 'persons.last_name']],
		'no' => ['fields' => ['persons.first_name', 'persons.name_particle', 'persons.last_name', 'identifier']]
	];
	$zz['fields'][17]['default'] = 'no';
	$zz['fields'][17]['field_sequence'] = 42;
	$zz['fields'][17]['separator'] = true;
}

$zz['fields'][9] = zzform_include_table('persons');
$zz['fields'][9]['add_details_destination'] = true;
$zz['fields'][9]['title'] = 'Person';
$zz['fields'][9]['dont_show_missing'] = true;
$zz['fields'][9]['type'] = 'subtable';
$zz['fields'][9]['form_display'] = 'inline';
$zz['fields'][9]['list_display'] = 'inline';
$zz['fields'][9]['min_records'] = 1;
$zz['fields'][9]['min_records_required'] = 1;
$zz['fields'][9]['max_records'] = 1;
$zz['fields'][9]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][9]['fields'][99]['hide_in_form'] = true; // last_update
unset($zz['fields'][9]['conditions']);

// category
$zz['fields'][4]['hide_in_form'] = true;
$zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['type'] = 'hidden';
$zz['fields'][4]['value'] = wrap_category_id('contact/person');
$zz['fields'][4]['export'] = false; // contact_category_id
$zz['fields'][4]['field_sequence'] = 43;

// latlon
$zz['fields'][8]['export'] = false;
$zz['fields'][8]['field_sequence'] = 44;

// addresses
$zz['fields'][5]['field_sequence'] = 70;
$zz['fields'][5]['fields'][7]['hide_in_form'] = true; // lat
$zz['fields'][5]['fields'][8]['hide_in_form'] = true; // lon
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['max_records'] = 10;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['separator'] = true;

// description
$zz['fields'][12]['field_sequence'] = 71;
$zz['fields'][12]['title'] = 'About me';
$zz['fields'][12]['explanation'] = 'A few lines about the person.';

// published
$zz['fields'][14]['field_sequence'] = 72;

// unwanted fields
unset($zz['fields'][15]); // parameters
unset($zz['fields'][10]); // contact_short
unset($zz['fields'][11]); // contact_abbr

// last_update
$zz['fields'][99]['field_sequence'] = 99;

// for search!
$zz['fields'][90]['field_name'] = 'person';
$zz['fields'][90]['type'] = 'display';
$zz['fields'][90]['search'] = 'CONCAT(first_name, " ", IFNULL(CONCAT(name_particle, " "), ""), last_name)';
$zz['fields'][90]['character_set'] = 'utf8';
$zz['fields'][90]['hide_in_form'] = true;
$zz['fields'][90]['hide_in_list'] = true;
$zz['fields'][90]['export'] = false;

$zz['fields'][91]['field_name'] = 'person';
$zz['fields'][91]['type'] = 'display';
$zz['fields'][91]['search'] = 'REPLACE(contacts.identifier, ".", " ")';
$zz['fields'][91]['character_set'] = 'latin1';
$zz['fields'][91]['hide_in_form'] = true;
$zz['fields'][91]['hide_in_list'] = true;
$zz['fields'][91]['export'] = false;

$zz['fields'][92]['field_name'] = 'person';
$zz['fields'][92]['type'] = 'display';
$zz['fields'][92]['search'] = 'CONCAT(SUBSTRING_INDEX(first_name, " ", 1), " ", IFNULL(CONCAT(name_particle, " "), ""), last_name)';
$zz['fields'][92]['character_set'] = 'utf8';
$zz['fields'][92]['hide_in_form'] = true;
$zz['fields'][92]['hide_in_list'] = true;
$zz['fields'][92]['export'] = false;

$zz['fields'][94]['field_name'] = 'person';
$zz['fields'][94]['type'] = 'display';
$zz['fields'][94]['search'] = 'CONCAT(last_name, ", ", IFNULL(CONCAT(name_particle, " "), ""), first_name)';
$zz['fields'][94]['character_set'] = 'utf8';
$zz['fields'][94]['hide_in_form'] = true;
$zz['fields'][94]['hide_in_list'] = true;
$zz['fields'][94]['export'] = false;

/*
$zz['fields'][93]['field_name'] = 'person';
$zz['fields'][93]['type'] = 'display';
$zz['fields'][93]['search'] = 'CONCAT(first_name, " ", IFNULL(CONCAT(name_particle, " "), ""), maiden_name)';
$zz['fields'][93]['character_set'] = 'utf8';
$zz['fields'][93]['hide_in_form'] = true;
$zz['fields'][93]['hide_in_list'] = true;
$zz['fields'][93]['export'] = false;
*/

$zz['add_details_return_field'] = 'personen.person_id';

$zz['sql'] = 'SELECT /*_PREFIX_*/contacts.*, category
		, (SELECT CONCAT(latitude, ",", longitude) FROM /*_PREFIX_*/addresses
			WHERE /*_PREFIX_*/addresses.contact_id = /*_PREFIX_*/contacts.contact_id
			LIMIT 1) AS latlon
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/persons USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY last_name, first_name ASC, identifier';

$zz['where']['contact_category_id'] = wrap_category_id('contact/person');

unset($zz['filter'][1]); // contact_category

$zz['filter'][1]['title'] = wrap_text('Published');
$zz['filter'][1]['identifier'] = 'published';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'published';
$zz['filter'][1]['selection']['yes'] = wrap_text('yes');
$zz['filter'][1]['selection']['no'] = wrap_text('no');

$zz_conf['search'] = 'both';

if (brick_access_rights()) {
	$zz_conf['merge'] = true;
}
