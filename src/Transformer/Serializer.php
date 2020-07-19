<?php

declare(strict_types=1);

namespace Transformer;

use Transformer\Serializer\Exception;

class Serializer
{
    /** @var Encoder\Contract */
    protected $encoder;

    public function __construct(Encoder\Contract $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @throws Encoder\Exception
     */
    public function serialize(\stdClass $data): string
    {
        return $this->encoder->encode($this->normalizeObject($data));
    }

    /**
     * @throws Encoder\Exception
     * @throws Exception
     */
    public function deserialize(string $value, string $classname = ''): \stdClass
    {
        $data = $this->encoder->decode($value);

        if (!is_array($data)) {
            throw new Exception("Not valid input data");
        }

        return $this->hydrate($data, $classname);
    }

    protected function normalizeObject(\stdClass $data): \stdClass
    {
        $data->__cn = get_class($data);

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data->{$key} = $this->normalizeObject($value);
            } elseif (is_array($value)) {
                $data->{$key} = [];

                foreach ($value as $keyIn => $valueIn) {
                    $data->{$key}[$keyIn] = $this->normalizeObject($valueIn);
                }
            }
        }

        return $data;
    }

    /**
     * @return array|\stdClass
     */
    protected function hydrate(array $data, string $classname = '')
    {
        if ($classname != '') {
            $object = new $classname();
        } elseif (isset($data['__cn'])) {
            $object = new $data['__cn']();
        } else {
            $object = [];
        }

        if (isset($data['__cn'])) {
            unset($data['__cn']);
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_array($object)) {
                    $object[$key] = $this->hydrate($value, '');
                } else {
                    $object->{$key} = $this->hydrate($value, '');
                }
            } else {
                $object->{$key} = $value;
            }
        }

        return $object;
    }
}
