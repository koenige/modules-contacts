<?php 

/**
 * contacts module
 * let user login via a link
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * let user login via a link
 *
 * @param array $params
 */
function mod_contacts_make_linklogin($params) {
	$data = [];
	$page['query_strings'] = ['link'];
	if (count($params) < 2) {
		$data['invalid_request'] = true;
		$page['text'] = wrap_template('linklogin', $data);
		return $page;
	}
	while (count($params) > 2) {
		// username might contain - as character
		$first = array_shift($params);
		$params[0] = $first.'-'.$params[0];
	}

	// check if username exists
	$sql = sprintf(wrap_sql('username_exists'), $params[0]);
	$user = wrap_db_fetch($sql);
	if (!$user) {
		wrap_error(sprintf('Unable to login, user does not exist: %s', $params[0]), E_USER_NOTICE);
		$data['invalid_request'] = true;
		$page['text'] = wrap_template('linklogin', $data);
		return $page;
	}
	
	// check if hash is correct
	// set contacts_linklogin_key!
	$access = wrap_check_hash($user['user_id'].'-'.$user['username'], $params[1], '', 'contacts_linklogin_key');
	if (!$access) {
		$data['invalid_request'] = true;
		wrap_error(sprintf('Unable to login, token is invalid: %s %s', $params[0], $params[1]), E_USER_NOTICE);
		$page['text'] = wrap_template('linklogin', $data);
		return $page;
	}
	$sql = sprintf(wrap_sql('login_exists'), $params[0]);
	$existing = wrap_db_fetch($sql, '', 'single value');
	if ($existing) {
		// if user has a login: do a full login
		mf_contacts_login_user($params[0]);
	} else {
		// otherwise: just save some values in session
		wrap_session_start();
		$_SESSION = $user;
		session_write_close();
	}
	wrap_redirect_change(wrap_domain_path('login_entry'));
	return $page;
}
