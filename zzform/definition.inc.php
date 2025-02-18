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
	// table
	$zz['fields'][$no] = zzform_include($table.'-contacts');
	$zz['fields'][$no]['title'] = $def['category'];
	$zz['fields'][$no]['table_name'] = str_replace('/*_PREFIX_*/', '', $zz['fields'][$no]['table']).'_'.$def['category_id'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = $def['parameters']['min_records'] ?? 1;
	$zz['fields'][$no]['max_records'] = $def['parameters']['max_records'] ?? 20;
	$zz['fields'][$no]['sql'] .= sprintf(' WHERE role_category_id = %d
		ORDER BY sequence, /*_PREFIX_*/contacts.identifier', $def['category_id']);
	$zz['fields'][$no]['form_display'] = 'lines';

	// fields
	$foreign_key = $zz['fields'][$no]['fields'][2]['field_name'];
	foreach ($zz['fields'][$no]['fields'] as $sub_no => $sub_field) {
		if ($sub_no === 1) continue; // id

		$identifier = zzform_field_identifier($sub_field);
		if (!$identifier) continue;
		switch ($identifier) {
			case $foreign_key:
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'foreign_key';
				break;

			case 'contact_id':
				$zz['fields'][$no]['fields'][$sub_no]['show_title'] = false;
				$zz['fields'][$no]['fields'][$sub_no]['sql'] = sprintf('SELECT contact_id, contact
					FROM contacts
					LEFT JOIN categories
						ON contacts.contact_category_id = categories.category_id
					WHERE categories.parameters LIKE "%%&%s_%s=1%%"
					ORDER BY identifier', $table, $def['path']);
				$zz['fields'][$no]['fields'][$sub_no]['add_details'] = $def['parameters']['add_details'] ?? false;
				$zz['fields'][$no]['fields'][$sub_no]['select_dont_force_single_value'] = true;
				break;

			case 'role_category_id':
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'hidden';
				$zz['fields'][$no]['fields'][$sub_no]['value'] = $def['category_id'];
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = true;
				break;

			case 'sequence':
				$zz['fields'][$no]['fields'][$sub_no]['type'] = 'sequence';
				break;

			case 'role':
				if (empty($def['parameters']['role'])) break;
				$zz['fields'][$no]['fields'][$sub_no]['hide_in_form'] = false;
				$zz['fields'][$no]['fields'][$sub_no]['placeholder'] = true;
				break;
		}
	}
	
	// list view
	if (!empty($zz['fields'][$no]['subselect'])) {
		$zz['fields'][$no]['unless']['export_mode']['subselect']['prefix'] = '<br><em>'.wrap_text($def['category']).'</em>: ';
		$zz['fields'][$no]['unless']['export_mode']['subselect']['suffix'] = '';
		if (empty($def['last_category']))
			$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
		$zz['fields'][$no]['subselect']['sql'] = wrap_edit_sql(
			$zz['fields'][$no]['subselect']['sql'], 'WHERE',
			sprintf('role_category_id = %d', $def['category_id'])	
		);
	}
	$zz['fields'][$no]['class'] = 'hidden480';
	$zz['fields'][$no]['hide_in_list'] = $def['parameters']['hide_in_list'] ?? false;
}
