<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\SuluOverrides;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluNavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\AdminPool;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class NavigationAdmin extends Admin
{
    public function __construct(
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly AdminPool $adminPool
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        foreach ($this->adminPool->getAdmins() as $admin) {
            $adminReflection = new \ReflectionClass($admin);
            foreach ($adminReflection->getAttributes(SuluNavigationItem::class) as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();

                if (!$this->hasPermission($adminReflection, $attribute)) {
                    continue;
                }

                $currentItemCollection = $navigationItemCollection;
                if ($attribute->parentName) {
                    $currentItemCollection->get($attribute->parentName);
                }

                $currentItemCollection->add($this->createNavigationItem($attribute));
            }
        }
    }

    private function createNavigationItem(SuluNavigationItem $attribute): NavigationItem
    {
        $navigationItem = new NavigationItem($attribute->title);
        $navigationItem->setView($attribute->view);

        if (null !== $attribute->position) {
            $navigationItem->setPosition($attribute->position);
        }
        if (null !== $attribute->icon) {
            $navigationItem->setIcon($attribute->icon);
        }

        return $navigationItem;
    }

    /**
     * @param \ReflectionClass<Admin> $adminReflection
     */
    private function hasPermission(\ReflectionClass $adminReflection, SuluNavigationItem $attribute): bool
    {
        if (false === $attribute->permission) {
            return true;
        }

        $permissions = $attribute->permission;
        if (null === $permissions) {
            $securityContext = $adminReflection->getConstant('SECURITY_CONTEXT');
            $permissions = [$securityContext, PermissionTypes::EDIT];
        }

        return $this->securityChecker->hasPermission($permissions[0], $permissions[1]);
    }
}
