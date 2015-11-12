<?php

namespace EsteIt\ShippingCalculator\Calculator\Asendia;

use EsteIt\ShippingCalculator\Exception\InvalidWeightException;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\NativeMath;

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
        if (!$this->math) {
            $this->math = new NativeMath();
        }

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
            throw new InvalidWeightException('Weight should be a scalar value.');
        }

        $math = $this->getMath();
        if ($math->lessThan($weight, 0)) {
            throw new InvalidWeightException('Weight should be greater than zero.');
        }

        $currentWeight = null;
        $price = null;
        $math = $this->getMath();

        foreach ($this->prices as $w => $p) {
            if ($math->lessOrEqualThan($currentWeight, $weight) && $math->lessOrEqualThan($weight, $w)) {
                $currentWeight = $w;
                $price = $p;
            }
        }

        if (is_null($price)) {
            throw new InvalidWeightException('Can not calculate shipping for this weight.');
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
