<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Length;
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
     * @param Dimensions $dimensions
     * @return Length
     */
    public function calculate(Dimensions $dimensions)
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
