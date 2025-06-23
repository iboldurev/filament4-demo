This package will automatically construct all translation keys in your Filament application. This means that all texts are mapped automatically to a consistent translation key, so you will only need to add the actual translation in your language file, but never again need to use the `__(...)` helper to reference/insert the actual translation – that is all done automatically 🎉

This package saves you many hours of work, because it automates a big part of the tedious and boring task of creating keys and applying them, ánd it keep your codebase more consistent. Never would you need to think about "how do I call this translation key" again, because all translation keys are by definition constructed in a consistent manner. This is therefore also a perfect addition for any team that wants to ensure consistency in their translation files.

TODO: ADD FIGMA IMAGE?

The package supports everything in Filament Panels, and can also be used outside Filament panels using a simple `HasTranslations` interface.

[//]: # (This means that you will only need to actually add the translation keys in your translation files, but you will _not_ need to manually type/add any of these methods anymore like `->label&#40;__&#40;'filament/resources/user-resource.form.fields.amount.label'&#41;&#41;` or `->helperText&#40;__&#40;'filament/resources/user-resource.form.fields.amount.helper_text'&#41;&#41;`. This saves you so much time, so that when translation any Filament panel this package is really a must-have.)

## Features
                                         
- Auto-generate **any** translation key. ✨ 
- Ensure full translation **consistency** across your codebase. 💪
- **Incrementally implement** in existing projects or enable strict mode to enforce all keys from the start on. 🚀
- Works across **Forms**, **Infolists**, **Actions** & **Table**, including infinite nested and complex structures. ⚡️
- Supports Filament V3 & will support V4. 🎉

## Screenshots

## Installation guide: Filament AutoTranslator

Thank you for purchasing the AutoTranslator plugin for Filament!

We tried to make the plugin as **easy-to-install** and **versatile** as possible. Nevertheless, if you still have a **question, feature request or found an unsupported translation**, please send an e-mail to **support@ralphjsmit.com**.

### Prerequisites

The package is supported on Laravel 10 or higher and Filament V3 (Filament V4 will be added).

#### Installation via Composer

To install the package you should add the following lines to your `composer.json` file in the `repositories` key in order to get access to the private package:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://satis.ralphjsmit.com"
        }
    ]
}
```

> If you have one of my other premium packages installed already, then you don't need to repeat these lines.

Next, you should require the package via the command line. You will be prompted for your username (which is your e-mail) and your password (which is your license key, e.g. `8c21df8f-6273-4932-b4ba-8bcc723ef500`).

```bash
composer require ralphjsmit/laravel-filament-auto-translator
```

#### Configuring the plugin per-panel
                                                      
Enabling the plugin into your panel is very easy and requires two steps:
      
First, for the files in your panel, instead of extending the default Filament class, you should extend the class provided by the package. Perform a **global search-and-replace** on the following classes:

- Replace `Filament\Clusters\Cluster` with `RalphJSmit\Filament\AutoTranslator\Filament\Clusters\Cluster`.
- Replace `Filament\Pages\Page` with `RalphJSmit\Filament\AutoTranslator\Filament\Pages\Page`.
- Replace `Filament\Resources\Resource` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource`.
- Replace `Filament\Resources\RelationManagers\RelationManager` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\RelationManagers\RelationManager`.
- Replace `Filament\Resources\Pages\CreateRecord` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\CreateRecord`.
- Replace `Filament\Resources\Pages\EditRecord` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\EditRecord`.
- Replace `Filament\Resources\Pages\ListRecords` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\ListRecords`.
- Replace `Filament\Resources\Pages\ManageRecords` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\ManageRecords`.
- Replace `Filament\Resources\Pages\ManageRelatedRecords` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\ManageRelatedRecords`.
- Replace `Filament\Resources\Pages\Page` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\Page`.
- Replace `Filament\Resources\Pages\ViewRecord` with `RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\ViewRecord`.
    
Alternatively, if for the above classes you already have your own (abstract) base class in your application, then you can also implement the `HasTranslations` interface and add the following traits:

- Cluster: `RalphJSmit\Filament\AutoTranslator\Concerns\HasClusterTranslations` 
- Page: `RalphJSmit\Filament\AutoTranslator\Concerns\HasPageTranslations` 
- Resource: `RalphJSmit\Filament\AutoTranslator\Concerns\HasResourceTranslations` 
- Resource relation manager: `RalphJSmit\Filament\AutoTranslator\Concerns\HasResourceRelationManagerTranslations` 
- Resource page: `RalphJSmit\Filament\AutoTranslator\Concerns\HasResourcePageTranslations` 
                                         
Example for a base `Resource` class in your own project:

```php
use Filament\Resources\Resource as BaseResource;
use RalphJSmit\Filament\AutoTranslator\Concerns\HasResourceTranslations;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;

abstract class Resource extends BaseResource implements HasTranslations
{
    use HasResourceTranslations; 
}
```

Second, you should register the plugin in any of the panels you want to be translated:

```php
use RalphJSmit\Filament\AutoTranslator\FilamentAutoTranslator;
 
$panel
    ->plugin(FilamentAutoTranslator::make())
```

In the rest of the docs, if we refer to the `$plugin` variable, then we mean the `$plugin = FilamentAutoTranslator::make()`. This is not necessarily a variable, but it helps to keep the code examples shorter and simpler.

## Usage

Once you installed the plugin into your panel, it will automatically start generating translation keys for all the fields in your panel for which you did not manually set a text. 
                                  
### Setting your translation mode (optional)

However, for some situations it needs to make a choice what to do if you did not yet add a translation key for a certain text. Do you want to fall back to the default Filament behaviour? Do you want to have the raw translation key displayed in the UI, so you can easily spot it? Or do you want a balanced mode of falling back to the default behaviour for some rarely changed keys and the raw translation key for the rest?

The plugin supports three modes for operation (only relevant locally):

- **Strict mode**: require translation keys to be present for every single text in your application, even those that are not often set manually (like `navigation-label` or `model-label` for a `Resource` class).
- **Balanced mode**: require translation keys to be present for all form labels, text column labels, action labels etc., but not for a few things like `navigation-label` or `model-label`.
- **Loose mode**: apply the auto-generated translation key if there is a value for it in the translation files, otherwise fall back to Filament's default behaviour of turning an attribute name into a label.

The balanced mode is the **default mode** in which the plugin operates. If you are unsure, start with `balanced` (or `strict`) mode, visually go through your application and add all the raw translation keys that you see displayed in the application. 

As it is better UX to fall back to an English auto-generated text then it is to display a raw translation key, on production therefore the plugin will ’fall back’ to the default Filament behaviour.

In order to enable **Strict mode** or **Loose mode**, use the following code:

```php
use RalphJSmit\Filament\AutoTranslator\Enums\Mode;

$plugin
    ->strict()
    // Or: 
    ->mode(Mode::Strict)

$plugin
    ->loose()
    // Or: 
    ->mode(Mode::Loose)
```

> If you are getting started with the plugin on an existing project, I would recommend either `strict` or `balanced` mode, because it will display raw keys in your application that you are still missing in your translation files. This will help you to quickly identify which keys you still need to add. Only use the `loose` mode if you are sure you want to incrementally add new translation keys using the package only for new code and not for existing code.

### How does it work/getting started

When starting to use the package, the generated translation keys really explain themselves because of their consistency. However, below is an introduction with several examples for people just starting to use the plugin, or to get an idea beforehand how it would work.

#### Translating forms

Let's take a quick look at how the plugin works and how it comes to a certain translation key. Consider the example that you have an `EditUser` page under a `UserResource`. The form defined in the `UserResource` `->form()` method has a component as follows:

```php
Forms\Components\Select::make('role'),
```

The plugin will now look at various aspects to determine a consistent translation key. In this case, it will notice that the form component is placed on a resource page `EditUser`, and that generally the form for that page is defined under the `UserResource`, so it will compile the following translation key: `filament/resources/user-resource.form.fields.role.label`. If the translation key is present in your language files it will get displayed, and if not, you will see the raw translation key, telling you that you need to add this key. This is because the plugin knows that `label` is a required field and every form component should have a label set. On production, the plugin will fall back to the default Filament behaviour, which is to make an English headline out of the component name. 

For the helper text, the plugin knows that this is an optional field. Therefore, it will not display a raw translation key or force you to add it. However, if you add a translation key `filament/resources/user-resource.form.fields.role.helper_text`, the plugin will automatically pick up the helper text and display it. The same applies for pretty much all other methods you can think of, from things like `->addActionLabel()` to `->hint()`, `->prefix()`, `->suffix()`, `->tooltip()`, `->validationAttribute()` and `->loadingMessage()`. 
      
The `lang/{locale}/filament/resources/user-resource.php` translation could now look like this:

```php
return [
    'form' => [
        'fields' => [
            'role' => [
                'label' => 'Role', // Required
                'helper_text' => 'The role of the user', // Optional, will be recognized once you add it.
                'loading_message' => 'Loading available roles from server', // Optional, will be recognized once you add it.
                'prefix' => 'Role ', // Optional, will be recognized once you add it.
                'suffix' => ' for user', // Optional, will be recognized once you add it.
                // Etc...
            ],
        ],
    ],
];
```
                                             
#### Complex forms

The plugin is also smart enough to understand complex structures:

```php
use RalphJSmit\Filament\AutoTranslator\AutoTranslator;
use RalphJSmit\Filament\AutoTranslator\Enums\ActionGroup;

Forms\Components\Section::make('authorization') // The value passed here is used as a key in the translation file...
    ->schema([
        Forms\Components\Select::make('role')
            ->required(),
        Forms\Components\Actions::make([
            Forms\Components\Actions\Action::make('reset_password')
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->required(),
                    // ...
                ])
                ->action(function (Forms\Components\Actions\Action $action, array $data, User $record) {
                    // Perform action...
                    
                    Notification::make()
                        ->title(AutoTranslator::translateActionText($action, ActionGroup::Notifications, 'success.title', ['userName' => $record->name]))
                        ->body(AutoTranslator::translateActionText($action, ActionGroup::Notifications, 'success.body'))
                        ->success()
                        ->send();
                })
            ]),
        ]),
    ]),
```

Which corresponds to the following translation file in `filament/resources/user-resource.php`:

```php
return [
    'form' => [
        'fields' => [
            'authorization' => [
                'heading' => 'Section Heading', 
                'description' => 'Section Description Paragraph.', 
                'schema' => [
                    'role' => [
                        'label' => 'Role', 
                    ],
                    'actions' => [
                        'reset_password' => [
                            'form' => [
                                'fields' => [
                                    'password' => [
                                        'label' => 'New password',
                                    ],
                                    // ...
                                ],
                            ],
                            'notifications' => [
                                'success' => [
                                    'title' => 'Reset password for :userName',
                                    'body' => 'Password successfully reset',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```
                 
As you can see, the translation keys are generated very consistently and in a clear format that corresponds to their place in the Filament panel. 
 
#### Manually request a translation key

You can also easily request a translation key manually using the `AutoTranslator::translateText()` methods:

```php
use RalphJSmit\Filament\AutoTranslator\Enums\FormGroup;

Forms\Components\Select::make('role')
    ->options(fn (Forms\Components\Select $component) => [
        'admin' => AutoTranslator::translateFormText($component, FormGroup::Fields, 'options.admin.label'),
        'user' => AutoTranslator::translateFormText($component, FormGroup::Fields, 'options.user.label'),
    ]),
```

There are methods like these for `translateFormText()`, `translateActionText()`, `translateTableText()` and `translateInfoListText()`.

Each of these functions accept the form/action/table/infolist component that you want to retrieve the translation key for, plus a group:

- `FormGroup::Fields`: use it whenever you are interacting with something that originates from a form field.
- `ActionGroup::Form`/`ActionGroup::Infolist`/`ActionGroup::Notifications`: use `Form` when you work with something originating from an action form, `Infolist` when you work with something originating from an infolist, and `Notifications` when you work with something originating from a notification sent by an action.
- `InfolistGroup::Entries`: use it when you work with something originating from an infolist entry.
- `TableGroup::Actions`/`TableGroup::BulkActions`/`TableGroup::Columns`/`TableGroup::Filters`: use `Actions` when you work with something originating from a table action, `BulkActions` when you work with something originating from a table bulk action, `Columns` when you work with something originating from a table column, and `Filters` when you work with something originating from a table filter.

#### Translating tables

Now, consider the following table on the `UserResource`:

```php
$table
    ->columns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('email'),
    ])
    ->actions([
        Tables\Actions\Action::make('reset_password')
            ->form([
                Forms\Components\TextInput::make('password')
                    ->required(),
                // ...
            ])
            ->action(function (Tables\Actions\Action $action, array $data, User $record) {
                // Perform action...
                
                Notification::make()
                    ->title(AutoTranslator::translateTableText($action, ActionGroup::Notifications, 'success.title', ['userName' => $record->name]))
                    ->body(AutoTranslator::translateTableText($action, ActionGroup::Notifications, 'success.body'))
                    ->success()
                    ->send();
            })
        ])    
    ->filters([
        Tables\Filters\SelectFilter::make('role')
            ->options(fn (Tables\Filters\SelectFilter $filter) => [
                'admin' => AutoTranslator::translateTableText($filter, TableGroup::Filters, 'options.admin.label'),
                'user' => AutoTranslator::translateTableText($filter, TableGroup::Filters, 'options.user.label'),
            ]),
    ])
```

This corresponds to the following translation file:

```php
return [
    'table' => [
        'columns' => [
            'name' => [
                'label' => 'Name', // Required
            ],
            'email' => [
                'label' => 'E-mail', // Required
                'prefix' => 'E-mail: ', // Optional
            ],
        ],
        'actions' => [
            'reset_password' => [
                'form' => [
                    'fields' => [
                        'password' => [
                            'label' => 'New password',
                        ],
                        // ...
                    ],
                ],
                'notifications' => [
                    'success' => [
                        'title' => 'Reset password for :userName',
                        'body' => 'Password successfully reset',
                    ],
                ],
            ],
        ],
        'filters' => [
            'role' => [
                'label' => 'Role',
                'options' => [ 
                    // Custom translation key requested manually
                    'admin' => 'Admin',
                    'user' => 'User',
                ],
            ],
        ],
        'empty_state_heading' => 'No users found',
    ],
];
```

#### Translating actions
                                               
The package will also generate translation keys for actions, even if they are infinitely nested. Image the following header action on the `EditUser` page:

```php
protected function getHeaderActions(): array
{
    return [
        Actions\Action::make('first_action')
            ->infolist([
                // ..
            ])
            ->extraModalFooterActions([
                Actions\Action::make('second_action')
                    ->form([
                        //
                    ]),
                    // ...
            ]),
    ];
}
```

The translation keys are generated as follows in the `filament/resources/user-resource.php` file:

```php
return [
    'pages' => [
        'edit-user' => [
            'navigation-label' => 'Navigation label',
            'title' => 'Page title',
            'subheading' => 'This page allows you to edit a user and perform several actions.',
            'actions' => [
                'first_action' => [
                    'label' => 'First action',
                    'infolist' => [
                        'entries' => [
                            // ..
                        ],
                    ],
                    'extra_modal_footer_actions' => [
                        'second_action' => [
                            'label' => 'Second action',
                            'form' => [
                                // ..
                            ],
                        ],
                        // ...
                    ]
                ],
            ],
        ],
    ],
];
```

I hope as you can see that this package will have a translation key for pretty much anything you can imagine, from the important ones like `$field->label()` to the tiniest ones as `$table->emptyStateDescription()` or infinitely nested actions within actions. Not every possible key or method is documented because of the huge amount of possible methods, but the system is easy to understand and really speaks for itself. If you are having an issue with a translation key, let me know at support@ralphjsmit.com.

### Translation Namespace

The default namespace for a translation is generated based on the FQCN of the class where it originates. Shortly speaking, a FQCN `App\Filament\Resources\UserResource` will get a translation key of `filament/resources/user-resource.php`, whereas an FQCN `App\Filament\Admin\Resources\UserResource` will get a translation key of `filament/admin/resources/user-resource.php`. 

In case that you want to override the namespace, you can override it using the `$plugin->translationGroups()` method:

```php
$plugin
    ->translationGroups([
        'App\\Filament\\Admin' => 'filament/admin-panel', // Re-codes all files under `App\Filament\Admin` to the translation key `filament/admin-panel/...`.    
    ])
```

### Custom translation methods    

By default, all components – also those of plugins – have their default methods translatable. So if you are using a `MoneyInput` component from an external package, the `label`, `helper_text`, `prefix`, `suffix` etc. will get translated as usual. However, if an external plugin provides a custom method like `->currencySuffix()`, then this method not be auto-translatable by default. You can register such methods and mark it as required using the following methods:

```php
$plugin
    ->additionalActionMethods([
        // ...
    ])
    ->additionalFormFieldMethods([
        'currencySuffix' => true, // `method` => `(bool) isNullable`
    ])
    ->additionalInfolistEntryMethods([
        // ...
    ])
    ->additionalTableColumnMethods([
        // ...
    ])
```

### Roadmap

I hope this package will be useful to you! If you have any ideas or suggestions on how to make it more useful, please let me know (support@ralphjsmit.com).

### Support

If you have a question, bug or feature request, please e-mail me at support@ralphjsmit.com or tag @ralphjsmit on [#activitylog-pro](https://discord.com/channels/883083792112300104/1194008969686032415) on the [Filament Discord](https://filamentphp.com/discord). Love to hear from you!

🙋‍ [Ralph J. Smit](https://ralphjsmit.com)