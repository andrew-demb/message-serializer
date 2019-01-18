<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) serializer component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\MessageSerializer\SymfonyNormalizer\Extensions;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Convert snake_case to lowerCamelCase
 */
final class PropertyNameConverter implements NameConverterInterface
{
    /**
     * Local cache
     *
     * @var array<string, string>
     */
    private $localStorage;

    /**
     * @inheritdoc
     */
    public function normalize($propertyName): string
    {
        return $propertyName;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($propertyName): string
    {
        if(false === isset($this->cache[$propertyName]))
        {
            $this->localStorage[$propertyName] = \lcfirst(
                \preg_replace_callback(
                    '/_(.?)/',
                    static function(array $matches): string
                    {
                        return \ucfirst((string) $matches[1]);
                    },
                    $propertyName
                )
            );
        }

        return $this->localStorage[$propertyName];
    }
}
