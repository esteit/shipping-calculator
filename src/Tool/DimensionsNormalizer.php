<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use Moriony\Trivial\Math\MathInterface;

class DimensionsNormalizer
{
    /**
     * @var MathInterface
     */
    protected $math;

    public function __construct(MathInterface $math)
    {
        $this->math = $math;
    }

    /**
     * @param DimensionsInterface $dimensions
     * @return Dimensions
     */
    public function normalize(DimensionsInterface $dimensions)
    {
        $values = [$dimensions->getLength(), $dimensions->getWidth(), $dimensions->getHeight()];
        usort($values, [$this, 'sort']);

        $normalized = new Dimensions();
        $normalized->setUnit($dimensions->getUnit());
        $normalized->setLength(reset($values));
        $normalized->setWidth(next($values));
        $normalized->setHeight(next($values));

        return $normalized;
    }

    protected function sort($a, $b)
    {
        if ($this->math->eq($a, $b)) {
            return 0;
        }

        if ($this->math->lessThan($a, $b)) {
            return 1;
        }

        return -1;
    }
}
