# Contacts Module

Managing contacts (persons and organisations) in Zugzwang Project CMS.

## What is this repository for? ###

* Managing contacts with contact details, addresses etc.

## How do I get set up? ###

* Install as a Module in folder `_inc/modules`
* Add required categories in categories table of default module
* Set category_ids for 

    $zz_setting['category']['address'] = n;
    $zz_setting['category']['contact'] = n;
    $zz_setting['category']['provider'] = n;
    $zz_setting['category']['relation'] = n;

* Database files found in `docs/sql` folder

## Who do I talk to? ###

* Gustaf Mossakowski <gustaf@koenige.org>
