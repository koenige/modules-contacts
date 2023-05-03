<?php 

/**
 * contacts module
 * Table with addresses
 *
 * https://www.zugzwang.org/modules/contacts
 * Part of »Zugzwang Project«
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2017-2021, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Addresses';
$zz['table'] = '/*_PREFIX_*/addresses';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'address_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Contact';
$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['sql_character_set'][1] = 'utf8';
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['class'] = 'block480a';

$zz['fields'][3]['title'] = 'Street/No.';
$zz['fields'][3]['field_name'] = 'address';
$zz['fields'][3]['type'] = 'memo';
$zz['fields'][3]['rows'] = 2;
$zz['fields'][3]['format'] = 'nl2br';
$zz['fields'][3]['hide_format_in_title_desc'] = true;
$zz['fields'][3]['cols'] = 40;
$zz['fields'][3]['trim'] = true;
$zz['fields'][3]['geocode'] = 'street_name';
$zz['fields'][3]['class'] = 'block480a';

$zz['fields'][4]['field_name'] = 'postcode';
$zz['fields'][4]['type'] = 'text';
$zz['fields'][4]['placeholder'] = true;
$zz['fields'][4]['size'] = 8;
$zz['fields'][4]['append_next'] = true;
$zz['fields'][4]['title_append'] = 'Postcode/Place';
$zz['fields'][4]['geocode'] = 'postal_code';
$zz['fields'][4]['geocode_ignore_update'] = true;
$zz['fields'][4]['class'] = 'block480a';
$zz['fields'][4]['dependencies'] = [5, 6];

$zz['fields'][5]['field_name'] = 'place';
$zz['fields'][5]['type'] = 'text';
$zz['fields'][5]['placeholder'] = true;
$zz['fields'][5]['geocode'] = 'locality';
$zz['fields'][5]['class'] = 'block480a';
$zz['fields'][5]['sql'] = 'SELECT DISTINCT place, place FROM /*_PREFIX_*/addresses ORDER BY place';
$zz['fields'][5]['dependencies'] = [6];
$zz['fields'][5]['sql_dependency'][4] = 'SELECT DISTINCT place
	FROM /*_PREFIX_*/addresses
	WHERE postcode = "%s"';

$zz['fields'][6]['field_name'] = 'country_id';
$zz['fields'][6]['type'] = 'select';
$zz['fields'][6]['sql'] = 'SELECT country_id, country_code, country
	FROM /*_PREFIX_*/countries
	ORDER BY country';
$zz['fields'][6]['sql_translate'] = ['country_id' => 'countries'];
$zz['fields'][6]['sql_character_set'][1] = 'latin1';
$zz['fields'][6]['sql_character_set'][2] = 'latin1';
$zz['fields'][6]['display_field'] = 'country_code';
$zz['fields'][6]['search'] = '/*_PREFIX_*/countries.country';
$zz['fields'][6]['geocode'] = 'country_id';
$zz['fields'][6]['geocode_sql'] = 'SELECT country_code
	FROM /*_PREFIX_*/countries
	WHERE country_id = %d';
$zz['fields'][6]['class'] = 'block480';
$zz['fields'][6]['sql_dependency'][4] = 'SELECT DISTINCT country, country_id
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
	WHERE postcode = "%s"';
$zz['fields'][6]['sql_dependency'][5] = 'SELECT DISTINCT country, country_id
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
	WHERE place = "%s"';

$zz['fields'][7]['field_name'] = 'latitude';
$zz['fields'][7]['title_append'] = 'Latitude / Longitude';
$zz['fields'][7]['type'] = 'number';
$zz['fields'][7]['number_type'] = 'latitude';
$zz['fields'][7]['kml'] = 'latitude';
$zz['fields'][7]['geocode'] = 'latitude';
$zz['fields'][7]['hide_in_list'] = true;
$zz['fields'][7]['if']['add']['class'] = 'hidden';
$zz['fields'][7]['kml'] = 'latitude';
$zz['fields'][7]['geojson'] = 'latitude';
$zz['fields'][7]['geocode'] = 'latitude';
$zz['fields'][7]['append_next'] = true;
$zz['fields'][7]['suffix'] = ' / ';

$zz['fields'][8]['field_name'] = 'longitude';
$zz['fields'][8]['type'] = 'number';
$zz['fields'][8]['number_type'] = 'longitude';
$zz['fields'][8]['kml'] = 'longitude';
$zz['fields'][8]['geocode'] = 'longitude';
$zz['fields'][8]['hide_in_list'] = true;
$zz['fields'][8]['if']['add']['class'] = 'hidden';
$zz['fields'][8]['kml'] = 'longitude';
$zz['fields'][8]['geojson'] = 'longitude';

$zz['fields'][10]['field_name'] = 'receive_mail';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = ['yes', 'no'];
$zz['fields'][10]['default'] = 'yes';
$zz['fields'][10]['def_val_ignore'] = true;
$zz['fields'][10]['explanation'] = 'If there is more than one address:<br>Send letters to this address?';
$zz['fields'][10]['class'] = 'hidden480';
$zz['fields'][10]['if_single_record']['hide_in_form'] = true;
$zz['fields'][10]['if_single_record']['type'] = 'hidden';
$zz['fields'][10]['if_single_record']['value'] = 'yes';
$zz['fields'][10]['hide_in_form'] = true;
$zz['fields'][10]['hide_in_list'] = true;

$zz['fields'][9]['title'] = 'Type';
$zz['fields'][9]['field_name'] = 'address_category_id';
$zz['fields'][9]['key_field_name'] = 'category_id';
$zz['fields'][9]['type'] = 'select';
$zz['fields'][9]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories ORDER BY sequence, category';
$zz['fields'][9]['display_field'] = 'address_type';
$zz['fields'][9]['search'] = '/*_PREFIX_*/categories.category';
$zz['fields'][9]['show_hierarchy'] = 'main_category_id';
$zz['fields'][9]['show_hierarchy_subtree'] = wrap_category_id('address');
$zz['fields'][9]['def_val_ignore'] = true;
$zz['fields'][9]['class'] = 'hidden480';

if (in_array('activities', wrap_setting('modules'))) {
	$zz['fields'][11]['field_name'] = 'formfield_id';
	$zz['fields'][11]['type'] = 'select';
	$zz['fields'][11]['sql'] = 'SELECT formfield_id
			, CONCAT(event, " ", formfields.sequence)
		FROM formfields
		LEFT JOIN forms USING (form_id)
		LEFT JOIN events USING (event_id)
		ORDER BY identifier, formfields.sequence';
	$zz['fields'][11]['exclude_from_search'] = true;
	$zz['fields'][11]['hide_in_list'] = true;
	$zz['fields'][11]['hide_in_form'] = true;
}

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT contact_id
		, address, postcode, place, country_id, country
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/addresses.address_category_id = /*_PREFIX_*/categories.category_id
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)';
$zz['subselect']['sql_translate'] = ['country_id' => 'countries'];
$zz['subselect']['sql_ignore'] = ['country_id'];
$zz['subselect']['concat_fields'] = '';
$zz['subselect']['list_field_format'] = 'nl2br';
$zz['unless']['export_mode']['subselect']['field_suffix'][0] = '<br>';
$zz['if']['export_mode']['subselect']['field_suffix'][0] = "\r";
$zz['subselect']['field_suffix'][1] = ' ';
$zz['unless']['export_mode']['subselect']['field_suffix'][2] = '<br>';
$zz['if']['export_mode']['subselect']['field_suffix'][2] = "\r";
$zz['export_no_html'] = true;
$zz['if']['export_mode']['subselect']['concat_rows'] = "\r\r";

$zz['sql'] = 'SELECT /*_PREFIX_*/addresses.*
		, /*_PREFIX_*/categories.category AS address_type
		, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/countries.country_code
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/addresses.address_category_id
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.identifier, country, postcode, place';
