<?php

namespace CrCms\Repository\Concerns;

/**
 * Class HasData
 * @package CrCms\Repository\Concerns
 */
trait HasData
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param array $data
     * @return HasData
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array|string $key
     * @param null $value
     * @return HasData
     */
    public function addData($key, $value = null): self
    {
        is_array($key) ?
            $this->data = array_merge($this->data,$key) :
            (!empty($value) && $this->data[$key] = $value);

        return $this;
    }
}