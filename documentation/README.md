# Description

Adds various features and tweaks to Gravity Forms.

# Requirements and Usage

[Purchase and install Gravity Forms](https://www.gravityforms.com/pricing/)

Install and activate this plugin and use the various features below.

# Friendly Field Names

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

If you are using the [Gravity Forms Webhook addon](https://www.gravityforms.com/add-ons/webhooks/), you may choose to use friendly field names in the webhook data as well.

## Requirements

- Enable the Gravity Forms REST API

## Usage

1. Create friendly field names on the form settings page:
  ![Form Settings Page](img/friendly-field-names-form.png)
1. Use the API endpoints to retrieve entries.
   - The standard entry data is untouched.
   - All named fields are added to a `fields` object in each entry object.

### PHP

If you need to use the friendly field names in PHP (using the `gform_after_submission` hook, for instance), follow this example:

```php
// Assuming $entry is a single form entry.
// You can retrieve an entry by id using GFAPI::get_entry( $id );
$entry_with_friendly_names = GF_Field_Helper_Common::replace_field_names( $entry );
```

### Filters

These filters are available to customize the friendly field name data:

- `gf_field_helper_friendly_entry`: called once for every form entry
- `gf_field_helper_api_response`: called for API endpoint responses

## API Endpoints

- Basically, append `/json` to Gravity Forms’ form- or entry-related endpoints to get JSON field data.
- Retrieve all entries:  `https://your-site.com/wp-json/gf/v2/entries/json/`
- Retrieve a specific entry:  `https://your-site.com/wp-json/gf/v2/entries/<entry_id>/json/`
- Retrieve all entries from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/json/`
- Retrieve a specific entry from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/entries/<entry_id>/json/`

## API Parameters

A couple of additional query parameters are available:

- `after[entry_id]`: retrieves only entries after the specified entry ID
- `after[time]`: retrieves only entries after the datetime

## Examples

### Standard Gravity Forms Response

Example of a **standard** `entry/<ID>` response with ID fields:

```json
{
  "id": "2",
  "form_id": "1",
  "post_id": null,
  "date_created": "2019-03-04 15:52:49",
  "date_updated": "2019-03-04 15:52:49",
  "is_starred": "0",
  "is_read": "0",
  "ip": "127.0.0.1",
  "source_url": "https:\/\/wordpress.test\/?gf_page=preview&id=1",
  "user_agent": "<user-agent>",
  "currency": "USD",
  "payment_status": null,
  "payment_date": null,
  "payment_amount": null,
  "payment_method": null,
  "transaction_id": null,
  "is_fulfilled": null,
  "created_by": "1",
  "transaction_type": null,
  "status": "active",
  "1.3": "Jane",
  "1.6": "Doe",
  "3": "Tea",
  "4.1": "Bagel",
  "4.3": "Danish",
  "1.2": "",
  "1.4": "",
  "1.8": "",
  "2.1": "",
  "2.2": "",
  "2.3": "",
  "2.4": "",
  "2.5": "",
  "2.6": "",
  "4.2": ""
}
```

### Friendly Gravity Forms Response

Example of a **friendly** `entry/<ID>/json` response with the `fields` object:

```json
{
  "id": "2",
  "form_id": "1",
  "post_id": null,
  "date_created": "2019-03-04 15:52:49",
  "date_updated": "2019-03-04 15:52:49",
  "is_starred": "0",
  "is_read": "0",
  "ip": "127.0.0.1",
  "source_url": "https:\/\/wordpress.test\/?gf_page=preview&id=1",
  "user_agent": "<user-agent>",
  "currency": "USD",
  "payment_status": null,
  "payment_date": null,
  "payment_amount": null,
  "payment_method": null,
  "transaction_id": null,
  "is_fulfilled": null,
  "created_by": "1",
  "transaction_type": null,
  "status": "active",
  "fields": {
    "name_first": "Jane",
    "name_last": "Doe",
    "beverage": "Tea",
    "name_middle": "",
    "address_1": "",
    "address_2": "",
    "city": "",
    "state": "",
    "zip": "",
    "country": ""
  }
}
```

## Version Control

Field Helper settings are stored in JSON files for easier deployment between development and production environments.

By default, they are stored in a `gf-json/` directory in your theme directory. If you wish to override this location, use the `gf_field_helper_json_directory` filter.

# Disable Autocomplete

Disables the browser’s autocomplete feature on all or individual fields.

## Usage

1. Disable autocomplete for the entire form on the field settings page:
  ![Form Settings Page](img/disable-autocomplete-form.png)
2. Or disable autocomplete for individual fields on the field settings under the Advanced tab:
  ![Field Settings](img/disable-autocomplete-field.png)

# Input Patterns

Allows setting a regex pattern for specific field value requirements. Read the [documentation](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-pattern) for more details.

## Usage

On a single-line text or paragraph text field, enable the Input Pattern checkbox and enter a valid regex pattern.

[Regexr](https://regexr.com/) is a great tool to help develop your regex expression.

![Screenshot](img/input-pattern-field.png)

## Examples

- Only letters (either case) and numbers: `[a-zA-Z0-9]+`
- Only letters (either case), numbers, and the underscore: `[A-Za-z0-9_]`
- Only lowercase letters and numbers; at least 5 characters, but no maximum: `[a-z/d.]{5,}`
