<?php

/**
 * Messages serializer implementation.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\MessageSerializer\Tests\Symfony;

use PHPUnit\Framework\TestCase;
use ServiceBus\MessageSerializer\Exceptions\DecodeMessageFailed;
use ServiceBus\MessageSerializer\Exceptions\DenormalizeFailed;
use ServiceBus\MessageSerializer\Exceptions\EncodeMessageFailed;
use ServiceBus\MessageSerializer\Symfony\SymfonyMessageSerializer;
use ServiceBus\MessageSerializer\Tests\Stubs\Author;
use ServiceBus\MessageSerializer\Tests\Stubs\ClassWithPrivateConstructor;
use ServiceBus\MessageSerializer\Tests\Stubs\EmptyClassWithPrivateConstructor;
use ServiceBus\MessageSerializer\Tests\Stubs\TestMessage;
use ServiceBus\MessageSerializer\Tests\Stubs\WithDateTimeField;
use ServiceBus\MessageSerializer\Tests\Stubs\WithNullableObjectArgument;

/**
 *
 */
final class SymfonyMessageSerializerTest extends TestCase
{
    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function emptyClassWithClosedConstructor(): void
    {
        $serializer = new SymfonyMessageSerializer();
        $object     = EmptyClassWithPrivateConstructor::create();

        $encoded = $serializer->encode($object);

        $data = \json_decode($encoded, true);

        static::assertArrayHasKey('message', $data);
        static::assertArrayHasKey('namespace', $data);

        static::assertEmpty($data['message']);
        static::assertSame(EmptyClassWithPrivateConstructor::class, $data['namespace']);

        static::assertSame(\get_object_vars($object), \get_object_vars($serializer->decode($encoded)));
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public static function classWithClosedConstructor(): void
    {
        $serializer = new SymfonyMessageSerializer();
        $object     = ClassWithPrivateConstructor::create(__METHOD__);

        static::assertSame(
            \get_object_vars($object),
            \get_object_vars($serializer->decode($serializer->encode($object)))
        );
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function classNotFound(): void
    {
        $this->expectException(DecodeMessageFailed::class);
        $this->expectExceptionMessage('Class "SomeClass" not found');

        (new SymfonyMessageSerializer())->decode(\json_encode(['message' => 'someValue', 'namespace' => \SomeClass::class]));
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function withoutNamespace(): void
    {
        $this->expectException(DecodeMessageFailed::class);
        $this->expectExceptionMessage(
            'The serialized data must contains a "namespace" field (indicates the message class) and "message" '
            . '(indicates the message parameters)'
        );

        (new SymfonyMessageSerializer())->decode(\json_encode(['message' => 'someValue']));
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function withoutPayload(): void
    {
        $this->expectException(DecodeMessageFailed::class);
        $this->expectExceptionMessage(
            'The serialized data must contains a "namespace" field (indicates the message class) and "message" (indicates the message parameters)'
        );

        (new SymfonyMessageSerializer())->decode(\json_encode(['namespace' => __CLASS__]));
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function withDateTime(): void
    {
        $serializer = new SymfonyMessageSerializer();
        $object     = new WithDateTimeField(new \DateTimeImmutable('NOW'));

        /** @var WithDateTimeField $result */
        $result = $serializer->decode($serializer->encode($object));

        static::assertSame(
            $object->dateTimeValue->format('Y-m-d H:i:s'),
            $result->dateTimeValue->format('Y-m-d H:i:s')
        );
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function wthNullableObjectArgument(): void
    {
        $serializer = new SymfonyMessageSerializer();

        $object = WithNullableObjectArgument::withObject('qwerty', ClassWithPrivateConstructor::create('qqq'));

        static::assertSame(\get_object_vars($object), \get_object_vars($serializer->decode($serializer->encode($object))));

        $object = WithNullableObjectArgument::withoutObject('qwerty');

        static::assertSame(\get_object_vars($object), \get_object_vars($serializer->decode($serializer->encode($object))));
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function denormalizeToUnknownClass(): void
    {
        $this->expectException(DenormalizeFailed::class);
        $this->expectExceptionMessage('Class "Qwerty" not exists');

        /** @noinspection PhpUndefinedClassInspection */
        (new SymfonyMessageSerializer())->denormalize([], \Qwerty::class);
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function withWrongCharset(): void
    {
        $this->expectException(EncodeMessageFailed::class);
        $this->expectExceptionMessage('JSON serialize failed: Malformed UTF-8 characters, possibly incorrectly encoded');

        (new SymfonyMessageSerializer())->encode(
            ClassWithPrivateConstructor::create(
                \iconv('utf-8', 'windows-1251', 'тест')
            )
        );
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function withIncorrectType(): void
    {
        $this->expectException(DecodeMessageFailed::class);
        $this->expectExceptionMessage(
            \sprintf(
                'The type of the "value" attribute for class "%s" must be one of "string" ("integer" given)',
                ClassWithPrivateConstructor::class
            )
        );

        $serializer = new SymfonyMessageSerializer();

        $data = $serializer->encode(ClassWithPrivateConstructor::create(100));

        $serializer->decode($data);
    }

    /**
     * @test
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function successFlow(): void
    {
        $serializer = new SymfonyMessageSerializer();

        $object = TestMessage::create(
            'message-serializer',
            null,
            'dev-master',
            Author::create('Vasiya', 'Pupkin')
        );

        static::assertSame(
            \get_object_vars($object),
            \get_object_vars($serializer->decode($serializer->encode($object)))
        );
    }
}
