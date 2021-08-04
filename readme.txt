=== Field Helper for Gravity Forms ===
Contributors: luminfire, macbookandrew, brilliantplugins, nickciske
Tags: forms, form entries, api, Gravity Forms, FileMaker, database, field names
Requires at least: 4.8
Tested up to: 5.7
Stable tag: 1.4.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

== Description ==

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

See [field-helper-for-gravity-forms.brilliantplugins.info](https://field-helper-for-gravity-forms.brilliantplugins.info) for more documentation.

# Looking to import Gravity Forms entries from your WordPress website into FileMaker?

Create nearly any form with Gravity Form’s drag-and-drop interface, and use this plugin to quickly import that form’s entry data to your FileMaker solution via [fmFlare](fmflare.com).

= Usage =

- Create a Gravity Forms API key.
- On each form, go to the Field Helper settings tab and set the friendly names for the fields you need.
- Append `/json` to Gravity Forms’ form- or entry-related endpoints to get JSON field data.
- Retrieve all entries:  `https://your-site.com/wp-json/gf/v2/entries/json/`
- Retrieve a specific entry:  `https://your-site.com/wp-json/gf/v2/entries/<entry_id>/json/`
- Retrieve all entries from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/json/`
- Retrieve a specific entry from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/entries/<entry_id>/json/`

== Installation ==

1. Install and activate this plugin.
2. Create friendly field names on the form settings page: ![Form Settings Page](documentation/img/plugin-settings.png)
3. Use the API endpoints to retrieve entries.
   - The standard entry data is untouched.
   - All named fields are added to a `fields` object in each entry object.
