<?php 

/**
 * Zugzwang Project
 * Table with contacts
 *
 * http://www.zugzwang.org/modules/newsletters
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contacts';
$zz['table'] = 'contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'contact';

$zz['fields'][3]['field_name'] = 'identifier';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = array('contact');
$zz['fields'][3]['conf_identifier']['exists'] = '-';

$zz['fields'][4]['field_name'] = 'contact_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM categories
	WHERE main_category_id = %d',
	$zz_setting['category']['contact']
);

require __DIR__.'/addresses.php';
$zz['fields'][5] = $zz_sub;
$zz['fields'][5]['title'] = 'Address';
$zz['fields'][5]['type'] = 'subtable';
$zz['fields'][5]['min_records'] = 0;
$zz['fields'][5]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][5]['sql'] .= $zz['fields'][5]['sqlorder'];

require __DIR__.'/contacts-details.php';
$zz['fields'][6] = $zz_sub;
$zz['fields'][6]['title'] = 'Details';
$zz['fields'][6]['type'] = 'subtable';
$zz['fields'][6]['min_records'] = 0;
$zz['fields'][6]['fields'][2]['type'] = 'foreign_key';
$zz['fields'][6]['form_display'] = 'set';
$zz['fields'][6]['sql'] .= $zz['fields'][6]['sqlorder'];

$zz['fields'][7] = false; // contacts_verifications

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['sql'] = 'SELECT contacts.*
	FROM contacts
';
$zz['sqlorder'] = ' ORDER BY identifier';

$zz_conf['export'] = 'CSV Excel';
