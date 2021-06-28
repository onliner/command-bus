<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Helper;

use ReflectionClass;
use Traversable;

final class MessageDumper
{
    /**
     * @param object $message
     *
     * @return array<string, mixed>
     */
    public static function dump(object $message): array
    {
        return iterator_to_array(self::properties($message, new ReflectionClass($message)));
    }

    /**
     * @template T of object
     *
     * @param T                  $message
     * @param ReflectionClass<T> $class
     *
     * @return Traversable<string, mixed>
     */
    private static function properties(object $message, ReflectionClass $class): Traversable
    {
        if ($parent = $class->getParentClass()) {
            yield from self::properties($message, $parent);
        }

        foreach ($class->getProperties() as $property) {
            $property->setAccessible(true);

            yield $property->getName() => self::value($property->getValue($message));

            $property->setAccessible(false);
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function value($value)
    {
        switch (true) {
            case is_iterable($value):
                $items = [];

                foreach ($value as $key => $item) {
                    $items[$key] = self::value($item);
                }

                return $items;
            case is_object($value):
                return self::dump($value);
            default:
                return $value;
        }
    }
}
