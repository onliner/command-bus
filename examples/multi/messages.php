<?php

declare(strict_types=1);

namespace Foo {
    class Hello
    {
        /**
         * @var string
         */
        public $name;

        /**
         * @param string $name
         */
        public function __construct(string $name)
        {
            $this->name = $name;
        }
    }
}

namespace Bar {
    class Hello
    {
        /**
         * @var string
         */
        public $name;

        /**
         * @param string $name
         */
        public function __construct(string $name)
        {
            $this->name = $name;
        }
    }
}
