# Inventory

## New Content

The inventory requires users to login in order to anything at all.

The first page to open will be the item viewing page. You can specifiy which types of inventory items you want to view by using the selectors above their corresponding columns. Filters are sent back to this page do define which items are filtered.

## Changes

There is no "?action=viewall" anymore. If you want to search fora  specific Serial Number, use its column filter.

## Old

This is the LCDI's internal inventory tracking system. 

It hooks into our AD for logins.


The original project was written very quickly due to necessity in the lab.  
There are probably bugs, errors, and algorithms that make no sense....but it works :P


Change the mysql credentials in includes/functions.php and admin.php


edit and rename the adLDAP module to fit your company's domain
includes/adLDAP/src/adLDAP.php.sample
