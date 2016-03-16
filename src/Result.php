<?php

namespace EsteIt\ShippingCalculator;

class Result
{
    /**
     * @var Package
     */
    protected $package;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Violation[]
     */
    protected $violations = [];

    /**
     * @param Package $package
     * @return $this
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        $result = null;
        if (array_key_exists($name, $this->data)) {
            $result =  $this->data[$name];
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Violation $violation
     * @return $this
     */
    public function addViolation(Violation $violation)
    {
        $this->violations[] = $violation;

        return $this;
    }

    /**
     * @return Violation[]
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @return bool
     */
    public function hasViolations()
    {
        return !empty($this->violations);
    }
}
