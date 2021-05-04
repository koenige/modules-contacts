<?php 

/**
 * Zugzwang Project
 * Table with persons
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2011-2015, 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Persons';
$zz['table'] = '/*_PREFIX_*/persons';

$zz['fields'][1]['field_name'] = 'person_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['import_id_value'] = true;
$zz['fields'][1]['field_sequence'] = 12;

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = sprintf('SELECT contact_id, contact
	FROM contacts
	WHERE contact_category_id = %d
	ORDER BY contact', wrap_category_id('contact/person'));
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['character_set'] = 'utf8';
$zz['fields'][2]['hide_in_list'] = true;
$zz['fields'][2]['field_sequence'] = 13;

$zz['fields'][3]['title'] = 'Title (prefix)';
$zz['fields'][3]['field_name'] = 'title_prefix';
$zz['fields'][3]['hide_in_list'] = true;
$zz['fields'][3]['replace_values'] = ['-' => ''];
$zz['fields'][3]['field_sequence'] = 14;

$zz['fields'][4]['title'] = 'First name';
$zz['fields'][4]['field_name'] = 'first_name';
$zz['fields'][4]['unless']['export_mode']['list_append_next'] = true;
$zz['fields'][4]['if']['multi']['null_string'] = true;
$zz['fields'][4]['merge_equal'] = true;
$zz['fields'][4]['field_sequence'] = 15;
$zz['fields'][4]['link'] = [
	'function' => 'mf_contacts_profile_path',
	'fields' => ['identifier', 'contact_parameters']
];
$zz['fields'][4]['add_details_destination'] = true;
$zz['fields'][4]['unless']['export_mode']['display_field'] = 'contact';

$zz['fields'][5]['title'] = 'Particle';
$zz['fields'][5]['field_name'] = 'name_particle';
$zz['fields'][5]['replace_values'] = ['-' => ''];
$zz['fields'][5]['merge_equal'] = true;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['field_sequence'] = 16;

$zz['fields'][6]['title'] = 'Last name';
$zz['fields'][6]['field_name'] = 'last_name';
$zz['fields'][6]['list_append_show_title'] = true;
$zz['fields'][6]['list_append_next'] = true;
$zz['fields'][6]['if']['multi']['null_string'] = true;
$zz['fields'][6]['list_hide_value'] = true;
$zz['fields'][6]['merge_equal'] = true;
$zz['fields'][6]['field_sequence'] = 17;

$zz['fields'][12]['field_name'] = 'birth_name';
$zz['fields'][12]['explanation'] = 'if different from last name';
$zz['fields'][12]['list_prefix'] = ' (born ';
$zz['fields'][12]['list_suffix'] = ')';
$zz['fields'][12]['field_sequence'] = 18;

$zz['fields'][7]['title'] = 'Title (suffix)';
$zz['fields'][7]['field_name'] = 'title_suffix';
$zz['fields'][7]['hide_in_list'] = true;
$zz['fields'][7]['field_sequence'] = 19;

$zz['fields'][8]['field_name'] = 'sex';
$zz['fields'][8]['type'] = 'select';
$zz['fields'][8]['enum'] = ['female', 'male', 'diverse'];
$zz['fields'][8]['enum_title'] = [wrap_text('female'), wrap_text('male'), wrap_text('diverse')];
$zz['fields'][8]['hide_novalue'] = false;
$zz['fields'][8]['hide_in_list'] = true;
$zz['fields'][8]['field_sequence'] = 20;

$zz['fields'][9]['title'] = 'Date of Birth';
$zz['fields'][9]['field_name'] = 'date_of_birth';
$zz['fields'][9]['type'] = 'date';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['field_sequence'] = 21;

$zz['fields'][11]['title'] = 'Date of Death';
$zz['fields'][11]['field_name'] = 'date_of_death';
$zz['fields'][11]['type'] = 'date';
$zz['fields'][11]['hide_in_list'] = true;
if (!wrap_access('contacts_date_of_death')) {
	$zz['fields'][11]['hide_in_form'] = true;
}
$zz['fields'][11]['field_sequence'] = 22;

$zz['fields'][10]['title'] = 'Nationality';
$zz['fields'][10]['field_name'] = 'nationality_country_id';
$zz['fields'][10]['key_field_name'] = 'country_id';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['sql'] = 'SELECT country_id, country_code, country
	FROM /*_PREFIX_*/countries
	ORDER BY country_code';
$zz['fields'][10]['hide_in_list'] = true;
$zz['fields'][10]['display_field'] = 'country';
$zz['fields'][10]['character_set'] = 'utf8';
$zz['fields'][10]['field_sequence'] = 23;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;
$zz['fields'][99]['field_sequence'] = 23;


$zz['sql'] = 'SELECT DISTINCT /*_PREFIX_*/persons.*
		, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/contacts.identifier
		, "type=person" AS contact_parameters
		, country
	FROM /*_PREFIX_*/persons
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/countries
		ON /*_PREFIX_*/countries.country_id = /*_PREFIX_*/persons.nationality_country_id';
$zz['sqlorder'] = ' ORDER BY last_name, first_name';
