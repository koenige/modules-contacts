<?php 

/**
 * Zugzwang Project
 * Table with contacts
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2021 Gustaf Mossakowski
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
$zz['fields'][2]['link'] = [
	'function' => 'mf_contacts_profile_path',
	'fields' => ['identifier', 'contact_parameters']
];

$zz['fields'][10]['title'] = 'Short';
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
	if (isset($values['contactdetails_restrict_to']))
		$restrict_to = 'AND parameters LIKE "%%&'.$values['contactdetails_restrict_to'].'=1%%"';
	else
		$restrict_to = '';
	$sql = 'SELECT category_id, category, parameters 
		FROM categories
		WHERE main_category_id = %d
		%s
		ORDER BY sequence, path';
	$sql = sprintf($sql, wrap_category_id('provider'), $restrict_to);
	$values['contactdetails'] = wrap_db_fetch($sql, 'category_id');
}

require __DIR__.'/contactdetails.php';
$no = 30;
foreach ($values['contactdetails'] as $category_id => $category) {
	if (empty($category['parameters'])) {
		$values['contactdetails'][$category_id]['parameters'] = [];
		continue;
	}
	if (!is_array($category['parameters'])) {
		parse_str($category['parameters'], $parameters);
		$values['contactdetails'][$category_id]['parameters'] = $parameters;
	}
	
	// group contactdetails?
	if (!empty($parameters['separate'])) {
		if ($parameters['separate'] === 1) continue;
		if (!empty($values['contactdetails_restrict_to'])
			AND !empty($parameters['separate'][$values['contactdetails_restrict_to']]))
			continue;
	}
	$key = $parameters['type'];
	if (!array_key_exists($key, $values['contactdetails'])) {
		$values['contactdetails'][$key]['parameters'] = [];
		$values['contactdetails'][$key]['type'] = $parameters['type'];
	}
	$values['contactdetails'][$key]['categories'][$category['category_id']] = $category;
	$values['contactdetails'][$key]['category_id'] = $category['category_id'];
	foreach ($parameters as $pkey => $pvalue) {
		if (array_key_exists($pkey, $values['contactdetails'][$key]['parameters'])) {
			if (is_numeric($pvalue))
				$values['contactdetails'][$key]['parameters'][$pkey] += $pvalue;
			else
				$values['contactdetails'][$key]['parameters'][$pkey] = $pvalue;
		} else {
			$values['contactdetails'][$key]['parameters'][$pkey] = $pvalue;
		}
	}
	$values['contactdetails'][$key]['parameters'] += $parameters;
	unset($values['contactdetails'][$category_id]);
}

foreach ($values['contactdetails'] as $category) {
	if (!empty($category['categories'])) {
		if (!empty($category['parameters']['category'])) {
			$category['category'] = $category['parameters']['category'];
			$category['category'] = wrap_text($category['category']);
		} elseif (count($category['categories']) === 1) {
			$category['category'] = reset($category['categories']);
			$category['category'] = $category['category']['category'];
		} else {
			$category['category'] = ucfirst($category['parameters']['type']);
			$category['category'] = wrap_text($category['category']);
		}
	}

	$zz['fields'][$no] = $zz_sub;
	$zz['fields'][$no]['class'] = 'contactdetails';
	$zz['fields'][$no]['table_name'] = 'contactdetails_'.$category['category_id'];
	$zz['fields'][$no]['title'] = $category['category'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = isset($category['parameters']['min_records']) ? $category['parameters']['min_records'] : 1;
	$zz['fields'][$no]['max_records'] = !empty($category['parameters']['max_records'])
		? $category['parameters']['max_records']
		: (!empty($category['categories']) ? count($category['categories']) : 1);
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	if (!empty($category['parameters']['type']) AND in_array($category['parameters']['type'], ['mail', 'url', 'phone'])) {
		$zz['fields'][$no]['fields'][3]['type'] = $category['parameters']['type'];
	}
	if (!empty($category['parameters']['explanation']))
		$zz['fields'][$no]['fields'][3]['explanation'] = $category['parameters']['explanation'];
	if (empty($category['categories']))
		$category['categories'][$category['category_id']] = $category;
	$zz['fields'][$no]['sql'] .= sprintf(
		' WHERE /*_PREFIX_*/contactdetails.provider_category_id IN (%s)'
		, implode(',', array_keys($category['categories']))
	);
	if (count($category['categories']) === 1) {
		$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
		$zz['fields'][$no]['fields'][4]['value'] = $category['category_id'];
	} else {
		$zz['fields'][$no]['fields'][4]['sql'] .= sprintf(
			' AND category_id IN (%s)'
			, implode(',', array_keys($category['categories']))
		);
		$zz['fields'][$no]['fields'][4]['default'] = key($category['categories']);
	}
	$zz['fields'][$no]['fields'][4]['def_val_ignore'] = true;
	$zz['fields'][$no]['fields'][4]['for_action_ignore'] = true;
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['subselect']['sql'] = sprintf('SELECT category, identification, contact_id
		FROM /*_PREFIX_*/contactdetails
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id
		WHERE /*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($category['categories'])));
	$zz['fields'][$no]['if']['export_mode']['subselect']['sql'] = sprintf('SELECT identification, contact_id
		FROM /*_PREFIX_*/contactdetails
		LEFT JOIN /*_PREFIX_*/categories
			ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id
		WHERE /*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($category['categories'])));
	$zz['fields'][$no]['subselect']['concat_fields'] = ' ';
	$zz['fields'][$no]['unless']['export_mode']['subselect']['field_prefix'][0] = '<em>';
	$zz['fields'][$no]['unless']['export_mode']['subselect']['field_suffix'][0] = ':</em>';
	$zz['fields'][$no]['if']['export_mode']['subselect']['concat_rows'] = "\r";
	$zz['fields'][$no]['export_no_html'] = true;
	if (!empty($category['field_sequence']))
		$zz['fields'][$no]['field_sequence'] = $category['field_sequence'];
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

$zz['fields'][13]['field_name'] = 'remarks';
$zz['fields'][13]['type'] = 'memo';
$zz['fields'][13]['format'] = 'markdown';
$zz['fields'][13]['hide_in_list'] = true;
$zz['fields'][13]['merge_append'] = true;
$zz['fields'][13]['rows'] = 3;

if (!isset($values['relations'])) {
	if (isset($values['relations_restrict_to']))
		$restrict_to = 'AND parameters LIKE "%%&'.$values['relations_restrict_to'].'=1%%"';
	else
		$restrict_to = '';
	$sql = 'SELECT category_id, category, parameters 
		FROM categories
		WHERE main_category_id = %d
		%s
		ORDER BY sequence, path';
	$sql = sprintf($sql, wrap_category_id('relation'), $restrict_to);
	$values['relations'] = wrap_db_fetch($sql, 'category_id');
}

$no = 60;
foreach ($values['relations'] as $relation) {
	parse_str($relation['parameters'], $relation_parameters);
	$zz['fields'][$no] = zzform_include_table('contacts-contacts');
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['table_name'] = 'contacts_contacts'.$relation['category_id'];
	$zz['fields'][$no]['title'] = $relation['category'];
	$zz['fields'][$no]['sql'] .= sprintf(' WHERE relation_category_id = %d', $relation['category_id']);
	$zz['fields'][$no]['sql'] .= ' ORDER BY sequence, contact';
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	// category
	$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][4]['type_detail'] = 'select';
	$zz['fields'][$no]['fields'][4]['value'] = $relation['category_id'];
	$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
	// remarks
	unset($zz['fields'][$no]['fields'][9]);
	// role
	if (empty($relation_parameters['role']))
		unset($zz['fields'][$no]['fields'][11]);
	// published
	$zz['fields'][$no]['fields'][10]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][10]['value'] = 'yes';
	$zz['fields'][$no]['fields'][10]['hide_in_form'] = true;
	$zz['fields'][$no]['hide_in_list'] = true;
	
	$no++;
}

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

$zz['fields'][97]['field_name'] = 'created';
$zz['fields'][97]['type'] = 'hidden';
$zz['fields'][97]['type_detail'] = 'datetime';
$zz['fields'][97]['default'] = date('Y-m-d H:i:s');
$zz['fields'][97]['hide_in_list'] = true;
$zz['fields'][97]['merge_ignore'] = true;
$zz['fields'][97]['if']['add']['hide_in_form'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/contacts.*, category
		, (SELECT CONCAT(latitude, ",", longitude) FROM /*_PREFIX_*/addresses
			WHERE /*_PREFIX_*/addresses.contact_id = /*_PREFIX_*/contacts.contact_id
			LIMIT 1) AS latlon
		, /*_PREFIX_*/categories.parameters AS contact_parameters
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

if (!empty($_GET['nolist'])) {
	$zz['dynamic_referer'] = $zz['fields'][2]['link'];
}

$zz_conf['export'][] = 'CSV Excel';

$zz_conf['redirect_to_referer_zero_records'] = true;

if (wrap_access('contacts_merge'))
	$zz_conf['merge'] = true;
