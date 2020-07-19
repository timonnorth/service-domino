<?php

declare(strict_types=1);

namespace ValueObject;

class Result
{
    /** @var string */
    protected $error;
    /**
     * If set means some system error happened, otherwise - validation problem.
     *
     * @var bool
     */
    protected $isSystemError;
    /** @var mixed */
    protected $object;

    /**
     * @param mixed $object
     * @param string|null $error
     * @return Result
     */
    public static function create($object, string $error = null, bool $isSystemError = false): Result
    {
        $result = new Result();
        $result->object = $object;
        $result->error = $error;
        $result->isSystemError = $isSystemError;
        return $result;
    }

    public function hasError(): bool
    {
        return !($this->error === null);
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
