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
