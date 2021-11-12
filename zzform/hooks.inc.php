<?php 

/**
 * contacts module
 * hook functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/**
 * check if data looks like SPAM, send back to user to confirm
 *
 * @return array
 */
function mf_contacts_check() {
	global $zz_conf;

	// just do this check once
	$token = sha1($zz_conf['int']['secret_key'].$_POST['zz_id']);
	if (!empty($_POST['zz_token']) AND $_POST['zz_token'] === $token) {
		if (!empty($_POST['zz_identical'])) return [];
		if (!empty($_POST['zz_mixedcase'])) return [];
	}
	$change = [];
	$change['output_form'] = sprintf('<input type="hidden" name="zz_token" value="%s">', $token);
	return $change;	
}

/**
 * check if contact looks like SPAM, send back to user to confirm
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_check_contact($ops) {
	$change = mf_contacts_check();
	if (!$change) return [];

	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] !== 'contacts') continue;
		if (mf_contacts_check_mixedcase($ops['record_new'][$index]['contact'])) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'contact' => [
					'class' => 'reselect',
					'explanation' => wrap_text('Is it true that your name contains capital letters after the first letter?').' '
						.wrap_text('If so, please resubmit the form. If not, please enter the correct information.')
						.'<br><label for="checkbox_mixedcase"><input type="checkbox" id="checkbox_mixedcase" name="zz_mixedcase"> '
						.wrap_text('Yes, that’s right.')
						.'</label>'
				]
			];
		}
	}
	return $change;
}


/**
 * check if first and last name are equal or names look like SPAM,
 * send back to user to confirm
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_check_names($ops) {
	$change = mf_contacts_check();
	if (!$change) return [];

	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] !== 'persons') continue;
		if ($ops['record_new'][$index]['first_name'] === $ops['record_new'][$index]['last_name']) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'first_name' => [
					'class' => 'reselect'
				],
				'last_name' => [
					'class' => 'reselect',
					'explanation' => wrap_text('Is it true that your first and last name are the same?').' '
						.wrap_text('If so, please resubmit the form. If not, please enter the correct information.')
						.'<br><label for="checkbox_identical"><input type="checkbox" id="checkbox_identical" name="zz_identical"> '
						.wrap_text('Yes, that’s right.')
						.'</label>'
				]
			];
		}
		if (mf_contacts_check_mixedcase($ops['record_new'][$index]['first_name'])
			OR mf_contacts_check_mixedcase($ops['record_new'][$index]['last_name'])) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'first_name' => [
					'class' => 'reselect'
				],
				'last_name' => [
					'class' => 'reselect',
					'explanation' => wrap_text('Is it true that your name contains capital letters after the first letter?').' '
						.wrap_text('If so, please resubmit the form. If not, please enter the correct information.')
						.'<br><label for="checkbox_mixedcase"><input type="checkbox" id="checkbox_mixedcase" name="zz_mixedcase"> '
						.wrap_text('Yes, that’s right.')
						.'</label>'
				]
			];
		}
	}
	return $change;
}

/**
 * check if a name is made up of both lower and uppercase letters after first letter
 * which can occur for human people but most of the time it is SPAM
 *
 * @param string $name
 * @return bool
 */
function mf_contacts_check_mixedcase($name) {
	// this is not a complete test, just against spammers who use ASCII characters only
	$name = trim($name);
	if (!preg_match('/^[a-zA-Z]+$/', $name)) return false;
	$name = substr($name, 1);
	if (ctype_lower($name)) return false;
	preg_match_all('/[A-Z]/', $name, $matches);
	if (count($matches[0]) === 1) return false; // McSomething etc.
	return true;
}
