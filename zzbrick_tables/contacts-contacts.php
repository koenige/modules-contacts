<?php 

/**
 * Zugzwang Project
 * Table relating contacts to contacts
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2019-2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz_sub['title'] = 'Relations of Contacts';
$zz_sub['table'] = '/*_PREFIX_*/contacts_contacts';

$zz_sub['fields'][1]['title'] = 'ID';
$zz_sub['fields'][1]['field_name'] = 'cc_id';
$zz_sub['fields'][1]['type'] = 'id';

$zz_sub['fields'][6]['title_tab'] = 'Seq.';
$zz_sub['fields'][6]['field_name'] = 'sequence';
$zz_sub['fields'][6]['type'] = 'number';
$zz_sub['fields'][6]['default'] = 1;
$zz_sub['fields'][6]['auto_value'] = 'increment';
$zz_sub['fields'][6]['def_val_ignore'] = true;

$zz_sub['fields'][2]['field_name'] = 'contact_id';
$zz_sub['fields'][2]['type'] = 'select';
$zz_sub['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz_sub['fields'][2]['display_field'] = 'contact';
$zz_sub['fields'][2]['search'] = 'contacts.contact';

$zz_sub['fields'][4]['title'] = 'Relation';
$zz_sub['fields'][4]['field_name'] = 'relation_category_id';
$zz_sub['fields'][4]['key_field_name'] = 'category_id';
$zz_sub['fields'][4]['type'] = 'select';
$zz_sub['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('relation')
);
$zz_sub['fields'][4]['sql_translate'] = ['category_id' => 'categories'];
$zz_sub['fields'][4]['display_field'] = 'category';
$zz_sub['fields'][4]['character_set'] = 'utf8';

$zz_sub['fields'][3]['title'] = 'Main Contact';
$zz_sub['fields'][3]['field_name'] = 'main_contact_id';
$zz_sub['fields'][3]['key_field_name'] = 'contact_id';
$zz_sub['fields'][3]['type'] = 'select';
$zz_sub['fields'][3]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz_sub['fields'][3]['display_field'] = 'main_contact';
$zz_sub['fields'][3]['search'] = 'main_contacts.contact';
$zz_sub['fields'][3]['character_set'] = 'utf8';

$zz_sub['fields'][9]['field_name'] = 'remarks';
$zz_sub['fields'][9]['type'] = 'memo';
$zz_sub['fields'][9]['hide_in_list'] = true;
$zz_sub['fields'][9]['rows'] = 2;

$zz_sub['fields'][10]['title_tab'] = 'WWW?';
$zz_sub['fields'][10]['field_name'] = 'published';
$zz_sub['fields'][10]['type'] = 'select';
$zz_sub['fields'][10]['enum'] = ['yes', 'no'];
$zz_sub['fields'][10]['default'] = 'no';
$zz_sub['fields'][10]['def_val_ignore'] = true;

$zz_sub['fields'][99]['field_name'] = 'last_update';
$zz_sub['fields'][99]['type'] = 'timestamp';
$zz_sub['fields'][99]['hide_in_list'] = true;


$zz_sub['sql'] = 'SELECT /*_PREFIX_*/contacts_contacts.*
		, main_contacts.contact AS main_contact, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/contacts_contacts
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/contacts main_contacts
		ON /*_PREFIX_*/contacts_contacts.main_contact_id = main_contacts.contact_id
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/contacts_contacts.relation_category_id
';
$zz_sub['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.contact, sequence, main_contacts.contact';
