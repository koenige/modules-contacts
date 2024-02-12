<?php 

/**
 * contacts module
 * Form for contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values = $values ?? [];
$zz = zzform_include('contacts', $values);

// restrictions because of contact category?
if (!empty($_GET['where']['contact_category_id']) OR !empty($values['contact_category_id'])) {
	$category_id = $_GET['where']['contact_category_id'] ?? $values['contact_category_id'];
	$sql = 'SELECT parameters FROM categories
	    WHERE category_id = %d';
	$sql = sprintf($sql, $category_id);
	$parameters = wrap_db_fetch($sql, '', 'single value');
	if ($parameters) {
		parse_str($parameters, $parameters);
		if (!empty($parameters['values']))
			foreach ($parameters['values'] as $key => $value) {
				if ($value === '[]') $value = [];
				$values[$key] = $value;
			} 
	}
}


//
// contacts-media
//

if (wrap_setting('contacts_media')) {
	$zz['fields'][98] = zzform_include('contacts-media');
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

//
// addresses
//

$separator = false;
if (!empty($values['addresses_restrict_to']))
	mf_default_categories_restrict($values, 'addresses', 'address');
if (isset($values['addresses'])) {
	$no = 80;
} else {
	$values['addresses'][] = [
		'category_id' => 0,
		'category' => 'Address',
		'parameters' => []
	];
	$no = 5; // @deprecated for backwards compatibility, keep no. 5 for single table
}
$subtable_params = [
	'title_desc', 'min_records', 'min_records_required', 'max_records', 'title_button'
	, 'explanation'
];
foreach ($values['addresses'] as $category_id => $category) {
	$zz['fields'][$no] = zzform_include('addresses');
	if (!$separator) {
		$zz['fields'][$no]['separator_before'] = true;
		$separator = true;
	}
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

//
// contactdetails
//

mf_default_categories_restrict($values, 'contactdetails', 'provider');

$no = 30;
foreach ($values['contactdetails'] as $category_id => $category) {
	// parse parameters
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
			$category['category'] = $category['parameters']['type'];
			$category['category'] = $category['category'] === 'url' ? strtoupper($category['category']) : ucfirst($category['category']);
			$category['category'] = wrap_text($category['category']);
		}
	}

	$zz['fields'][$no] = zzform_include('contactdetails');
	if (!$separator) {
		$zz['fields'][$no]['separator_before'] = true;
		$separator = true;
	}
	$zz['fields'][$no]['title_tab'] = 'Contact Details';
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
	$zz['fields'][$no]['subselect']['sql'] = wrap_edit_sql(
		$zz['fields'][$no]['subselect']['sql'], 'WHERE',
		sprintf('/*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($category['categories'])))
	);
	$zz['fields'][$no]['if']['export_mode']['subselect']['sql'] = wrap_edit_sql(
		$zz['fields'][$no]['if']['export_mode']['subselect']['sql'], 'WHERE',
		sprintf('/*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($category['categories'])))
	);
	$zz['fields'][$no]['export_no_html'] = true;
	if (!empty($category['field_sequence']))
		$zz['fields'][$no]['field_sequence'] = $category['field_sequence'];
	if ($no - 29 < count($values['contactdetails'])) {
		$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
	}
	$no++;
}

// separator below contact details
if (!empty($zz['fields'][$no - 1]))
	$zz['fields'][$no - 1]['separator'] = true;

//
// contacts_categories
//

mf_default_categories_subtable($zz, 'contacts', 'contact-properties', 50);

//
// contacts-contacts
//

mf_default_categories_restrict($values, 'relations', 'relation');

$separator = false;
$no = 60;
// associations?
$pos = 0;
$new = [];
foreach ($values['relations'] as $index => $relation) {
	if (!$relation['parameters']) continue;
	$pos++;
	if (isset($values['relations_restrict_to'])) {
		$key = sprintf('%s_params', $values['relations_restrict_to']);
		if (!empty($relation['parameters'][$key]))
			$values['relations'][$index]['parameters'] = array_merge(
				$values['relations'][$index]['parameters'], $values['relations'][$index]['parameters'][$key]
			);
	}
	if (!empty($values['relations'][$index]['parameters']['association'])) {
		$new[$pos] = $values['relations'][$index];
		$new[$pos]['parameters']['integrate_in_next'] = true;
		$new[$pos]['association'] = true;
	}
}
foreach ($new as $pos => $association)
	array_splice($values['relations'], $pos -1, 0, [$association]);

foreach ($values['relations'] as $index => $relation) {
	$zz['fields'][$no] = zzform_include('contacts-contacts');
	if (!$separator) {
		$zz['fields'][$no]['separator_before'] = true;
		$separator = true;
	}
	if (!empty($relation['association']) OR !empty($relation['parameters']['reverse_relation'])) {
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
	if (isset($relation['parameters']['show_title']))
		$zz['fields'][$no]['show_title'] = $relation['parameters']['show_title'];
	if (isset($relation['parameters']['integrate_in_next']))
		$zz['fields'][$no]['integrate_in_next'] = $relation['parameters']['integrate_in_next'];
	if (isset($relation['parameters']['min_records']))
		$zz['fields'][$no]['min_records'] = $relation['parameters']['min_records'];
	if (isset($relation['parameters']['max_records']))
		$zz['fields'][$no]['max_records'] = $relation['parameters']['max_records'];
	if (!empty($relation['parameters']['placeholder']))
		$zz['fields'][$no]['fields'][$contact_no]['placeholder'] = $relation['parameters']['placeholder'];
	else
		$zz['fields'][$no]['fields'][$contact_no]['placeholder'] = $relation['category'];
	$zz['fields'][$no]['fields'][$f_contact_no]['type'] = 'foreign_key';
	if (!empty($relation['parameters']['main_contact']['category'])) {
		$categories = $relation['parameters']['main_contact']['category'];
		if (!is_array($categories)) $categories = [$categories];
		foreach ($categories as $index => $category) {
			$categories[$index] = wrap_category_id('contact/'.$category);
		}
		$zz['fields'][$no]['fields'][$contact_no]['sql'] = wrap_edit_sql(
			$zz['fields'][$no]['fields'][$contact_no]['sql'], 'WHERE',
			sprintf('contact_category_id IN (%s)', implode(',', $categories))
		);
	}
	if (!empty($relation['parameters']['main_contact']['add_details'])) {
		$zz['fields'][$no]['fields'][$contact_no]['add_details']
			= wrap_setting('base').$relation['parameters']['main_contact']['add_details'];
		// no recursive linking on forms that link to add_details on themselves
		// this is not possible to edit
		if ($zz['fields'][$no]['fields'][$contact_no]['add_details'] === parse_url(wrap_setting('request_uri'), PHP_URL_PATH))
			$zz['fields'][$no]['hide_in_form'] = true;
	}
	$zz['fields'][$no]['fields'][6]['placeholder'] = 'No.';
	if (isset($relation['parameters']['sequence'])) {
		if ($relation['parameters']['sequence'] === 'hidden') {
			$zz['fields'][$no]['fields'][6]['type'] = 'hidden';
			$zz['fields'][$no]['fields'][6]['class'] = 'hidden';
		} elseif ($relation['parameters']['sequence'] === 'sequence') {
			$zz['fields'][$no]['fields'][6]['type'] = 'sequence';
		} elseif (!$relation['parameters']['sequence']) {
			$zz['fields'][$no]['fields'][6]['hide_in_form'] = true;
		}
	}
	// category
	$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][4]['type_detail'] = 'select';
	$zz['fields'][$no]['fields'][4]['value'] = $relation['category_id'];
	$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
	// remarks
	$zz['fields'][$no]['fields'][9]['placeholder'] = 'Remarks';
	if (empty($relation['parameters']['remarks']))
		unset($zz['fields'][$no]['fields'][9]);
	// role
	if (empty($relation['parameters']['role']))
		unset($zz['fields'][$no]['fields'][11]);
	else
		$zz['fields'][$no]['fields'][11]['placeholder'] = true;
	// published
	$zz['fields'][$no]['fields'][10]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][10]['hide_in_form'] = true;
	$zz['fields'][$no]['hide_in_list'] = true;
	if (!empty($relation['parameters']['explanation']))
		$zz['fields'][$no]['explanation'] = $relation['parameters']['explanation'];
	
	$no++;
}

//
// contacts-identifiers
//

if (wrap_setting('contacts_identifiers')) {
	$zz['fields'][19] = zzform_include('contacts-identifiers');
	$zz['fields'][19]['type'] = 'subtable';
	$zz['fields'][19]['fields'][2]['type'] = 'foreign_key';
	$zz['fields'][19]['sql'] .= $zz['fields'][19]['sqlorder'];
	$zz['fields'][19]['fields'][4]['exclude_from_search'] = true;
	$zz['fields'][19]['fields'][4]['for_action_ignore'] = true;
	$zz['fields'][19]['form_display'] = 'lines';
	$zz['fields'][19]['hide_in_list'] = true;
}

