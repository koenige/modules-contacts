<?php 

/**
 * contacts module
 * Table script: 3rd party identifiers of contacts
 *
 * https://www.zugzwang.org/modules/contacts
 * Part of »Zugzwang Project«
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2013-2014, 2017-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Identifiers of Contacts';
$zz['table'] = 'contacts_identifiers';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_identifier_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM contacts
	ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['unique_ignore'] = ['identifier'];

$zz['fields'][3]['field_name'] = 'identifier';

$zz['fields'][4]['title'] = 'Category';
$zz['fields'][4]['field_name'] = 'identifier_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM categories
	WHERE main_category_id = %d
', wrap_category_id('identifiers'));
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['key_field_name'] = 'category_id';

$zz['fields'][5]['field_name'] = 'current';
$zz['fields'][5]['type'] = 'select';
$zz['fields'][5]['enum'] = ['yes'];
$zz['fields'][5]['enum_title'] = [wrap_text('current')];

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['unique'][] = ['identifier', 'identifier_category_id'];


$zz['sql'] = 'SELECT contacts_identifiers.*
		, contact, category
	FROM contacts_identifiers
	LEFT JOIN contacts USING (contact_id)
	LEFT JOIN categories
		ON categories.category_id = contacts_identifiers.identifier_category_id
';
$zz['sqlorder'] = ' ORDER BY contact ASC, category, ISNULL(current), identifier';
