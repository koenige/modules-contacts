/**
 * contacts module
 * SQL queries for core, page, auth and database IDs
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


-- auth_access_token_contact --
SELECT identifier AS username
FROM /*_PREFIX_*/tokens
LEFT JOIN /*_PREFIX_*/logins USING (login_id)
LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
WHERE access_token = "%s"
AND access_token_expires > NOW()

-- auth_login_contact --
SELECT password, identifier AS username, contacts.contact_id AS user_id, logins.login_id
, contact
FROM /*_PREFIX_*/logins logins
LEFT JOIN /*_PREFIX_*/contacts contacts USING (contact_id)
WHERE active = 'yes' AND identifier = _latin1'%s';

-- auth_login_email --
SELECT password
, (SELECT identification FROM /*_PREFIX_*/contactdetails cd
WHERE cd.contact_id = contacts.contact_id
AND provider_category_id = /*_ID CATEGORIES provider/e-mail _*/ ORDER BY identification LIMIT 1) AS username
, contacts.contact_id AS user_id, logins.login_id
FROM /*_PREFIX_*/logins logins
LEFT JOIN /*_PREFIX_*/contacts contacts USING (contact_id)
WHERE active = 'yes'
HAVING username = _latin1'%s';

-- auth_login_exists --
SELECT login_id
FROM /*_PREFIX_*/logins
LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
WHERE /*_PREFIX_*/contacts.identifier = '%s';

-- auth_username_exists --
SELECT contact_id AS user_id, identifier AS username, contact, contact_id
FROM /*_PREFIX_*/contacts
WHERE identifier = '%s';
