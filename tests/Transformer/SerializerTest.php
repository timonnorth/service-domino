<?php

declare(strict_types=1);

namespace Tests\Transformer\Encoder;

use Transformer\Encoder\Exception;
use Transformer\Encoder\Json;
use Transformer\Serializer;

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Serializer */
    protected $serializer;

    public function testSerializeOk()
    {
        self::assertEquals($this->getValue1(), $this->getSerializer()->serialize($this->getObject1()));
    }

    public function testDeserializeOk()
    {
        $object = $this->getSerializer()->deserialize($this->getValue1());
        self::assertEquals($this->getObject1(), $object);
    }

    public function testDeserializeError()
    {
        $this->expectException(Exception::class);
        $this->getSerializer()->deserialize('tiesto');
    }

    public function testDeserializeUndefinedClassname()
    {
        $this->expectException(Serializer\HydrateException::class);
        $this->expectExceptionMessage("Class 'tiesto' not found");
        $this->getSerializer()->deserialize('{"__cn":"tiesto"}');
    }

    protected function getSerializer(): Serializer
    {
        if ($this->serializer == null) {
            $this->serializer = new Serializer(new Json());
        }

        return $this->serializer;
    }

    protected function getValue1(): string
    {
        return '{"a":"a","b":"b","o":{"c":"c","__cn":"stdClass"},"ar":[{"c":"c","__cn":"stdClass"},{"d":"d","__cn":"stdClass"}],"__cn":"stdClass"}';
    }

    protected function getObject1(): \stdClass
    {
        $object     = new \stdClass();
        $object->a  = "a";
        $object->b  = "b";
        $obj2       = new \stdClass();
        $obj2->c    = "c";
        $obj3       = new \stdClass();
        $obj3->d    = "d";
        $object->o  = $obj2;
        $object->ar = [$obj2, $obj3];

        return $object;
    }
}
