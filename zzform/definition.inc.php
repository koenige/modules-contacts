<?php 

/**
 * contacts module
 * definition helper functions for forms with zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * create addresses-subtable from categories
 *
 * @param array $zz
 * @param array $def definition of table
 * @param int $no
 */
function mf_contacts_addresses_subtable(&$zz, $def, $no) {
	$subtable_params = [
		'title_desc', 'min_records', 'min_records_required', 'max_records', 'title_button'
		, 'explanation'
	];

	$zz['fields'][$no] = zzform_include('addresses');
	$zz['fields'][$no]['table_name'] = 'address_'.$def['category_id'];
	$zz['fields'][$no]['title'] = $def['category'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = 0;
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	foreach ($subtable_params as $s_param) {
		if (empty($def['parameters'][$s_param])) continue;
		$zz['fields'][$no][$s_param] = $def['parameters'][$s_param];
	}
	if ($def['category_id']) {
		// address_catgory_id
		$zz['fields'][$no]['fields'][9]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][9]['value'] = $def['category_id'];
		$zz['fields'][$no]['fields'][9]['def_val_ignore'] = true;
		$zz['fields'][$no]['fields'][9]['hide_in_form'] = true;

		$zz['fields'][$no]['sql'] .= sprintf(' WHERE address_category_id = %d', $def['category_id']);
	}
	if (empty($def['parameters']['fields']))
		$def['parameters']['fields'] = [];
	foreach ($def['parameters']['fields'] as $field_name => $field_def) {
		if ($field_name === 'country_id' AND !empty($field_def['default']))
			$field_def['default'] = wrap_id('countries', $field_def['default']);
		foreach ($zz['fields'][$no]['fields'] as $sub_no => $sub_field)	{
			if (empty($sub_field['field_name'])) continue;
			if ($sub_field['field_name'] !== $field_name) continue;
			$zz['fields'][$no]['fields'][$sub_no] = array_merge($sub_field, $field_def);
		}
	}
	// @todo use category for columns
	$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
}

/**
 * create contactdetails-subtable from categories
 *
 * @param array $zz
 * @param array $def definition of table
 * @param int $no
 */
function mf_contacts_contactdetails_subtable(&$zz, $def, $no) {
	if (!empty($def['categories'])) {
		if (!empty($def['parameters']['category'])) {
			$def['category'] = $def['parameters']['category'];
			$def['category'] = wrap_text($def['category']);
		} elseif (count($def['categories']) === 1) {
			$def['category'] = reset($def['categories']);
			$def['category'] = $def['category']['category'];
		} else {
			$def['category'] = $def['parameters']['type'];
			$def['category'] = $def['category'] === 'url' ? strtoupper($def['category']) : ucfirst($def['category']);
			$def['category'] = wrap_text($def['category']);
		}
	}

	// table
	$zz['fields'][$no] = zzform_include('contactdetails');
	$zz['fields'][$no]['title_tab'] = 'Contact Details';
	$zz['fields'][$no]['class'] = 'contactdetails';
	$zz['fields'][$no]['table_name'] = 'contactdetails_'.$def['category_id'];
	$zz['fields'][$no]['title'] = $def['parameters']['title'] ?? $def['category'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = $def['parameters']['min_records'] ?? 1;
	$zz['fields'][$no]['max_records'] = $def['parameters']['max_records']
		?? (!empty($def['categories']) ? count($def['categories']) : 1);
	$zz['fields'][$no]['explanation'] = $def['parameters']['explanation'] ?? '';

	// fields
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	if (!empty($def['parameters']['type'])
		AND in_array($def['parameters']['type'], ['mail', 'url', 'phone', 'username'])) {
		$zz['fields'][$no]['fields'][3]['type'] = $def['parameters']['type'];
	}
	$parameters_to_fields = [
		'explanation', 'parse_url', 'url', 'dont_check_username_online',
		'validate', 'title', 'suffix'
	];
	foreach ($parameters_to_fields as $parameter_to_field) {
		if (empty($def['parameters'][$parameter_to_field])) continue;
		$zz['fields'][$no]['fields'][3][$parameter_to_field] = $def['parameters'][$parameter_to_field];
	}
	if (empty($def['categories']))
		$def['categories'][$def['category_id']] = $def;
	$zz['fields'][$no]['sql'] .= sprintf(
		' WHERE /*_PREFIX_*/contactdetails.provider_category_id IN (%s)'
		, implode(',', array_keys($def['categories']))
	);
	if (count($def['categories']) === 1) {
		$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
		$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
		$zz['fields'][$no]['fields'][4]['value'] = $def['category_id'];
	} else {
		$zz['fields'][$no]['fields'][4]['sql'] .= sprintf(
			' AND category_id IN (%s)'
			, implode(',', array_keys($def['categories']))
		);
		$zz['fields'][$no]['fields'][4]['default'] = key($def['categories']);
	}
	$zz['fields'][$no]['fields'][4]['def_val_ignore'] = true;
	$zz['fields'][$no]['fields'][4]['for_action_ignore'] = true;
	$zz['fields'][$no]['fields'][5]['hide_in_form']
		= isset($def['parameters']['label']) ? !$def['parameters']['label']
		: (wrap_setting('contacts_details_with_label') ? false : true);
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['subselect']['sql'] = wrap_edit_sql(
		$zz['fields'][$no]['subselect']['sql'], 'WHERE',
		sprintf('/*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($def['categories'])))
	);
	$zz['fields'][$no]['if']['export_mode']['subselect']['sql'] = wrap_edit_sql(
		$zz['fields'][$no]['if']['export_mode']['subselect']['sql'], 'WHERE',
		sprintf('/*_PREFIX_*/contactdetails.provider_category_id IN (%s)', implode(',', array_keys($def['categories'])))
	);
	$zz['fields'][$no]['export_no_html'] = true;
	if (!empty($def['field_sequence']))
		$zz['fields'][$no]['field_sequence'] = $def['field_sequence'];
}

/**
 * create _contacts-subtable from categories
 *
 * @param array $zz
 * @param string $table prefix of table name *_contacts
 * @param array $def definition of table
 *		output of mf_default_categories_restrict()
 *		int category_id
 *		string category
 *		string path
 *		array parameters
 * @param int $no
 */
function mf_contacts_contacts_subtable(&$zz, $table, $def, $no) {
	static $separator = false;

	// table
	$zz['fields'][$no] = zzform_include($table.'-contacts');
	$foreign_key_field = $zz['fields'][$no]['fields'][2]['field_name'];
	$contact_field = 'contact_id';

	switch ($table) {
	case 'contacts':
		$category_field = 'relation_category_id';
		$hide_in_list = true;

		if (!$separator) {
			$zz['fields'][$no]['separator_before'] = true;
			$separator = true;
		}
		if (!empty($def['association']) OR !empty($def['parameters']['reverse_relation'])) {
			$foreign_key_field = 'main_contact_id';
			$zz['fields'][$no]['sql'] = $zz['fields'][$no]['sql_association'];
		} else {
			$contact_field = 'main_contact_id';
		}
		break;

	default:
		$category_field = 'role_category_id';
		$hide_in_list = false;
		break;
	}

	$zz['fields'][$no]['title'] = $def['category'];
	$zz['fields'][$no]['table_name']
		= str_replace('/*_PREFIX_*/', '', $zz['fields'][$no]['table']).'_'.$def['category_id'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = $def['parameters']['min_records'] ?? 1;
	$zz['fields'][$no]['min_records_required'] = $def['parameters']['min_records_required'] ?? 0;
	$zz['fields'][$no]['max_records'] = $def['parameters']['max_records'] ?? 20;
	$zz['fields'][$no]['sql'] = wrap_edit_sql($zz['fields'][$no]['sql'],
		'WHERE', sprintf('%s = %d', $category_field, $def['category_id'])
	);
	$zz['fields'][$no]['sql'] = wrap_edit_sql($zz['fields'][$no]['sql'],
		'ORDER BY', 'sequence, /*_PREFIX_*/contacts.identifier'
	);
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['show_title'] = $def['parameters']['show_title'] ?? true;
	$zz['fields'][$no]['explanation'] = $def['parameters']['explanation'] ?? '';
	$zz['fields'][$no]['integrate_in_next'] = $def['parameters']['integrate_in_next'] ?? false;

	// fields
	foreach ($zz['fields'][$no]['fields'] as $sub_no => $sub_field) {
		if ($sub_no === 1) continue; // id

		$identifier = zzform_field_identifier($sub_field);
		if (!$identifier) continue;
		switch ($identifier) {
			case $foreign_key_field:
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'foreign_key';
				break;

			case $contact_field:
				$zz['fields'][$no]['fields'][$sub_no]['show_title'] = false;
				$zz['fields'][$no]['fields'][$sub_no]['sql'] = sprintf(
					'SELECT contact_id, contact
					FROM contacts
					LEFT JOIN categories
						ON contacts.contact_category_id = categories.category_id
					WHERE categories.parameters LIKE "%%&%s_%s=1%%"
					ORDER BY identifier', $table, $def['path']);
				$zz['fields'][$no]['fields'][$sub_no]['add_details']
					= $def['parameters']['add_details'] ?? false;
				$zz['fields'][$no]['fields'][$sub_no]['select_dont_force_single_value'] = true;
				$zz['fields'][$no]['fields'][$sub_no]['placeholder']
					= $def['parameters']['placeholder'] ?? $def['category'];

				// restrict contacts to category
				if (!empty($def['parameters']['main_contact']['category'])) {
					$categories = $def['parameters']['main_contact']['category'];
					if (!is_array($categories)) $categories = [$categories];
					foreach ($categories as $index => $category) {
						$categories[$index] = wrap_category_id('contact/'.$category);
					}
					$zz['fields'][$no]['fields'][$sub_no]['sql'] = wrap_edit_sql(
						$zz['fields'][$no]['fields'][$sub_no]['sql'], 'WHERE',
						sprintf('contact_category_id IN (%s)', implode(',', $categories))
					);
				}

				// add new contacts
				if (!empty($def['parameters']['main_contact']['add_details'])) {
					$zz['fields'][$no]['fields'][$sub_no]['add_details']
						= wrap_setting('base').$def['parameters']['main_contact']['add_details'];
					// no recursive linking on forms that link to add_details on themselves
					// this is not possible to edit
					if ($zz['fields'][$no]['fields'][$sub_no]['add_details']
						=== parse_url(wrap_setting('request_uri'), PHP_URL_PATH))
						$zz['fields'][$no]['hide_in_form'] = true;
				}
				break;

			case $category_field:
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'hidden';
				$zz['fields'][$no]['fields'][$sub_no]['type_detail'] = 'select';
				$zz['fields'][$no]['fields'][$sub_no]['value'] = $def['category_id'];
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
				break;

			case 'sequence':
				$zz['fields'][$no]['fields'][$sub_no]['placeholder'] = 'No.';
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'sequence';
				if (isset($def['parameters']['sequence'])) {
					if ($def['parameters']['sequence'] === 'hidden') {
						$zz['fields'][$no]['fields'][$sub_no]['type'] = 'hidden';
						$zz['fields'][$no]['fields'][$sub_no]['class'] = 'hidden';
					} elseif ($def['parameters']['sequence'] === 'sequence') {
						$zz['fields'][$no]['fields'][$sub_no]['type'] = 'sequence';
					} elseif (!$def['parameters']['sequence']) {
						$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
					}
				}
				break;

			case 'role':
				if (empty($def['parameters']['role'])) {
					$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
					$zz['fields'][$no]['fields'][$sub_no]['hide_in_list'] = true;
					break;
				}
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = false;
				$zz['fields'][$no]['fields'][$sub_no]['placeholder'] = true;
				break;

			case 'published':
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'hidden';
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
				break;

			case 'remarks':
				$zz['fields'][$no]['fields'][$sub_no]['placeholder'] = 'Remarks';
				if (!empty($def['parameters']['remarks'])) break;
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_list'] = true;
				break;
		}
	}
	
	// list view
	if (!empty($zz['fields'][$no]['subselect'])) {
		$zz['fields'][$no]['unless']['export_mode']['subselect']['prefix']
			= '<br><em>'.wrap_text($def['category']).'</em>: ';
		$zz['fields'][$no]['unless']['export_mode']['subselect']['suffix'] = '';
		if (empty($def['last_category']))
			$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
		$zz['fields'][$no]['subselect']['sql'] = wrap_edit_sql(
			$zz['fields'][$no]['subselect']['sql'], 'WHERE',
			sprintf('role_category_id = %d', $def['category_id'])	
		);
	}
	$zz['fields'][$no]['class'] = 'hidden480';
	$zz['fields'][$no]['hide_in_list'] = $def['parameters']['hide_in_list'] ?? $hide_in_list;
}
