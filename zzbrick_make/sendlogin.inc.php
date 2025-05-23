<?php

/**
 * contacts module
 * send login link to contact
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2022, 2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_make_sendlogin($params, $settings) {
	if (count($params) !== 1) return false;

	$sql = 'SELECT contact_id, identification AS e_mail
			, contact
			, first_name
			, CONCAT(IFNULL(CONCAT(name_particle, " "), ""), last_name) AS last_name
			, contacts.identifier
			, IF(persons.sex = "female", 1, NULL) AS female
			, IF(persons.sex = "male", 1, NULL) AS male
			, IF(persons.sex = "diverse", 1, NULL) AS diverse
			, IF(ISNULL(persons.sex), 1, NULL) AS sex_unknown
		FROM persons
		LEFT JOIN contacts USING (contact_id)
		LEFT JOIN contactdetails USING (contact_id)
		WHERE contacts.%s
		AND contactdetails.provider_category_id = /*_ID categories provider/e-mail _*/
		ORDER BY contactdetails.contactdetail_id
		LIMIT 1';
	$sql = sprintf($sql
		, is_numeric($params[0])
			? sprintf('contact_id = %d', $params[0])
			: sprintf('identifier = "%s"', wrap_db_escape($params[0]))
	);
	$contact = wrap_db_fetch($sql);
	if (!$contact)
		wrap_error(wrap_text(
			'Contact %s was not found.', ['values' => [wrap_html_escape($params[0])]]
		), E_USER_ERROR);

	if (!empty($_SESSION['logged_in'])) {
		$sql = 'SELECT identification AS e_mail, identifier, contact
			FROM contacts
			LEFT JOIN contactdetails USING (contact_id)
			WHERE contact_id = %d
			AND contactdetails.provider_category_id = /*_ID categories provider/e-mail _*/
			ORDER BY contactdetails.contactdetail_id
			LIMIT 1';
		$sql = sprintf($sql, $_SESSION['user_id']);
		$sender = wrap_db_fetch($sql);
		$contact['sender'] = $sender['contact'];
		$contact['sender_link'] = $sender['identifier'];
		$mail['headers']['From']['name'] = $sender['contact'];
		$mail['headers']['From']['e_mail'] = $sender['e_mail'];
	}
	$mail['to']['name'] = $contact['contact'];
	$mail['to']['e_mail'] = $contact['e_mail'];
	$contact['addlogin_hash'] = wrap_set_hash($contact['contact_id'].'-'.$contact['identifier'], 'addlogin_key');
	$mail['message'] = wrap_template('addlogin-mail', $contact);
	$success = wrap_mail($mail);
	if (!$success) {
		wrap_error(wrap_text(
			'The login link could not be emailed to contact %s (ID %d).',
			['values' => [$contact['contact_id'], $contact['contact']]]
		), E_USER_ERROR);
	}
	if (array_key_exists('redirect', $settings) AND !$settings['redirect']) return;
	return wrap_redirect_change();
}
