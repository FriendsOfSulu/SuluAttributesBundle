<?php declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SuluResourceRoute
{
    /** @var 'detail'|'list' $type */
    public function __construct(
        public readonly string $type,
        public readonly string $resourceKey,
    ) {
    }
}
