<?php declare(strict_types=1);

namespace Solido\Serialization\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Solido\Serialization\SerializerInterface;

abstract class AbstractSerializerAdapterTest extends TestCase
{
    abstract protected function createAdapter(): SerializerInterface;

    public function testShouldSerializeToJson(): void
    {
        $serializer = $this->createAdapter();
        $obj = new Test_Object();

        self::assertJsonStringEqualsJsonString(<<<JSON
{
    "arr": [ "Element 1" ],
    "bar_bar": null,
    "foo": null,
    "foo_bar": null,
    "integer": 42,
    "map": {
        "key": "value"
    },
    "number": 0.42,
    "obj": {},
    "str": "This is a string",
    "x_bar": null
}
JSON
, $serializer->serialize($obj, 'json'));
    }

    public function testShouldRespectSerializationGroups(): void
    {
        $serializer = $this->createAdapter();
        $obj = new Test_Object();

        self::assertJsonStringEqualsJsonString(<<<JSON
{
    "bar_bar": null,
    "foo_bar": null,
    "x_bar": null
}
JSON
, $serializer->serialize($obj, 'json', [ 'groups' => ['bar'] ]));

        self::assertJsonStringEqualsJsonString(<<<JSON
{
    "foo": null,
    "foo_bar": null
}
JSON
, $serializer->serialize($obj, 'json', [ 'groups' => ['foo'] ]));

        self::assertJsonStringEqualsJsonString(<<<JSON
{
    "bar_bar": null,
    "x_bar": null,
    "foo": null,
    "foo_bar": null
}
JSON
, $serializer->serialize($obj, 'json', [ 'groups' => ['bar', 'foo'] ]));
    }

    public function testShouldRespectSerializeNullOption(): void
    {
        $serializer = $this->createAdapter();
        $obj = new Test_Object();

        self::assertJsonStringEqualsJsonString(<<<JSON
{
    "arr": [ "Element 1" ],
    "integer": 42,
    "map": {
        "key": "value"
    },
    "number": 0.42,
    "obj": {},
    "str": "This is a string"
}
JSON
, $serializer->serialize($obj, 'json', [ 'serialize_null' => false ]));
    }
}

class Test_Object
{
    public $fooBar;
    protected $barBar;
    private $xBar;

    public $foo = null;
    public $str = 'This is a string';
    public $number = .42;
    public $integer = 42;
    public $arr = [ 'Element 1' ];
    public $map = [ 'key' => 'value' ];
    public $obj;

    public function __construct()
    {
        $this->obj = new \stdClass();
    }

    public function getBarBar()
    {
        return $this->barBar;
    }

    public function getXBar()
    {
        return $this->xBar;
    }
}
