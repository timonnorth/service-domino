<?php

declare(strict_types=1);

namespace Transformer;

use Transformer\Encoder\EncoderInterface;
use Transformer\Serializer\Exception;
use Transformer\Serializer\HydrateException;

class Serializer
{
    /** @var EncoderInterface */
    protected $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param object $data
     *
     * @throws Encoder\Exception
     */
    public function serialize($data): string
    {
        if (!is_object($data)) {
            throw new HydrateException();
        }

        return $this->encoder->encode($this->normalizeObject($data));
    }

    /**
     * @throws Encoder\Exception
     * @throws Exception
     */
    public function deserialize(string $value, string $classname = ''): object
    {
        $data = $this->encoder->decode($value);

        if (!is_array($data)) {
            throw new Exception("Not valid input data");
        }

        return $this->hydrate($data, $classname);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function normalizeObject($data)
    {
        if (is_object($data)) {
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
        }

        return $data;
    }

    /**
     * @return array|object
     * @throws HydrateException
     *
     */
    protected function hydrate(array $data, string $classname = '')
    {
        try {
            if ($classname != '') {
                $object = new $classname();
            } elseif (isset($data['__cn'])) {
                $object = new $data['__cn']();
            } else {
                $object = [];
            }
        } catch (\Error $e) {
            throw new HydrateException($e->getMessage());
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
