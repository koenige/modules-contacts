<?php

/**
 * contacts module
 * form script: create own login
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


// 0: contact_id
if (count($brick['vars']) !== 1) wrap_quit(403);
if (!is_numeric($brick['vars'][0])) wrap_quit(403);

$zz = zzform_include('logins');
// remarks: logins script must work with contact_id

$zz['title'] = 'Add a new login';
$zz['explanation'] = wrap_template('addlogin', ['explanation' => 1]);
$zz['access'] = 'add_only';

$zz['where']['contact_id'] = $brick['vars'][0];

// 11 = contact
$zz['fields'][11]['hide_in_form'] = true;

// 12 = password reminder
unset($zz['fields'][12]);

// 2 = username
if (wrap_setting('login_with_email')) {
	$sql = sprintf('SELECT identification
		FROM contactdetails
		WHERE contact_id = %d
		AND provider_category_id = /*_ID categories provider/e-mail _*/
		LIMIT 1', $brick['vars'][0]);
	$zz['sql'] = wrap_edit_sql($zz['sql'], 'SELECT', sprintf('(%s) AS username', $sql));
} else {
	$sql = sprintf('SELECT identifier
		FROM contacts
		WHERE contact_id = %d', $brick['vars'][0]);
}
$zz['fields'][2]['type'] = 'display';
$zz['fields'][2]['display_value'] = wrap_db_fetch($sql, '', 'single value');

// 6 = login_rights
if (!empty($zz['fields'][6])) {
	$zz['fields'][6]['type'] = 'hidden';
	$zz['fields'][6]['value'] = wrap_setting('addlogin_rights');
	$zz['fields'][6]['hide_in_form'] = true;
}

// 3 = password
$zz['fields'][3]['type'] = 'password_change';
unset($zz['fields'][3]['function']); // no password function, e. g. random pwd
$zz['fields'][3]['dont_require_old_password'] = true;

// 9 = password_change
$zz['fields'][9]['type'] = 'hidden';
$zz['fields'][9]['hide_in_form'] = true;
$zz['fields'][9]['value'] = 'no';

// 13 = generate random password
unset($zz['fields'][13]);

// 20 = login_category_id
if (!empty($zz['fields'][20]))
	$zz['fields'][20]['hide_in_form'] = true;

// keep URL query from confirmation script
$zz['setting']['zzform_url_keep_query'] = true;
if (!empty($brick['local_settings']['query_strings']))
	$zz['page']['query_strings'] = $brick['local_settings']['query_strings'];

$zz['hooks']['after_insert'] = 'mf_contacts_addlogin_password';
$zz['record']['no_timeframe'] = true;

wrap_text_set('Add a record', ' ');
wrap_text_set('Add record', 'Save password');

$zz['record']['redirect']['successful_insert'] = $brick['local_settings']['link'] ?? wrap_domain_path('login_entry');


/**
 * log user into database after password is set
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_addlogin_password($ops) {
	$login_id = false;
	foreach ($ops['return'] as $index => $table) {
		if ($table['table'] !== 'logins') continue;
		$login_id = $ops['record_new'][$index]['login_id'];
		$username = $ops['record_old'][$index]['username']; // old, is set via display in logins
	}
	if (!$login_id) return [];

	mf_contacts_login_user($username);
	return [];
}
