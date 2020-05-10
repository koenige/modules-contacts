<?php 

/**
 * Zugzwang Project
 * Table with contacts
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contacts';
$zz['table'] = '/*_PREFIX_*/contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['geojson'] = 'id';

$zz['fields'][98] = []; // image

$zz['fields'][2]['field_name'] = 'contact';
$zz['fields'][2]['type'] = 'memo';
$zz['fields'][2]['trim'] = true;
$zz['fields'][2]['rows'] = 2;
$zz['fields'][2]['cols'] = 50;
$zz['fields'][2]['kml'] = 'title';
$zz['fields'][2]['geojson'] = 'title';
$zz['fields'][2]['export_no_html'] = true;
$zz['fields'][2]['unless']['export_mode']['list_format'] = 'nl2br';
$zz['fields'][2]['merge_equal'] = true;
$zz['fields'][2]['add_details_destination'] = true;

$zz['fields'][10]['field_name'] = 'contact_short';
$zz['fields'][10]['class'] = 'hidden480';
$zz['fields'][10]['hide_in_list_if_empty'] = true;

$zz['fields'][11] = []; // contact_abbr

$zz['fields'][9] = []; // persons

$zz['fields'][3]['field_name'] = 'identifier';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = ['contact_short', 'contact'];
$zz['fields'][3]['conf_identifier']['exists'] = '-';
$zz['fields'][3]['conf_identifier']['ignore_this_if']['contact'] = 'contact_short';
$zz['fields'][3]['hide_in_list'] = true;
$zz['fields'][3]['geojson'] = 'identifier';
$zz['fields'][3]['merge_ignore'] = true;
$zz['fields'][3]['unique'] = true;
$zz['fields'][3]['character_set'] = 'latin1';

$zz['fields'][4]['title'] = 'Category';
$zz['fields'][4]['field_name'] = 'contact_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('contact')
);
$zz['fields'][4]['key_field_name'] = 'category_id';
$zz['fields'][4]['if']['where']['hide_in_form'] = true;
$zz['fields'][4]['if']['where']['hide_in_list'] = true;
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['geojson'] = 'category';
$contact_categories = wrap_db_fetch($zz['fields'][4]['sql'], 'category_id');
if (count($contact_categories) === 1) $zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['exclude_from_search'] = true;

$zz['fields'][5] = zzform_include_table('addresses');
$zz['fields'][5]['title'] = 'Address';
$zz['fields'][5]['type'] = 'subtable';
$zz['fields'][5]['min_records'] = 0;
$zz['fields'][5]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][5]['subselect']['sql'] = 'SELECT address, postcode, place, country, contact_id
	FROM /*_PREFIX_*/addresses
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/addresses.address_category_id = /*_PREFIX_*/categories.category_id
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)';
// @todo use category for columns
$zz['fields'][5]['subselect']['concat_fields'] = '';
$zz['fields'][5]['subselect']['list_field_format'] = 'nl2br';
//$zz['fields'][5]['subselect']['field_prefix'][0] = '<em>';
//$zz['fields'][5]['subselect']['field_suffix'][0] = ':</em><br>';
$zz['fields'][5]['unless']['export_mode']['subselect']['field_suffix'][0] = '<br>';
$zz['fields'][5]['if']['export_mode']['subselect']['field_suffix'][0] = "\r";
$zz['fields'][5]['subselect']['field_suffix'][1] = ' ';
$zz['fields'][5]['unless']['export_mode']['subselect']['field_suffix'][2] = '<br>';
$zz['fields'][5]['if']['export_mode']['subselect']['field_suffix'][2] = "\r";
$zz['fields'][5]['unless']['export_mode']['list_append_next'] = true;
$zz['fields'][5]['export_no_html'] = true;
$zz['fields'][5]['if']['export_mode']['subselect']['concat_rows'] = "\r\r";

if (!isset($values['contactdetails'])) {
	$sql = 'SELECT category_id, category, parameters 
		FROM categories
		WHERE main_category_id = %d
		ORDER BY sequence, path';
	$sql = sprintf($sql, wrap_category_id('provider'));
	$values['contactdetails'] = wrap_db_fetch($sql, 'category_id');
}

require __DIR__.'/contactdetails.php';
$no = 30;
foreach ($values['contactdetails'] as $category) {
	$parameters = [];
	parse_str($category['parameters'], $parameters);
	$zz['fields'][$no] = $zz_sub;
	$zz['fields'][$no]['table_name'] = 'contactdetails_'.$category['category_id'];
	$zz['fields'][$no]['title'] = $category['category'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = 1;
	$zz['fields'][$no]['max_records'] = !empty($parameters['max_records']) ? $parameters['max_records'] : 1;
	$zz['fields'][$no]['sql'] .= sprintf(' WHERE /*_PREFIX_*/contactdetails.provider_category_id = %d', $category['category_id']);
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	if (!empty($parameters['type']) AND in_array($parameters['type'], ['mail', 'url', 'phone'])) {
		$zz['fields'][$no]['fields'][3]['type'] = $parameters['type'];
	}
	$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
	$zz['fields'][$no]['fields'][4]['value'] = $category['category_id'];
	$zz['fields'][$no]['fields'][4]['def_val_ignore'] = true;
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['subselect']['sql'] = sprintf('SELECT category, identification, contact_id
		FROM /*_PREFIX_*/contactdetails
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id
		WHERE /*_PREFIX_*/contactdetails.provider_category_id = %d', $category['category_id']);
	$zz['fields'][$no]['if']['export_mode']['subselect']['sql'] = sprintf('SELECT identification, contact_id
		FROM /*_PREFIX_*/contactdetails
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id
		WHERE /*_PREFIX_*/contactdetails.provider_category_id = %d', $category['category_id']);
	$zz['fields'][$no]['subselect']['concat_fields'] = ' ';
	$zz['fields'][$no]['unless']['export_mode']['subselect']['field_prefix'][0] = '<em>';
	$zz['fields'][$no]['unless']['export_mode']['subselect']['field_suffix'][0] = ':</em>';
	$zz['fields'][$no]['if']['export_mode']['subselect']['concat_rows'] = "\r";
	$zz['fields'][$no]['export_no_html'] = true;
	if ($no - 29 < count($values['contactdetails'])) {
		$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
	}
	$no++;
}
unset($zz_sub);

$zz['fields'][7] = []; // contacts_verifications

$zz['fields'][8]['field_name'] = 'latlon';
$zz['fields'][8]['type'] = 'display';
$zz['fields'][8]['exclude_from_search'] = true;
$zz['fields'][8]['hide_in_form'] = true;
$zz['fields'][8]['unless']['export_mode']['hide_in_list'] = true;
$zz['fields'][8]['geojson'] = 'latitude/longitude';

$zz['fields'][12]['field_name'] = 'description';
$zz['fields'][12]['type'] = 'memo';
$zz['fields'][12]['hide_in_list'] = true;
$zz['fields'][12]['format'] = 'markdown';

$zz['fields'][13] = [];  // remarks

$zz['fields'][14]['title_tab'] = 'Pub.';
$zz['fields'][14]['field_name'] = 'published';
$zz['fields'][14]['type'] = 'select';
$zz['fields'][14]['enum'] = ['yes', 'no'];
$zz['fields'][14]['default'] = 'yes';
$zz['fields'][14]['class'] = 'hidden480';
$zz['fields'][14]['explanation'] = 'Publish on website?';

$zz['fields'][15]['field_name'] = 'parameters';
$zz['fields'][15]['type'] = 'parameter';
$zz['fields'][15]['hide_in_list'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/contacts.*, category
		, (SELECT CONCAT(latitude, ",", longitude) FROM /*_PREFIX_*/addresses
			WHERE /*_PREFIX_*/addresses.contact_id = /*_PREFIX_*/contacts.contact_id
			LIMIT 1) AS latlon
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/contacts_verifications USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY identifier';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][1]['title'] = wrap_text('Category');
$zz['filter'][1]['identifier'] = 'category';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'contact_category_id';
$zz['filter'][1]['field_name'] = 'contact_category_id';

$zz_conf['export'][] = 'CSV Excel';
