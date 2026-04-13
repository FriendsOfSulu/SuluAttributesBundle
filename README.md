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
