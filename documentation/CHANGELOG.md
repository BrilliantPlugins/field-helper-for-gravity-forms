# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

# 1.10.4 | 2024-10-30

- Bugfix: fix empty values for more advanced fields caused by 1.10.3

# 1.10.3 | 2024-10-25

- Bugfix: fix friendly field name settings being mangled by a change in Gravity Forms core

# 1.10.2 | 2024-01-12

- Bugfix: fix combined checkbox field handling

# 1.10.1 | 2023-11-16

- Bugfix: fix automated release

# 1.10.0 | 2023-11-16

- Feature: allow filename or URL response types for signature fields

# 1.9.7 | 2023-10-04

- Bugfix: fix automated release

# 1.9.6 | 2023-10-04

- Bugfix: fix checkbox fields

# 1.9.5 | 2023-09-25

- Bugfix: fix nested fields using the same field IDs

# 1.9.4 | 2023-07-17

- Bugfix: fix time field handling

# 1.9.3 | 2023-07-17

- Bugfix: return original entry data if field names are not set

# 1.9.2 | 2023-07-10

- Bugfix: prevent issues with empty settings in JSON files

# 1.9.1 | 2023-04-28

- Bugfix: prevent issues with `null` settings in JSON files

# 1.9.0 | 2023-04-21

- Feature: add setting to use friendly field names in webhooks

# 1.8.0 | 2023-04-17

- Feature: add support for Gravity Forms Survey radio buttons, checkboxes, single line text, paragraph text, and drop down fields

# 1.7.1 | 2023-03-06

- Bugfix: fix an error when saving a new form

# 1.7.0 | 2023-02-16

- Feature: add support for Gravity Forms Survey likert, rank, and rating fields

# 1.6.1 | 2022-08-22

- Bugfix: prevent error when a JSON settings file doesn’t exist

# 1.6.0 | 2022-08-22

- Feature: add `gf_field_helper_friendly_entry` and `gf_field_helper_api_response` filters

# 1.5.0 | 2022-08-11

- Feature: save and load Field Helper settings as JSON files for easier deployment
- Feature: improve copy for Field Helper unusable fields

# 1.4.5 | 2022-07-06

- Bugfix: fix compatibility issues with Members plugin

# 1.4.4 | 2022-04-13

- Bugfix: fix compatibility issues with Gravity Forms 2.5.7+

# 1.4.3 | 2021-09-30

- Bugfix: update version in readme

# 1.4.2 | 2021-09-30

- Bugfix: fix an issue on first use after installation

# 1.4.1 | 2021-08-04

- Bugfix: update version in readme

# 1.4.0 | 2021-08-04

- Feature: add `after` [query parameters](https://field-helper-for-gravity-forms.brilliantplugins.info/#/?id=api-parameters)

# 1.3.1 | 2021-04-21

- Bugfix: resolve deployment error.

# 1.3.0 | 2021-04-21

- Feature: expand [GravityWiz Nested Forms](https://gravitywiz.com/documentation/gravity-forms-nested-forms/) entries.
- Chore: consolidate duplicated code.

# 1.2.2 | 2021-04-05

- Fix a number of minor code quality issues.

# 1.2.1 | 2020-09-28

## Features

- Highlight fields with invalid input patterns.
- Disable form submission when a field has an invalid input pattern.

# 1.2.0 | 2020-09-28

## Features

- Add form and field settings to disable browser autocomplete.
- Add field settings for HTML input patterns.

# 1.1.2 | 2020-07-09

## Features

- Update branding.

# 1.1.1 | 2020-06-26

## Features

- Tweaks for WordPress.org repository.

# 1.1.0 | 2020-06-24

## Features

- Public release.

## Bugfixes

- Fix problems on forms without friendly labels.
- Fix errors for nonexistent entry IDs.

# 1.0.3.5 | 2020-04-23

## Bugfixes

- Fix API URL in dashboard.

# 1.0.3.4 | 2020-03-18

## Bugfixes

- Handle forms with no friendly labels set.

# 1.0.3.3 | 2019-06-21

## Bugfixes

- Checkbox fields should return an array correctly.
- PHP notices/errors.

# 1.0.3.2 | 2019-06-19

## Features

- Update EDD licensing store details.

# 1.0.3.1 | 2019-06-19

## Bugfixes

- Prevent unchecked checkboxes from return an empty value.

# 1.0.3.0 | 2019-06-19

## Features

- Add option to return checkbox fields as an array of selected options.
- Add EDD licensing for automatic plugin updates.

# 1.0.2 | 2019-03-29

## Features
- Add support for retrieving form entries.

## Bugfixes

- Fix typo in the admin.

# 1.0.1 | 2019-03-08

## Bugfixes

- Fix a class loading conflict with Gutenberg.

# 1.0.0 | 2019-03-07

## Features

- First version of the plugin.
