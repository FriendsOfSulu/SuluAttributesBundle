# SuluAttributesBundle
Symfony is using using Attributes for configuring parts of your application. This bundle adds Attributes for Sulu features so no more searching for which configuration to extend.

## 🛠️ Installation Steps:
```
composer require friendsofsulu/sulu-attributes-bundle
```

## 🧐 Features
He're some of the project's features:

### `#[SuluResourceRoutes]` for configuring routes on the admin class

Before
```yaml
    resources:
        events:
            routes:
                list: app.get_event_list
                detail: app.get_event
```

After
```php
<?php
#[SuluResourcesRoutes(
    'events',
    [
        'list' => 'app.get_event_list',
        'details' => 'app.get_event'
    ]
)]
class EventAdmin {}
```

### `#[SuluNavigationItem]` for configuring navigation items

Before
```php
<?php

use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class ActivityAdmin extends Admin 
{
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $activitiesNavigationItem = new NavigationItem('sulu_activity.activities');
            $activitiesNavigationItem->setPosition(100);
            $activitiesNavigationItem->setView(static::LIST_VIEW);

            $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($activitiesNavigationItem);
        }
    }

    // ...
}
```

After
```php
<?php

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluNavigationItem;

#[SuluNavigationItem(
    title: 'sulu_activity.activities',
    position: 100,
    view: self::LIST_VIEW,
    permission: [self::SECURITY_CONTEXT, PermissionTypes::VIEW],
    parentName: Admin::SETTINGS_NAVIGATION_ITEM,
)]
class ActivityAdmin extends Admin
{
}
```

### `#[SuluSingleSelection]` and `#[SuluMultiSelection]` for configuring selection overlays

You can tag the PropertyResolver with a `#[SuluSingleSelection]` or `#[SuluMultiSelection]` and provide a configuration
for those overlays.

```php
<?php

#[\FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection\SuluSingleSelection(
    name: 'single_account_selection',
    defaultType: 'auto_complete',
    resourceKey: 'accounts',
    types: [
        new \FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection\AutoComplete(
            displayProperty: 'name',
            searchProperties: ['number', 'name']
        ),
        new \FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection\ListOverlay(
            adapter:  'table',
            listKey: 'accounts',
            detailOptions: [],
            displayProperties: ['name'],
            icon: 'su-house',
            emptyText: 'sulu_contact.no_account_selected',
            overlayTitle: 'sulu_contact.single_account_selection_overlay_title',
        )
    ]
)]
class SingleContactSelectionPropertyResolver implements PropertyResolverInterface {}
```

### `#[AsForm]` for defining forms as PHP classes

Instead of `config/forms/*.xml` you can describe a Sulu form as a plain PHP class.
The bundle turns it into the exact same `FormMetadata` the XML loader produces and
writes it into the same form cache (`%sulu.cache_dir%/forms`) — for the rest of Sulu
a PHP form is indistinguishable from an XML one.

Any class carrying `#[AsForm]` is registered automatically (attribute
autoconfiguration), so no service wiring is required in your project.

Before (`config/forms/example.xml`)
```xml
<?xml version="1.0" ?>
<form xmlns="http://schemas.sulu.io/template/template">
    <key>example</key>
    <properties>
        <property name="title" type="text_line" mandatory="true">
            <meta>
                <title lang="de">Titel</title>
                <title lang="en">Title</title>
            </meta>
            <params>
                <param name="headline" value="true"/>
            </params>
        </property>
        <block name="content">
            <meta>
                <title lang="de">Inhalt</title>
            </meta>
            <types>
                <type name="text_eco">
                    <meta>
                        <title lang="de">Text (Eco)</title>
                    </meta>
                    <properties>
                        <property name="quote" type="text_area" mandatory="true">
                            <meta>
                                <title lang="de">Zitat</title>
                            </meta>
                        </property>
                    </properties>
                </type>
            </types>
        </block>
    </properties>
</form>
```

After
```php
<?php

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Block;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

// Class name -> form key: ExampleForm -> example
#[AsForm]
class ExampleForm
{
    #[Property(
        type: FieldType::TextLine,
        title: ['de' => 'Titel', 'en' => 'Title'],
        mandatory: true,
        params: ['headline' => true],
    )]
    public string $title;

    #[Block(
        types: [TextEcoType::class],
        title: ['de' => 'Inhalt'],
    )]
    public array $content;
}

#[AsBlockType(title: ['de' => 'Text (Eco)', 'en' => 'Text (eco)'])]
class TextEcoType
{
    #[Property(type: FieldType::TextArea, title: ['de' => 'Zitat'], mandatory: true)]
    public string $quote;
}
```

#### What is supported

The `type` of a property accepts a `FieldType` enum case for Sulu's built-in types
(autocompletion, typo-safety) **or** a plain string — so every content type works,
including custom ones from your project or third-party bundles:

```php
#[Property(type: FieldType::TextLine)]      // built-in, via the enum
#[Property(type: 'my_custom_content_type')] // custom, via a plain string
```

Beyond simple properties the following building blocks are available:

| Feature (XML)                                 | PHP                                                       |
|-----------------------------------------------|----------------------------------------------------------|
| `<key>`                                       | `#[AsForm(key: …)]` or derived from the class name       |
| `<property type mandatory …>`                 | `#[Property(type, mandatory, …)]`                        |
| `<meta><title>` / `<info_text>`               | `title: [...]`, `infoText: [...]`                         |
| `multilingual`, `colspan`, `spaceAfter`       | `multilingual`, `colSpan`, `spaceAfter`                   |
| `minOccurs`, `maxOccurs`, `onInvalid`         | `minOccurs`, `maxOccurs`, `onInvalid`                     |
| `disabledCondition`, `visibleCondition`       | `disabledCondition`, `visibleCondition`                  |
| `<params>` (string / expression / collection) | `params:` — scalar shorthand or `Param` / `ParamType`    |
| `<tag name priority …>`                        | `Tag`                                                    |
| `<block>` + `<types>` / `<type>`              | `#[Block(types: […])]` + `#[AsBlockType]`                |
| nested blocks                                  | `#[Block]` inside an `#[AsBlockType]` class              |
| `<section>`                                    | `#[Section(SomeSection::class)]` + `#[AsSection]`        |
| Schema (`required` etc.)                       | derived automatically via Sulu's `SchemaMetadataProvider` |

Use `Param` / `ParamType` when a param needs an expression or a collection:

```php
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Param;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\ParamType;

#[Property(
    type: FieldType::SmartContent,
    params: [
        new Param('provider', 'pages'),
        new Param('sortBy', 'published', ParamType::Expression),
        new Param('present_as', type: ParamType::Collection, value: [
            new Param('two', 'Two columns'),
        ]),
    ],
)]
public array $teaser;
```
