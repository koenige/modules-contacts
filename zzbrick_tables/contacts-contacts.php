<?php 

/**
 * contacts module
 * Table relating contacts to contacts
 *
 * https://www.zugzwang.org/modules/contacts
 * Part of »Zugzwang Project«
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2019-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Relations of Contacts';
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
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['search'] = 'contacts.contact';
$zz['fields'][2]['placeholder'] = true;
$zz['fields'][2]['select_dont_force_single_value'] = true;
$zz['fields'][2]['select_empty_no_add'] = true;

$zz['fields'][4]['title'] = 'Relation';
$zz['fields'][4]['field_name'] = 'relation_category_id';
$zz['fields'][4]['key_field_name'] = 'category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['type_detail'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('relation')
);
$zz['fields'][4]['sql_translate'] = ['category_id' => 'categories'];
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['character_set'] = 'utf8';

$zz['fields'][3]['title'] = 'Main Contact';
$zz['fields'][3]['field_name'] = 'main_contact_id';
$zz['fields'][3]['key_field_name'] = 'contact_id';
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

$zz['fields'][11]['field_name'] = 'role';
$zz['fields'][11]['type'] = 'text';
$zz['fields'][11]['size'] = 18;
$zz['fields'][11]['hide_in_list'] = true;

$zz['fields'][9]['field_name'] = 'remarks';
$zz['fields'][9]['type'] = 'memo';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['rows'] = 2;

$zz['fields'][10]['title_tab'] = 'WWW?';
$zz['fields'][10]['field_name'] = 'published';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = ['yes', 'no'];
$zz['fields'][10]['default'] = 'no';
$zz['fields'][10]['def_val_ignore'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT /*_PREFIX_*/contacts_contacts.*
		, main_contacts.contact AS main_contact, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/contacts_contacts
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/contacts main_contacts
		ON /*_PREFIX_*/contacts_contacts.main_contact_id = main_contacts.contact_id
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/contacts_contacts.relation_category_id
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.contact, sequence, main_contacts.contact';
