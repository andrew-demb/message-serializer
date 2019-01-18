<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) serializer component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\MessageSerializer;


use Desperado\ServiceBus\Common\Messages\Message;

interface MessageDecoder
{
    /**
     * Restore message from string
     *
     * @param string $serializedMessage
     *
     * @return Message
     *
     * @throws \Desperado\ServiceBus\MessageSerializer\Exceptions\DecodeMessageFailed
     */
    public function decode(string $serializedMessage): Message;

    /**
     * Convert array to specified object
     *
     * @param array<string, mixed> $payload
     * @param string               $class
     *
     * @return object
     *
     * @throws \Desperado\ServiceBus\MessageSerializer\Exceptions\DenormalizeFailed
     */
    public function denormalize(array $payload, string $class): object;
}