<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Weight;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Unit\LengthUnits;
use Moriony\Trivial\Unit\WeightUnits;

class AramexVolumetricWeightCalculator implements VolumetricWeightCalculatorInterface
{
    /**
     * @var MathInterface
     */
    protected $math;
    protected $weightConverter;
    protected $lengthConverter;
    protected $factor;

    public function __construct(MathInterface $math, WeightConverter $weightConverter, LengthConverter $lengthConverter)
    {
        $this->math = $math;
        $this->lengthConverter = $lengthConverter;
        $this->weightConverter = $weightConverter;
        $this->factor = 166;
    }

    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param Dimensions $dimensions
     * @return Weight
     */
    public function calculate(Dimensions $dimensions)
    {
        $length = $this->lengthConverter->convert($dimensions->getLength(), $dimensions->getUnit(), LengthUnits::IN);
        $width = $this->lengthConverter->convert($dimensions->getWidth(), $dimensions->getUnit(), LengthUnits::IN);
        $height = $this->lengthConverter->convert($dimensions->getHeight(), $dimensions->getUnit(), LengthUnits::IN);

        $volume = $this->math->mul($length, $width);
        $volume = $this->math->mul($volume, $height);

        $value = $this->math->div($volume, $this->getFactor());
        $value = $this->math->roundUp($value, 3);

        $weight = new Weight();
        $weight->setValue($value);
        $weight->setUnit(WeightUnits::LB);

        return $weight;
    }
}
