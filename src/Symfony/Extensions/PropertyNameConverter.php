<?php

/**
 * Messages serializer implementation.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\MessageSerializer\SymfonyNormalizer\Extensions;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Convert snake_case to lowerCamelCase.
 */
final class PropertyNameConverter implements NameConverterInterface
{
    /**
     * Local cache.
     *
     * @psalm-var array<string, string>
     */
    private array $localStorage;

    /**
     * {@inheritdoc}
     */
    public function normalize(string $propertyName): string
    {
        return $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(string $propertyName): string
    {
        if (false === isset($this->localStorage[$propertyName]))
        {
            $joinedString = \preg_replace_callback(
                '/_(.?)/',
                static function (array $matches): string
                {
                    return \ucfirst((string) $matches[1]);
                },
                $propertyName
            );

            $this->localStorage[$propertyName] = \lcfirst((string) $joinedString);
        }

        return $this->localStorage[$propertyName];
    }
}
