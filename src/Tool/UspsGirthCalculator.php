<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Length;
use Moriony\Trivial\Math\MathInterface;

class UspsGirthCalculator
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

        $value = $dimensions->getLength();
        $value = $this->math->sum($value, $this->math->mul($dimensions->getWidth(), 2));
        $value = $this->math->sum($value, $this->math->mul($dimensions->getHeight(), 2));

        $girth = new Length();
        $girth->setUnit($dimensions->getUnit());
        $girth->setValue($value);

        return $girth;
    }
}
