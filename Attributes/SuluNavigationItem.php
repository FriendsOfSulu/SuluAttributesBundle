<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SuluNavigationItem
{
    /**
     * @param string $title
     *                      Label of the navigation item. Will be translated.
     * @param string $view
     *                     Name of the view that should be shown when clicking on the menu item
     * @param string|null $icon
     *                          Icon to show infront of the name (default null means no icon)
     * @param int|null $position
     *                           Ordering of the element with their siblings (null means at the end)
     * @param array{string, string}|null|false $permission
     *                                                     Only show the menu item when the user has the following permission.
     *                                                     * null = use the default
     *                                                     * false = any user can see the menu item
     *                                                     * any array will be passed to the Sulu security checker
     *                                                     (by default: [self::SECURITY_CONTEXT, PermissionTypes::EDIT]
     * @param string|null $parentName
     *                                Name of the parent navigation item (null means main menu)
     */
    public function __construct(
        public readonly string $title,
        public readonly string $view,
        public readonly ?string $icon,
        public readonly ?int $position = null,
        public readonly array|false|null $permission = null,
        public readonly ?string $parentName = null,
    ) {
    }
}
