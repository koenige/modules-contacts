<?php 

/**
 * contacts module
 * Categories per contact
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2009, 2016-2017, 2020, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Categories per Contact';
$zz['table'] = 'contacts_categories';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_category_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM contacts
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['search'] = 'contacts.contact';

$zz['fields'][6]['title_tab'] = 'Seq.';
$zz['fields'][6]['field_name'] = 'sequence';
$zz['fields'][6]['type'] = 'number';
$zz['fields'][6]['hide_in_list_if_empty'] = true;

$zz['fields'][3]['field_name'] = 'category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories
	ORDER BY sequence';
$zz['fields'][3]['show_hierarchy'] = 'main_category_id';
$zz['fields'][3]['id_field_name'] = 'category_id';
$zz['fields'][3]['show_hierarchy_subtree'] = wrap_category_id('contact-properties');
$zz['fields'][3]['display_field'] = 'category';
$zz['fields'][3]['hide_in_list'] = true;

$zz['fields'][4]['field_name'] = 'property';

$zz['fields'][5]['field_name'] = 'type_category_id';
$zz['fields'][5]['type'] = 'hidden';
$zz['fields'][5]['type_detail'] = 'select';
$zz['fields'][5]['value'] = wrap_category_id('contact-properties');
$zz['fields'][5]['hide_in_form'] = true;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['exclude_from_search'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT contacts_categories.*
		, contacts.contact, category
	FROM contacts_categories
	LEFT JOIN contacts USING (contact_id)
	LEFT JOIN categories USING (category_id)
';
$zz['sqlorder'] = ' ORDER BY identifier, contact, path, contacts_categories.sequence';

$zz['subselect']['sql'] = 'SELECT contact_id, contact_category_id
		, category_id, category, property
	FROM contacts_categories
	LEFT JOIN categories USING (category_id)
';
$zz['subselect']['sql_translate'] = ['category_id' => 'categories', 'contact_category_id' => 'contact_categories'];
$zz['subselect']['sql_ignore'] = ['contact_category_id', 'category_id'];
$zz['subselect']['concat_fields'] = ' ';
$zz['subselect']['concat_rows'] = ', ';
$zz['unless']['export_mode']['subselect']['prefix'] = '<br><em>'.wrap_text('Category').': ';
$zz['unless']['export_mode']['subselect']['suffix'] = '</em>';
$zz['export_no_html'] = true;
