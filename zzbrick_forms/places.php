<?php 

/**
 * contacts module
 * Form for places
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values['contactdetails_restrict_to'] = 'places';
$values['relations_restrict_to'] = 'places';
$zz = zzform_include('contacts', $values, 'forms');

$zz['title'] = 'Venues';

// contact
$zz['fields'][2]['title'] = 'Venue';

// category
$zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['sql'] = wrap_edit_sql($zz['fields'][4]['sql'],
	'WHERE', 'categories.parameters LIKE "%&events_location=1%"'
);

// addresses
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['max_records'] = 1;
$zz['fields'][5]['form_display'] = 'inline';
if (!empty($zz['fields'][5]['fields'][10])) {
	// receive_mail
	$zz['fields'][5]['fields'][10]['type'] = 'hidden';
	$zz['fields'][5]['fields'][10]['type_detail'] = 'enum';
	$zz['fields'][5]['fields'][10]['value'] = 'yes';
	$zz['fields'][5]['fields'][10]['hide_in_form'] = true;
}
$zz['fields'][5]['fields'][9]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories
	WHERE parameters LIKE "%&places=1%"
	ORDER BY sequence, category';
unset($zz['fields'][5]['fields'][9]['default']);

// published
$zz['fields'][14]['type'] = 'hidden';
$zz['fields'][14]['type_detail'] = 'enum';
$zz['fields'][14]['value'] = 'yes';
$zz['fields'][14]['hide_in_form'] = true;

// start_date, end_date
$zz['fields'][16]['hide_in_form'] = true;
$zz['fields'][17]['hide_in_form'] = true;

// country
$zz['fields'][18]['hide_in_form'] = true;
$zz['fields'][18]['hide_in_list'] = true;

// parameters
unset($zz['fields'][15]);

$zz['sql'] .= 'WHERE categories.parameters LIKE "%&events_location=1%"';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	WHERE categories.parameters LIKE "%&events_location=1%"
	ORDER BY category';
