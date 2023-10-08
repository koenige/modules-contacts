<?php 

/**
 * contacts module
 * common functions, only if module is active
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * log user into database
 *
 * @param string $username
 * @return bool
 */
function mf_contacts_login_user($username) {
	// get user data
	$sql = sprintf(wrap_sql_login(), $username);
	$data = wrap_db_fetch($sql);
	if (!$data) return false;

	// register user data
	wrap_session_start();
	$_SESSION['logged_in'] = true;
	wrap_register($data['user_id'], $data);
	session_write_close();
	return true;
}
