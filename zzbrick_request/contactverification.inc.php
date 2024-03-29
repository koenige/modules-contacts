<?php 

/**
 * contacts module
 * Confirm contact registration
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2016, 2018-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contactverification($params, $settings) {
	wrap_setting('cache', false);
	wrap_setting_add('extra_http_headers', 'X-Frame-Options: Deny');
	wrap_setting_add('extra_http_headers', "Content-Security-Policy: frame-ancestors 'self'");

	$tpl = 'contact-verification';

	$form = [];
	$form['reminder'] = false;
	$form['own_e_mail'] = $settings['e_mail'] ?? wrap_setting('own_e_mail');
	$form['category'] = $settings['category'] ?? 'Registration';
	$form['action'] = $settings['path'] ?? parse_url(wrap_setting('request_uri'), PHP_URL_PATH);

	$possible_actions = ['confirm', 'delete'];
	$page['query_strings'] = ['code', 'action', 'confirm', 'delete'];
	$page['breadcrumbs'][]['title'] = wrap_text(sprintf('Confirm %s', $form['category']));

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
		$page['text'] = wrap_template($tpl, $form);
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
		$page['text'] = wrap_template($tpl, $form);
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
		$page['text'] = wrap_template($tpl, $form);
		return $page;
	}
	
	if (!$action) {
		$page['text'] = wrap_template($tpl, $form);
		return $page;
	}

	$sql = 'SELECT cv_id, identifier
		FROM contacts_verifications
		LEFT JOIN contacts USING (contact_id)
		WHERE contact_id = %d';
	$sql = sprintf($sql, $data['contact_id']);
	$cv = wrap_db_fetch($sql);

	wrap_setting('log_username', $cv['identifier']);
	$values = [];
	$values['POST']['cv_id'] = $cv['cv_id'];
	if ($action === 'confirm') {
		$values['action'] = 'update';
		$values['POST']['verification_date'] = date('Y-m-d H:i:s');
		$values['POST']['verification_ip'] = wrap_setting('remote_ip');
		$values['POST']['status'] = 'confirmed per link';
	} else {
		$values['action'] = 'delete';
	}
	$ops = zzform_multi('contacts-verifications', $values);
	if (!$ops['id']) {
		wrap_error(sprintf(
			'%s ID %s could not be %sd. %s',
			$form['category'], $data['contact_id'], $values['action'], implode(', ', $ops['error'])
		), E_USER_ERROR);
	}
	$form[$action] = true;

	$page['text'] = wrap_template($tpl, $form);
	return $page;
}
