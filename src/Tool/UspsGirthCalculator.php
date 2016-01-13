<?php

namespace EsteIt\ShippingCalculator\Tool;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\Length;
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
     * @param DimensionsInterface $dimensions
     * @return Length
     */
    public function calculate(DimensionsInterface $dimensions)
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
