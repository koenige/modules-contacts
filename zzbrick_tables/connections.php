<?php 

/**
 * contacts module
 * Table for connections of contacts
 *
 * https://www.zugzwang.org/modules/contacts
 * Part of »Zugzwang Project«
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2005-2013, 2015-2017, 2019-2020, 2022-2024, 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Connections';
$zz['table'] = '/*_PREFIX_*/connections';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'connection_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][99] = []; // image

$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id
		, contact, identifier
	FROM /*_PREFIX_*/persons
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	ORDER BY last_name, first_name';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['search'] = 'pcontacts.contact';
$zz['fields'][2]['character_set'] = 'utf8';
$zz['fields'][2]['sql_character_set'][1] = 'utf8';
$zz['fields'][2]['add_details'] = wrap_path('contacts_persons');
$zz['fields'][2]['default'] = $_SESSION['user_id'];
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;

$zz['fields'][3]['title'] = 'Contact';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['field_name'] = 'connected_contact_id';
$zz['fields'][3]['sql'] = 'SELECT contact_id
		, contact, identifier
	FROM /*_PREFIX_*/persons
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	ORDER BY last_name, first_name';
$zz['fields'][3]['sql_character_set'][1] = 'utf8';
$zz['fields'][3]['display_field'] = 'connected_contact';
$zz['fields'][3]['search'] = 'contacts.contact';
$zz['fields'][3]['link'] = [
	'area' => 'contacts_profile[person]',
	'fields' => ['identifier']
]; 

$zz['fields'][4] = []; // connections_categories

$zz['fields'][5]['field_name'] = 'last_update';
$zz['fields'][5]['type'] = 'timestamp';
$zz['fields'][5]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/connections.*
	, pcontacts.contact
	, contacts.contact AS connected_contact
	, contacts.identifier
	FROM /*_PREFIX_*/connections
	LEFT JOIN /*_PREFIX_*/contacts pcontacts
		ON /*_PREFIX_*/connections.contact_id = pcontacts.contact_id
	LEFT JOIN /*_PREFIX_*/contacts
		ON /*_PREFIX_*/connections.connected_contact_id = contacts.contact_id
';
$zz['sqlorder'] = ' ORDER BY pcontacts.identifier DESC';

$zz['subtitle']['contact_id']['sql'] = $zz['fields'][2]['sql'];
$zz['subtitle']['contact_id']['var'] = ['contact'];
