<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Model\Dimensions;
use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\Length;
use Moriony\Trivial\Math\MathInterface;

class MaximumPerimeterCalculator
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
    public function normalizeDimensions(DimensionsInterface $dimensions)
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

    /**
     * @param DimensionsInterface $dimensions
     * @return Length
     */
    public function calculate(DimensionsInterface $dimensions)
    {
        $dimensions = $this->normalizeDimensions($dimensions);

        $value = $this->math->sum($dimensions->getLength(), $dimensions->getWidth());
        $value = $this->math->mul($value, 2);

        $perimeter = new Length();
        $perimeter->setUnit($dimensions->getUnit());
        $perimeter->setValue($value);

        return $perimeter;
    }
}
