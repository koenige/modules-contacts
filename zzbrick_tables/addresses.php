<?php 

/**
 * Zugzwang Project
 * Table with addresses
 *
 * http://www.zugzwang.org/modules/newsletters
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz_sub['title'] = 'Addresses';
$zz_sub['table'] = '/*_PREFIX_*/addresses';

$zz_sub['fields'][1]['title'] = 'ID';
$zz_sub['fields'][1]['field_name'] = 'address_id';
$zz_sub['fields'][1]['type'] = 'id';

$zz_sub['fields'][2]['title'] = 'Contact';
$zz_sub['fields'][2]['field_name'] = 'contact_id';
$zz_sub['fields'][2]['type'] = 'select';
$zz_sub['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz_sub['fields'][2]['display_field'] = 'contact';
$zz_sub['fields'][2]['if']['where']['hide_in_form'] = true;
$zz_sub['fields'][2]['if']['where']['hide_in_list'] = true;
$zz_sub['fields'][2]['class'] = 'block480a';

$zz_sub['fields'][3]['field_name'] = 'address';
$zz_sub['fields'][3]['type'] = 'memo';
$zz_sub['fields'][3]['rows'] = 4;
$zz_sub['fields'][3]['geocode'] = 'street_name';
$zz_sub['fields'][3]['class'] = 'block480a';

$zz_sub['fields'][4]['field_name'] = 'postcode';
$zz_sub['fields'][4]['type'] = 'text';
$zz_sub['fields'][4]['size'] = 8;
$zz_sub['fields'][4]['append_next'] = true;
$zz_sub['fields'][4]['title_append'] = 'Postcode/Place';
$zz_sub['fields'][4]['geocode'] = 'postal_code';
$zz_sub['fields'][4]['class'] = 'block480a';

$zz_sub['fields'][5]['field_name'] = 'place';
$zz_sub['fields'][5]['type'] = 'text';
$zz_sub['fields'][5]['geocode'] = 'locality';
$zz_sub['fields'][5]['class'] = 'block480a';

$zz_sub['fields'][6]['field_name'] = 'country_id';
$zz_sub['fields'][6]['type'] = 'select';
$zz_sub['fields'][6]['sql'] = 'SELECT country_id, country_code, country
	FROM /*_PREFIX_*/countries
	ORDER BY country';
$zz_sub['fields'][6]['display_field'] = 'country';
$zz_sub['fields'][6]['search'] = '/*_PREFIX_*/countries.country';
$zz_sub['fields'][6]['geocode'] = 'country_id';
$zz_sub['fields'][6]['geocode_sql'] = 'SELECT country_code
	FROM /*_PREFIX_*/countries
	WHERE country_id = %d';
$zz_sub['fields'][6]['class'] = 'block480';

$zz_sub['fields'][7]['field_name'] = 'latitude';
$zz_sub['fields'][7]['type'] = 'number';
$zz_sub['fields'][7]['number_type'] = 'latitude';
$zz_sub['fields'][7]['kml'] = 'latitude';
$zz_sub['fields'][7]['geocode'] = 'latitude';
$zz_sub['fields'][7]['hide_in_list'] = true;

$zz_sub['fields'][8]['field_name'] = 'longitude';
$zz_sub['fields'][8]['type'] = 'number';
$zz_sub['fields'][8]['number_type'] = 'longitude';
$zz_sub['fields'][8]['kml'] = 'longitude';
$zz_sub['fields'][8]['geocode'] = 'longitude';
$zz_sub['fields'][8]['hide_in_list'] = true;

// @todo receive_mail yes/no

$zz_sub['fields'][9]['title'] = 'Type';
$zz_sub['fields'][9]['field_name'] = 'address_category_id';
$zz_sub['fields'][9]['type'] = 'select';
$zz_sub['fields'][9]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories ORDER BY sequence, category';
$zz_sub['fields'][9]['display_field'] = 'address_type';
$zz_sub['fields'][9]['search'] = '/*_PREFIX_*/categories.category';
$zz_sub['fields'][9]['show_hierarchy'] = 'main_category_id';
$zz_sub['fields'][9]['show_hierarchy_subtree'] = $zz_setting['category']['address'];

$zz_sub['fields'][20]['field_name'] = 'last_update';
$zz_sub['fields'][20]['type'] = 'timestamp';
$zz_sub['fields'][20]['hide_in_list'] = true;

$zz_sub['sql'] = 'SELECT /*_PREFIX_*/addresses.*
		, /*_PREFIX_*/categories.category AS address_type
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/addresses.address_category_id
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
';
$zz_sub['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.identifier, country, postcode, place';
