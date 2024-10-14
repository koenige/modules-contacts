<?php 

/**
 * contacts module
 * let user create a new login
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * show a form to letting an existing user create a new login
 *
 * @param array $params
 */
function mod_contacts_make_addlogin($params) {
	$data = [];
	$page['query_strings'][] = 'add';
	$page['query_strings'][] = 'request'; // @deprecated
	if (count($params) < 2) {
		$data['invalid_request'] = true;
		$page['text'] = wrap_template('addlogin', $data);
		return $page;
	}
	while (count($params) > 2) {
		// username might contain - as character
		$first = array_shift($params);
		$params[0] = $first.'-'.$params[0];
	}

	// check if login exists
	$sql = sprintf(wrap_sql_query('auth_login_exists'), $params[0]);
	$existing = wrap_db_fetch($sql, '', 'single value');
	if ($existing) {
		wrap_error(sprintf('Could not create login, login for user already exists: %s', $params[0]), E_USER_NOTICE);
		$data['missing_user_or_login_exists'] = true;
		$page['text'] = wrap_template('addlogin', $data);
		return $page;
	}
	
	// check if username exists
	$sql = sprintf(wrap_sql_query('auth_username_exists'), $params[0]);
	$user = wrap_db_fetch($sql);
	if (!$user) {
		wrap_error(sprintf('Could not create login, user does not exist: %s', $params[0]), E_USER_NOTICE);
		$data['missing_user_or_login_exists'] = true;
		$page['text'] = wrap_template('addlogin', $data);
		return $page;
	}

	// check if hash is correct
	// set addlogin_key 
	// set addlogin_key_validity_in_minutes
	$access = wrap_check_hash($user['user_id'].'-'.$user['username'], $params[1], '', 'addlogin_key');
	if (!$access) {
		wrap_error(sprintf('Could not create login, hash is invalid: %s %s', $params[0], $params[1]), E_USER_NOTICE);
		$data['invalid_request'] = true;
		$page['text'] = wrap_template('addlogin', $data);
		return $page;
	}

	// everything is correct, let user add a login
	wrap_setting('log_username', $user['username']);
	$page = brick_format('%%% forms addlogin '.$user['user_id'].' %%%');
	return $page;
}
