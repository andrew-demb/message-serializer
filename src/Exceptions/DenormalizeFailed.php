<?php

/**
 * Messages serializer implementation.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\MessageSerializer\Exceptions;

/**
 *
 */
final class DenormalizeFailed extends \RuntimeException implements SerializerExceptionMarker
{
}
