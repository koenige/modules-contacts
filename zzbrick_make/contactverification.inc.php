<?php 

/**
 * contacts module
 * Confirm contact registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2016, 2018-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_make_contactverification($params, $settings) {
	wrap_setting('cache', false);
	wrap_setting_add('extra_http_headers', 'X-Frame-Options: Deny');
	wrap_setting_add('extra_http_headers', "Content-Security-Policy: frame-ancestors 'self'");

	$form = [];
	$form['reminder'] = false;
	$form['own_e_mail'] = $settings['e_mail'] ?? wrap_setting('own_e_mail');
	$category = $settings['category'] ?? 'Registration';
	$form['category'] = wrap_text($category, !empty($settings['translation_context']) ? ['context' => $settings['translation_context']] : []);
	$form['action'] = $settings['path'] ?? parse_url(wrap_setting('request_uri'), PHP_URL_PATH);

	$possible_actions = ['confirm', 'delete'];
	$page['query_strings'] = ['code', 'action', 'confirm', 'delete'];
	$page['breadcrumbs'][]['title'] = wrap_text(sprintf('Confirm %s', $category));

	// What to do?
	if (!empty($_GET['code']) && !empty($_GET['action'])
		&& in_array($_GET['action'], $possible_actions)) {
		$form['code'] = $_GET['code'];
		$action = $_GET['action'];
	} elseif (!empty($_GET['confirm'])) {
		$form['code'] = $_GET['confirm'];
		$action = 'confirm';
	} elseif (!empty($_GET['delete'])) {
		$form['code'] = $_GET['delete'];
		$action = 'delete';
	} elseif (!empty($_GET['code'])) {
		$form['code'] = $_GET['code'];
		$form['form'] = true;
		$action = false;
		$form['reminder'] = true;
	} else {
		$form['code'] = false;
	}
	if ($form['code'] AND !preg_match('/^[A-Za-z0-9]+$/', $form['code']))
		$form['code'] = false;

	if (!$form['code']) {
		$form['form'] = true;
		$page['text'] = wrap_template('contact-verification', $form);
		return $page;
	}

	$sql = 'SELECT contact_id, verification_date
		FROM contacts_verifications
		WHERE verification_hash = "%s"';
	$sql = sprintf($sql, wrap_db_escape($form['code']));
	$data = wrap_db_fetch($sql);
	
	if (!$data) {
		$form['no_data'] = true;
		$form['form'] = true;
		$form['check_'.$action] = true;
		$page['text'] = wrap_template('contact-verification', $form);
		return $page;
	}
	
	if ($data['verification_date']) {
		if ($action === 'confirm') {
			$form['already_confirmed'] = true;
		} elseif ($action === 'delete') {
			$form['confirmed_delete'] = true;
		} else {
			$form['form'] = true;
		}
		$page['text'] = wrap_template('contact-verification', $form);
		return $page;
	}
	
	if (!$action) {
		$page['text'] = wrap_template('contact-verification', $form);
		return $page;
	}

	$sql = 'SELECT cv_id, identifier
		FROM contacts_verifications
		LEFT JOIN contacts USING (contact_id)
		WHERE contact_id = %d';
	$sql = sprintf($sql, $data['contact_id']);
	$cv = wrap_db_fetch($sql);

	wrap_setting('log_username', $cv['identifier']);
	if ($action === 'confirm') {
		$line = [
			'cv_id' => $cv['cv_id'],
			'verification_date' => date('Y-m-d H:i:s'),
			'verification_ip' => wrap_setting('remote_ip'),
			'status' => 'confirmed per link'
		];
		zzform_update('contacts-verifications', $line, E_USER_ERROR);
	} else {
		zzform_delete('contacts_verifications', $cv['cv_id'], E_USER_ERROR);
	}

	$form[$action] = true;

	$page['text'] = wrap_template('contact-verification', $form);
	return $page;
}
