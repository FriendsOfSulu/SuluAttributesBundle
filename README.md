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
