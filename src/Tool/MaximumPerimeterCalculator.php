<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\Length;
use Moriony\Trivial\Math\MathInterface;

class MaximumPerimeterCalculator
{
    /**
     * @var MathInterface
     */
    protected $math;
    /**
     * @var DimensionsNormalizer
     */
    protected $dimensionsNormalizer;

    public function __construct(MathInterface $math, DimensionsNormalizer $dimensionsNormalizer)
    {
        $this->math = $math;
        $this->dimensionsNormalizer = $dimensionsNormalizer;
    }

    /**
     * @param DimensionsInterface $dimensions
     * @return Length
     */
    public function calculate(DimensionsInterface $dimensions)
    {
        $dimensions = $this->dimensionsNormalizer->normalize($dimensions);

        $value = $this->math->sum($dimensions->getLength(), $dimensions->getWidth());
        $value = $this->math->mul($value, 2);

        $perimeter = new Length();
        $perimeter->setUnit($dimensions->getUnit());
        $perimeter->setValue($value);

        return $perimeter;
    }
}
