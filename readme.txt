=== Gravity Forms Field Helper ===
Contributors: luminfire, macbookandrew
Tags: forms, form entries, api
Requires at least: 4.8
Tested up to: 4.9.9
Stable tag: 10.3.1

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

== Description ==

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

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
2. Go to Forms > Settings > Field Helper to add your license key.
3. Create friendly field names on the form settings page: ![Form Settings Page](assets/img/plugin-settings.png)
4. Use the API endpoints to retrieve entries.
   - The standard entry data is untouched.
   - All named fields are added to a `fields` object in each entry object.

== Changelog ==

= 1.0.3.1 | 2019-06-19 =
  - Prevent unchecked checkboxes from return an empty value.

= 1.0.3.0 | 2019-06-19 =
  - Add option to return checkbox fields as an array of selected options.
  - Add EDD licensing for automatic plugin updates.

= 1.0.2 | 2019-03-29 =
  - Fix typo in the admin.
  - Add support for retrieving form entries.

= 1.0.1 | 2019-03-08 =
   - Fix a class loading conflict with Gutenberg.

= 1.0.0 | 2019-03-07 =
   - First version of the plugin.
