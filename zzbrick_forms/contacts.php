<?php 

/**
 * contacts module
 * Form for contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values = $values ?? [];
$zz = zzform_include('contacts', $values);

// country_id

if (wrap_setting('contacts_country_list'))
	$zz['fields'][18]['hide_in_list'] = false;


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
		'area' => 'media_internal_folder',
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
if (mf_default_contexts($values, 'addresses'))
	mf_default_categories_restrict($values, 'addresses', 'address');
if (isset($values['addresses'])) {
	$no = 80;
} elseif (wrap_setting('contacts_addresses_legacy')) {
	$values['addresses'][] = [
		'category_id' => 0,
		'category' => 'Address',
		'parameters' => []
	];
	$no = 5; // @deprecated for backwards compatibility, keep no. 5 for single table
} else {
	$values['addresses'] = [];
}
foreach ($values['addresses'] as $category_id => $category) {
	mf_contacts_addresses_subtable($zz, $category, $no);
	if (!$separator) {
		$zz['fields'][$no]['separator_before'] = true;
		$separator = true;
	}
	$no++;
}

//
// contactdetails
//

mf_default_categories_restrict($values, 'contactdetails', 'channel');

$no = 30;
foreach ($values['contactdetails'] as $category_id => $category) {
	$continue = false;
	if (!empty($category['parameters']['contacts_details_separate'])
		AND !is_array($category['parameters']['contacts_details_separate'])
		AND $category['parameters']['contacts_details_separate'].'' === '1') {
		$continue = true;
	}
	if (empty($category['parameters']['zzform']['type'])) $continue = true;
	if ($continue) {
		$values['contactdetails']['none-'.$category_id] = $category;
		unset($values['contactdetails'][$category_id]);
		continue;
	}

	$key = $category['parameters']['zzform']['type'];
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
	mf_contacts_contactdetails_subtable($zz, $category, $no);
	if (!$separator) {
		$zz['fields'][$no]['separator_before'] = true;
		$separator = true;
	}
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

mf_default_categories_subtable($zz, 'contacts', 'contact-properties', 50, mf_default_contexts($values, 'categories') ?? '');

//
// contacts-contacts
//

mf_default_categories_restrict($values, 'relations', 'relation');

$separator = false;
$no = 60;
foreach ($values['relations'] as $relation) {
	mf_contacts_contacts_subtable($zz, 'contacts', $relation, $no);
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
