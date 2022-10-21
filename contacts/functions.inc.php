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

/**
 * get a list of categories to include subtable depending on these categories
 *
 * @param array $values
 * @param string $type
 * @param string $category_path
 * @return array
 */
function mf_contacts_restrict_categories($values, $type, $category_path) {
	if (isset($values[$type])) return $values[$type];
	if (isset($values[$type.'_restrict_to']))
		$restrict_to = 'AND parameters LIKE "%%&'.$values[$type.'_restrict_to'].'=1%%"';
	else
		$restrict_to = '';
	$sql = 'SELECT category_id, category, parameters
		FROM categories
		WHERE main_category_id = %d
		%s
		ORDER BY sequence, path';
	$sql = sprintf($sql
		, wrap_category_id($category_path)
		, $restrict_to
	);
	return wrap_db_fetch($sql, 'category_id');
}
