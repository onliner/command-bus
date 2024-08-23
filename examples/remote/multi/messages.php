<?php

declare(strict_types=1);

namespace Foo {
    class Hello
    {
        public function __construct(
            public string $name,
        ) {}
    }
}

namespace Bar {
    class Hello
    {
        public function __construct(
            public string $name,
        ) {}
    }
}
