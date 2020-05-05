# Contacts Module

Managing contacts (persons and organisations) in Zugzwang Project CMS.

## What is this repository for? ###

* Managing contacts with contact details, addresses etc.

## How do I get set up? ###

* Install as a Module in folder `_inc/modules`
* Add required categories in categories table of default module
* Set category_ids via `path` = category or parameter `alias=category` for 

    'address' = list of different address types (home, work)
    'contact' = list of different contact types (person, organisation)
    'provider' = list of different contact providers (e-mail, website, phone)
    'relation' = list of contact relations (member)

* Database files found in `docs/sql` folder

## Who do I talk to? ###

* Gustaf Mossakowski <gustaf@koenige.org>
