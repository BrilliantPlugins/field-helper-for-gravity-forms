# Description

Adds various features and tweaks to Gravity Forms.

# Requirements and Usage

[Purchase and install Gravity Forms](https://www.gravityforms.com/pricing/)

Install and activate this plugin and use the various features below.

# Friendly Field Names

Adds a settings page and REST API endpoint to retrieve human- and computer-friendly field names.

## Requirements

- Enable the Gravity Forms REST API

## Usage

1. Create friendly field names on the form settings page:
  ![Form Settings Page](img/friendly-field-names-form.png)
2. Use the API endpoints to retrieve entries.
  - The standard entry data is untouched.
  - All named fields are added to a `fields` object in each entry object.

## API Endpoints

- Basically, append `/json` to Gravity Forms’ form- or entry-related endpoints to get JSON field data.
- Retrieve all entries:  `https://your-site.com/wp-json/gf/v2/entries/json/`
- Retrieve a specific entry:  `https://your-site.com/wp-json/gf/v2/entries/<entry_id>/json/`
- Retrieve all entries from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/json/`
- Retrieve a specific entry from a specific form:  `https://your-site.com/wp-json/gf/v2/forms/<form_id>/entries/<entry_id>/json/`

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

# Disable Autocomplete

Disables the browser’s autocomplete feature on all or individual fields.

## Usage

1. Disable autocomplete for the entire form on the field settings page:
  ![Form Settings Page](img/disable-autocomplete-form.png)
2. Or disable autocomplete for individual fields on the field settings under the Advanced tab:
  ![Field Settings](img/disable-autocomplete-field.png)
