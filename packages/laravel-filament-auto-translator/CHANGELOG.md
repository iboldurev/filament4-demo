# Changelog

All notable changes to `laravel-filament-auto-translator` will be documented in this file.

## 1.3.2 - 2025-03-05

- Feat: support custom table filter forms.

## 1.3.1 - 2025-02-27

- Feat: improved detection of current component in wizard in action

## 1.3.0 - 2025-02-25

- Feat: Laravel 12 support.
- Fix: preserve form components behind modal background to get translated from action.

## 1.2.0 - 2025-02-17

- Feat: ability to have absolute translation keys.

## 1.1.11 - 2025-02-15

- Revert: optimize `->translationKey()` performance with many translation keys

## 1.1.10 - 2025-02-15

- Feat: optimize `->translationKey()` performance with many translation keys
- Feat: create widget-specific traits
- Test: action scenario with `ActionGroup`

## 1.1.9 - 2025-02-13

- Fix: access form name gracefully.

## 1.1.8 - 2025-02-11

- Chore: translate toggle & select column

## 1.1.7 - 2025-01-28

- Feat: add support for automatic translating of table groups after Filament closure support.
- Fix: change retrieving of pre-built translations for filters

## 1.1.5 - 2025-01-16

- Feat: add breadcrumb support

## 1.1.4 - 2025-01-16

- Fix: custom form names when provided by traits

## 1.1.3 - 2025-01-16

- Fix: apply default filter actions text.

## 1.1.2 - 2025-01-16

- Fix: non-table actions with forms on `ManageRelatedRecords` page.
- Fix: correctly apply default bulk actions text.

## 1.1.1 - 2025-01-16

- Fix: `Argument #2 ($record) must be of type Illuminate\Database\Eloquent\Model, null given`

## 1.1.0 - 2025-01-15

- Feat: add support for table `ColumnGroup` components.
- Fix: use of default table filter.

## 1.0.0 - 2025-01-01

- Initial release!
