<?php 

/**
 * Zugzwang Project
 * Confirm contact registration
 *
 * http://www.zugzwang.org/modules/contact
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2016, 2018 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contactverification($params, $settings) {
	global $zz_conf;
	global $zz_setting;
	global $zz_page;

	$zz_setting['cache'] = false;
	$zz_setting['extra_http_headers'][] = 'X-Frame-Options: Deny';
	$zz_setting['extra_http_headers'][] = "Content-Security-Policy: frame-ancestors 'self'";

	$tpl = 'contact-verification';
	$informal_supported_langugages = ['de'];
	if (!empty($settings['informal']) AND in_array($zz_setting['lang'], $informal_supported_langugages)) {
		$tpl .= '-informal';
	}

	$form = [];
	$form['reminder'] = false;
	$form['own_e_mail'] = !empty($settings['e_mail']) ? $settings['e_mail'] : $zz_setting['own_e_mail'];
	if (!empty($settings['path'])) {
		$form['action'] = $settings['path'];
	} else {
		$url = parse_url($_SERVER['REQUEST_URI']);
		$form['action'] = $url['path'];
	}

	$possible_actions = ['confirm', 'delete'];
	$page['query_strings'] = ['code', 'action', 'confirm', 'delete'];
	$page['breadcrumbs'][] = wrap_text('Confirm Registration');

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

	$sql = 'SELECT cv_id FROM contacts_verifications WHERE contact_id = %d';
	$sql = sprintf($sql, $data['contact_id']);
	$cv_id = wrap_db_fetch($sql, '', 'single value');

	require_once $zz_conf['dir'].'/zzform.php';
	$values = [];
	$values['POST']['cv_id'] = $cv_id;
	if ($action === 'confirm') {
		$values['action'] = 'update';
		$values['POST']['verification_date'] = date('Y-m-d H:i:s');
		$values['POST']['verification_ip'] = $zz_setting['remote_ip'];
		$values['POST']['status'] = 'confirmed per link';
	} else {
		$values['action'] = 'delete';
	}
	$ops = zzform_multi('contacts-verifications', $values);
	if (!$ops['id']) {
		wrap_error(sprintf(
			'Registration ID %s could not be %sd. %s',
			$data['contact_id'], $values['action'], implode(', ', $ops['error'])
		), E_USER_ERROR);
	}
	$form[$action] = true;

	$page['text'] = wrap_template($tpl, $form);
	return $page;
}
