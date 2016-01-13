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
        $values = [$dimensions->getLength()];

        if ($this->math->greaterThan($dimensions->getWidth(), reset($values))) {
            array_unshift($values, $dimensions->getWidth());
        } else {
            $values[] = $dimensions->getWidth();
        }

        if ($this->math->greaterThan($dimensions->getHeight(), reset($values))) {
            array_unshift($values, $dimensions->getHeight());
        } else {
            $values[] = $dimensions->getHeight();
        }

        $normalized = new Dimensions();
        $normalized->setUnit($dimensions->getUnit());
        $normalized->setLength(reset($values));
        $normalized->setWidth(next($values));
        $normalized->setHeight(next($values));

        return $normalized;
    }
}
