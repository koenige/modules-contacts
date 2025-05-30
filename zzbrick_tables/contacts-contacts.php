<?php 

/**
 * contacts module
 * Table relating contacts to contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2019-2022, 2024-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Linked Contacts';
$zz['table'] = '/*_PREFIX_*/contacts_contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'cc_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][6]['title_tab'] = 'Seq.';
$zz['fields'][6]['field_name'] = 'sequence';
$zz['fields'][6]['type'] = 'number';
$zz['fields'][6]['default'] = 1;
$zz['fields'][6]['auto_value'] = 'increment';
$zz['fields'][6]['def_val_ignore'] = true;
$zz['fields'][6]['placeholder'] = 'No.';
$zz['fields'][6]['for_action_ignore'] = true;

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id
		, IF(contact_category_id != /*_ID categories contact/person _*/
			, CONCAT(postcode, " ", place), ""
		) AS place
		, contact, identifier
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/addresses USING (contact_id)
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['search'] = 'contacts.contact';
$zz['fields'][2]['placeholder'] = true;
$zz['fields'][2]['select_dont_force_single_value'] = true;
$zz['fields'][2]['select_empty_no_add'] = true;
$zz['fields'][2]['link'] = [
	'area' => 'contacts_profile[*]',
	'fields' => ['identifier']
];
$zz['fields'][2]['unique_ignore'] = ['identifier'];

$zz['fields'][4]['title'] = 'Relation';
$zz['fields'][4]['field_name'] = 'relation_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['type_detail'] = 'select';
$zz['fields'][4]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = /*_ID categories relation _*/';
$zz['fields'][4]['sql_translate'] = ['category_id' => 'categories'];
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['character_set'] = 'utf8';
$zz['fields'][4]['for_action_ignore'] = true;
$zz['fields'][4]['if']['where']['hide_in_form'] = true;
$zz['fields'][4]['if']['where']['hide_in_list'] = true;

$zz['fields'][3]['title'] = 'Main Contact';
$zz['fields'][3]['field_name'] = 'main_contact_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['type_detail'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][3]['display_field'] = 'main_contact';
$zz['fields'][3]['search'] = 'main_contacts.contact';
$zz['fields'][3]['character_set'] = 'utf8';
$zz['fields'][3]['select_dont_force_single_value'] = true;
$zz['fields'][3]['select_empty_no_add'] = true;
$zz['fields'][3]['not_identical_with'] = 'contact_id';
$zz['fields'][3]['if']['where']['hide_in_form'] = true;
$zz['fields'][3]['if']['where']['hide_in_list'] = true;
$zz['fields'][3]['link'] = [
	'area' => 'contacts_profile[*]',
	'fields' => ['main_identifier']
];
$zz['fields'][3]['unique_ignore'] = ['identifier'];

$zz['fields'][11]['field_name'] = 'role';
$zz['fields'][11]['type'] = 'text';
$zz['fields'][11]['size'] = 18;
$zz['fields'][11]['hide_in_list_if_empty'] = true;

$zz['fields'][9]['field_name'] = 'remarks';
$zz['fields'][9]['type'] = 'memo';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['rows'] = 2;

$zz['fields'][10]['title_tab'] = 'WWW?';
$zz['fields'][10]['field_name'] = 'published';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = ['yes', 'no'];
$zz['fields'][10]['default'] = 'no';
$zz['fields'][10]['for_action_ignore'] = true;
$zz['fields'][10]['hide_in_list'] = true;
$zz['fields'][10]['hide_in_form'] = true;
$zz['fields'][10]['if']['revise']['hide_in_form'] = false;
if (wrap_access('contacts_published')) {
	$zz['fields'][10]['hide_in_form'] = false;
}

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT /*_PREFIX_*/contacts_contacts.*
		, main_contacts.contact AS main_contact, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/categories.category
		, /*_PREFIX_*/contacts.identifier
		, main_contacts.identifier AS main_identifier
	FROM /*_PREFIX_*/contacts_contacts
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/contacts main_contacts
		ON /*_PREFIX_*/contacts_contacts.main_contact_id = main_contacts.contact_id
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/contacts_contacts.relation_category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.contact, sequence, main_contacts.contact';


$zz['subselect']['sql'] = 'SELECT contacts_contacts.contact_id, contact
	FROM contacts_contacts
	LEFT JOIN contacts
		ON contacts_contacts.main_contact_id = contacts.contact_id
	WHERE contacts_contacts.published = "yes"
	ORDER BY contact';

$zz['list']['batch_delete'] = true;
