<?php

namespace EsteIt\PackageDeliveryCalculator\Calculator\Asendia;

use EsteIt\PackageDeliveryCalculator\Exception\InvalidArgumentException;
use EsteIt\PackageDeliveryCalculator\Exception\LogicException;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\Native;

/**
 * Class PriceGroup
 */
class PriceGroup
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $prices;

    /**
     * @var MathInterface
     */
    protected $math;

    public function __construct()
    {
        $this->math = new Native();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return MathInterface
     */
    public function getMath()
    {
        return $this->math;
    }

    /**
     * @param MathInterface $math
     * @return $this
     */
    public function setMath(MathInterface $math)
    {
        $this->math = $math;

        return $this;
    }

    /**
     * @param string|int|float $weight
     * @return mixed
     */
    public function getPrice($weight)
    {
        if (!is_scalar($weight)) {
            throw new InvalidArgumentException('Weight should be a scalar value.');
        }

        $currentWeight = null;
        $price = null;
        $math = $this->getMath();

        foreach ($this->prices as $w => $p) {
            if ($math->greaterOrEqualThan($w, $weight) && $math->lessThan($currentWeight, $w)) {
                $currentWeight = $w;
                $price = $p;
            }
        }

        if (is_null($price)) {
            throw new LogicException('Price was not found.');
        }

        return $price;
    }

    /**
     * @param string|float|int $weight
     * @param string|float|int $price
     * @return $this
     */
    public function setPrice($weight, $price)
    {
        $this->prices[$weight] = $price;

        return $this;
    }
}
