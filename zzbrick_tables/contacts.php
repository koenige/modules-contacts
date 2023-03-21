<?php 

/**
 * contacts module
 * Table with contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contacts';
$zz['table'] = '/*_PREFIX_*/contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['geojson'] = 'id';

$zz['fields'][98] = []; // image
if (wrap_setting('contacts_media')) {
	$zz['fields'][98] = zzform_include_table('contacts-media');
	$zz['fields'][98]['title'] = 'Media';
	$zz['fields'][98]['type'] = 'subtable';
	$zz['fields'][98]['hide_in_list_if_empty'] = true;
	$zz['fields'][98]['min_records'] = wrap_setting('contacts_media_min_records');
	$zz['fields'][98]['max_records'] = wrap_setting('contacts_media_max_records');
	$zz['fields'][98]['fields'][2]['type'] = 'foreign_key';
	if (wrap_setting('contacts_media_max_records') === 1) {
		$zz['fields'][98]['fields'][4]['hide_in_form'] = true;
		$zz['fields'][98]['fields'][4]['type'] = 'hidden';
		$zz['fields'][98]['fields'][4]['value'] = 1;
	}
	$zz['fields'][98]['fields'][3]['add_details'] = [
		'area' => 'media_internal',
		'fields' => ['contact_category_id'],
		'target' => [0 => [
			'ids' => 'categories',
			'table' => 'media',
			'identifier_field' => 'filename'
		]]
	];
}

$zz['fields'][2]['field_name'] = 'contact';
$zz['fields'][2]['type'] = 'memo';
$zz['fields'][2]['typo_cleanup'] = true;
$zz['fields'][2]['typo_remove_double_spaces'] = true;
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
$zz['fields'][2]['link_record'] = true;

$zz['fields'][10]['title'] = 'Short';
$zz['fields'][10]['field_name'] = 'contact_short';
$zz['fields'][10]['class'] = 'hidden480';
$zz['fields'][10]['hide_in_list_if_empty'] = true;
if (!wrap_setting('contacts_contact_short'))
	$zz['fields'][10]['hide_in_form'] = true;

$zz['fields'][11] = []; // contact_abbr

$zz['fields'][21]['title'] = 'Sort';
$zz['fields'][21]['field_name'] = 'contact_sort';
$zz['fields'][21]['hide_in_list'] = true;
if (!wrap_setting('contacts_contact_sort'))
	$zz['fields'][21]['hide_in_form'] = true;

$zz['fields'][9] = []; // persons

$zz['fields'][3]['field_name'] = 'identifier';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = ['contact_short', 'contact'];
$zz['fields'][3]['conf_identifier']['exists'] = '-';
$zz['fields'][3]['conf_identifier']['ignore_this_if']['contact'] = 'contact_short';
$zz['fields'][3]['log_username'] = true;
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
	WHERE main_category_id = %d
	ORDER BY sequence, category',
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

if (!empty($values['addresses_restrict_to'])) {
	// here, do not show address fields for every address type, only if restrict_to is set
	$values['addresses'] = mf_contacts_restrict_categories($values, 'addresses', 'address');
}
if (isset($values['addresses'])) {
	$no = 80;
} else {
	$values['addresses'][] = [
		'category_id' => 0,
		'category' => 'Address',
		'parameters' => ''
	];
	$no = 5; // @deprecated for backwards compatibility, keep no. 5 for single table
}
$subtable_params = [
	'title_desc', 'min_records', 'min_records_required', 'max_records', 'title_button'
	, 'explanation'
];
foreach ($values['addresses'] as $category_id => $category) {
	if ($category['parameters'])
		parse_str($category['parameters'], $category['parameters']);
	else
		$category['parameters'] = [];

	$zz['fields'][$no] = zzform_include_table('addresses');
	$zz['fields'][$no]['table_name'] = 'address_'.$category_id;
	$zz['fields'][$no]['title'] = $category['category'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = 0;
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	foreach ($subtable_params as $s_param) {
		if (empty($category['parameters'][$s_param])) continue;
		$zz['fields'][$no][$s_param] = $category['parameters'][$s_param];
	}
	if ($category_id) {
		// address_catgory_id
		$zz['fields'][$no]['fields'][9]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][9]['value'] = $category_id;
		$zz['fields'][$no]['fields'][9]['def_val_ignore'] = true;
		$zz['fields'][$no]['fields'][9]['hide_in_form'] = true;

		$zz['fields'][$no]['sql'] .= sprintf(' WHERE address_category_id = %d', $category_id);
	}
	if (empty($category['parameters']['fields']))
		$category['parameters']['fields'] = [];
	foreach ($category['parameters']['fields'] as $field_name => $def) {
		if ($field_name === 'country_id' AND !empty($def['default']))
			$def['default'] = wrap_id('countries', $def['default']);
		foreach ($zz['fields'][$no]['fields'] as $sub_no => $sub_field)	{
			if (empty($sub_field['field_name'])) continue;
			if ($sub_field['field_name'] !== $field_name) continue;
			$zz['fields'][$no]['fields'][$sub_no] = array_merge($sub_field, $def);
		}
	}
	// @todo use category for columns
	$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
	$no++;
}

$values['contactdetails'] = mf_contacts_restrict_categories($values, 'contactdetails', 'provider');

$no = 30;
foreach ($values['contactdetails'] as $category_id => $category) {
	// parse parameters
	$category['parameters'] = $category['parameters'] ?? [];
	if ($category['parameters'] AND !is_array($category['parameters']))
		parse_str($category['parameters'], $category['parameters']);
	if (!empty($values['contactdetails_restrict_to'])
		AND !empty($category['parameters']['if'][$values['contactdetails_restrict_to']]))
		$category['parameters'] = array_merge($category['parameters'], $category['parameters']['if'][$values['contactdetails_restrict_to']]);

	// group contactdetails?
	$continue = false;
	if (!empty($values['contactdetails_separate'])) $continue = true;
	if (!empty($category['parameters']['separate'])) {
		if (!is_array($category['parameters']['separate']) AND $category['parameters']['separate'].'' === '1') $continue = true;
		// @deprecated, use if-notation instead
		if (!empty($values['contactdetails_restrict_to'])
			AND !empty($category['parameters']['separate'][$values['contactdetails_restrict_to']])) $continue = true;
	}
	if (empty($category['parameters']['type'])) $continue = true;
	if ($continue) {
		$values['contactdetails']['none-'.$category_id] = $category;
		unset($values['contactdetails'][$category_id]);
		continue;
	}

	$key = $category['parameters']['type'];
	if (!array_key_exists($key, $values['contactdetails'])) {
		$values['contactdetails'][$key]['parameters'] = [];
		$values['contactdetails'][$key]['type'] = $key;
	}
	$values['contactdetails'][$key]['categories'][$category['category_id']] = $category;
	$values['contactdetails'][$key]['category_id'] = $category['category_id'];
	foreach ($category['parameters'] as $pkey => $pvalue) {
		if (array_key_exists($pkey, $values['contactdetails'][$key]['parameters'])) {
			if (is_numeric($pvalue))
				$values['contactdetails'][$key]['parameters'][$pkey] += $pvalue;
			else
				$values['contactdetails'][$key]['parameters'][$pkey] = $pvalue;
		} else {
			$values['contactdetails'][$key]['parameters'][$pkey] = $pvalue;
		}
	}
	$values['contactdetails'][$key]['parameters'] += $category['parameters'];
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

	$zz['fields'][$no] = zzform_include_table('contactdetails');
	$zz['fields'][$no]['class'] = 'contactdetails';
	$zz['fields'][$no]['table_name'] = 'contactdetails_'.$category['category_id'];
	$zz['fields'][$no]['title'] = $category['category'];
	if (!empty($category['parameters']['title']))
		$zz['fields'][$no]['title'] = $category['parameters']['title'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = isset($category['parameters']['min_records']) ? $category['parameters']['min_records'] : 1;
	$zz['fields'][$no]['max_records'] = !empty($category['parameters']['max_records'])
		? $category['parameters']['max_records']
		: (!empty($category['categories']) ? count($category['categories']) : 1);
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	if (!empty($category['parameters']['type']) AND in_array($category['parameters']['type'], ['mail', 'url', 'phone', 'username'])) {
		$zz['fields'][$no]['fields'][3]['type'] = $category['parameters']['type'];
	}
	$parameters_to_fields = [
		'explanation', 'parse_url', 'url', 'dont_check_username_online',
		'validate', 'title'
	];
	foreach ($parameters_to_fields as $parameter_to_field) {
		if (empty($category['parameters'][$parameter_to_field])) continue;
		$zz['fields'][$no]['fields'][3][$parameter_to_field] = $category['parameters'][$parameter_to_field];
	}
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
	$zz['fields'][$no]['fields'][5]['hide_in_form']
		= isset($category['parameters']['label']) ? !$category['parameters']['label']
		: (wrap_setting('contacts_details_with_label') ? false : true);
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

$zz['fields'][16]['field_name'] = 'start_date';
$zz['fields'][16]['type'] = 'date';
$zz['fields'][16]['hide_in_list'] = true;
if (!wrap_setting('contacts_start_date'))
	$zz['fields'][16]['hide_in_form'] = true;

$zz['fields'][17]['field_name'] = 'end_date';
$zz['fields'][17]['type'] = 'date';
$zz['fields'][17]['hide_in_list'] = true;
if (!wrap_setting('contacts_end_date'))
	$zz['fields'][17]['hide_in_form'] = true;

$zz['fields'][18]['field_name'] = 'country_id';
$zz['fields'][18]['type'] = 'select';
$zz['fields'][18]['sql'] = 'SELECT country_id, country_code, country
	FROM /*_PREFIX_*/countries
	ORDER BY country';
$zz['fields'][18]['sql_translate'] = ['country_id' => 'countries'];
$zz['fields'][18]['sql_character_set'][1] = 'latin1';
$zz['fields'][18]['sql_character_set'][2] = 'latin1';
$zz['fields'][18]['search'] = '/*_PREFIX_*/countries.country';
$zz['fields'][18]['hide_in_list'] = true;
if (!wrap_setting('contacts_country'))
	$zz['fields'][18]['hide_in_form'] = true;

$zz['fields'][13]['field_name'] = 'remarks';
$zz['fields'][13]['type'] = 'memo';
$zz['fields'][13]['format'] = 'markdown';
$zz['fields'][13]['merge_append'] = true;
$zz['fields'][13]['rows'] = 3;
$zz['fields'][13]['hide_in_list'] = true;
$zz['fields'][13]['hide_in_form'] = true;
$zz['fields'][13]['explanation'] = 'Internal remarks';
if (wrap_access('contacts_remarks')) {
	$zz['fields'][13]['hide_in_form'] = false;
}

$values['relations'] = mf_contacts_restrict_categories($values, 'relations', 'relation');

$no = 60;
// associations?
$pos = 0;
$new = [];
foreach ($values['relations'] as $index => $relation) {
	if (!$relation['parameters']) continue;
	$pos++;
	parse_str($relation['parameters'], $values['relations'][$index]['params']);
	if (isset($values['relations_restrict_to'])) {
		$key = sprintf('%s_params', $values['relations_restrict_to']);
		if (!empty($values['relations'][$index]['params'][$key]))
			$values['relations'][$index]['params'] = array_merge(
				$values['relations'][$index]['params'], $values['relations'][$index]['params'][$key]
			);
	}
	if (!empty($values['relations'][$index]['params']['association'])) {
		$new[$pos] = $values['relations'][$index];
		$new[$pos]['params']['integrate_in_next'] = true;
		$new[$pos]['association'] = true;
	}
}
foreach ($new as $pos => $association) {
	array_splice($values['relations'], $pos -1, 0, [$association]);
}

foreach ($values['relations'] as $index => $relation) {
	$zz['fields'][$no] = zzform_include_table('contacts-contacts');
	if (!empty($relation['association']) OR !empty($relation['params']['reverse_relation'])) {
		$contact_no = 2; // contact_id
		$f_contact_no = 3; // main_contact_id
		$zz['fields'][$no]['sql'] = $zz['fields'][$no]['sql_association'];
	} else {
		$contact_no = 3; // main_contact_id
		$f_contact_no = 2; // contact_id
	}
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['table_name'] = 'contacts_contacts'.$relation['category_id'].$index;
	$zz['fields'][$no]['title'] = $relation['category'];
	$zz['fields'][$no]['sql'] .= sprintf(' WHERE relation_category_id = %d', $relation['category_id']);
	$zz['fields'][$no]['sql'] .= ' ORDER BY sequence, contact';
	if (isset($relation['params']['show_title']))
		$zz['fields'][$no]['show_title'] = $relation['params']['show_title'];
	if (isset($relation['params']['integrate_in_next']))
		$zz['fields'][$no]['integrate_in_next'] = $relation['params']['integrate_in_next'];
	if (isset($relation['params']['min_records']))
		$zz['fields'][$no]['min_records'] = $relation['params']['min_records'];
	if (isset($relation['params']['max_records']))
		$zz['fields'][$no]['max_records'] = $relation['params']['max_records'];
	if (!empty($relation['params']['placeholder']))
		$zz['fields'][$no]['fields'][$contact_no]['placeholder'] = $relation['params']['placeholder'];
	else
		$zz['fields'][$no]['fields'][$contact_no]['placeholder'] = $relation['category'];
	$zz['fields'][$no]['fields'][$f_contact_no]['type'] = 'foreign_key';
	if (!empty($relation['params']['main_contact']['category'])) {
		$categories = $relation['params']['main_contact']['category'];
		if (!is_array($categories)) $categories = [$categories];
		foreach ($categories as $index => $category) {
			$categories[$index] = wrap_category_id('contact/'.$category);
		}
		$zz['fields'][$no]['fields'][$contact_no]['sql'] = wrap_edit_sql(
			$zz['fields'][$no]['fields'][$contact_no]['sql'], 'WHERE',
			sprintf('contact_category_id IN (%s)', implode(',', $categories))
		);
	}
	if (!empty($relation['params']['main_contact']['add_details'])) {
		$zz['fields'][$no]['fields'][$contact_no]['add_details']
			= wrap_setting('base').$relation['params']['main_contact']['add_details'];
		// no recursive linking on forms that link to add_details on themselves
		// this is not possible to edit
		$my_url = parse_url(wrap_setting('request_uri'));
		if ($zz['fields'][$no]['fields'][$contact_no]['add_details'] === $my_url['path']) {
			$zz['fields'][$no]['hide_in_form'] = true;
		}
	}
	$zz['fields'][$no]['fields'][6]['placeholder'] = 'No.';
	if (isset($relation['params']['sequence'])) {
		if ($relation['params']['sequence'] === 'hidden') {
			$zz['fields'][$no]['fields'][6]['type'] = 'hidden';
			$zz['fields'][$no]['fields'][6]['class'] = 'hidden';
		} elseif ($relation['params']['sequence'] === 'sequence') {
			$zz['fields'][$no]['fields'][6]['type'] = 'sequence';
		} elseif (!$relation['params']['sequence']) {
			$zz['fields'][$no]['fields'][6]['hide_in_form'] = true;
		}
	}
	// category
	$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][4]['type_detail'] = 'select';
	$zz['fields'][$no]['fields'][4]['value'] = $relation['category_id'];
	$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
	// remarks
	if (empty($relation['params']['remarks']))
		unset($zz['fields'][$no]['fields'][9]);
	// role
	if (empty($relation['params']['role']))
		unset($zz['fields'][$no]['fields'][11]);
	else
		$zz['fields'][$no]['fields'][11]['placeholder'] = true;
	// published
	$zz['fields'][$no]['fields'][10]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][10]['hide_in_form'] = true;
	$zz['fields'][$no]['hide_in_list'] = true;
	if (!empty($relation['params']['explanation']))
		$zz['fields'][$no]['explanation'] = $relation['params']['explanation'];
	
	$no++;
}

if (wrap_setting('contacts_identifiers')) {
	$zz['fields'][19] = zzform_include_table('contacts-identifiers');
	$zz['fields'][19]['type'] = 'subtable';
	$zz['fields'][19]['fields'][2]['type'] = 'foreign_key';
	$zz['fields'][19]['sql'] .= $zz['fields'][19]['sqlorder'];
	$zz['fields'][19]['fields'][4]['exclude_from_search'] = true;
	$zz['fields'][19]['fields'][4]['for_action_ignore'] = true;
	$zz['fields'][19]['form_display'] = 'lines';
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
if (!wrap_setting('contacts_parameters'))
	$zz['fields'][15]['hide_in_form'] = true;

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
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
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

$zz['filter'][2]['title'] = wrap_text('Alpha');
$zz['filter'][2]['identifier'] = 'alpha';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = 'UPPER(SUBSTRING(contact, 1, 1))';
$zz['filter'][2]['sql'] = 'SELECT DISTINCT 
		UPPER(SUBSTRING(contact, 1, 1)), 
		UPPER(SUBSTRING(contact, 1, 1))
	FROM /*_PREFIX_*/contacts
	ORDER BY UPPER(SUBSTRING(contact, 1, 1))';

if (!empty($_GET['nolist']) AND empty($_GET['referer']))
	$zz['dynamic_referer'] = $zz['fields'][2]['link'];

$zz['hooks']['before_insert'][] = 'mf_contacts_hook_check_contactdetails';
$zz['hooks']['before_update'][] = 'mf_contacts_hook_check_contactdetails';

$zz_conf['export'][] = 'CSV Excel';

$zz_conf['redirect_to_referer_zero_records'] = true;

if (wrap_access('contacts_merge'))
	$zz_conf['merge'] = true;
