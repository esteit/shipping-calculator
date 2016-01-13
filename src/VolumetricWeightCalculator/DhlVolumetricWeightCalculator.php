<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\Weight;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Unit\LengthUnits;
use Moriony\Trivial\Unit\WeightUnits;

class DhlVolumetricWeightCalculator implements VolumetricWeightCalculatorInterface
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
        $this->factor = 5000;
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
     * @param DimensionsInterface $dimensions
     * @return mixed
     */
    public function calculate(DimensionsInterface $dimensions)
    {
        $length = $this->lengthConverter->convert($dimensions->getLength(), $dimensions->getUnit(), LengthUnits::CM);
        $width = $this->lengthConverter->convert($dimensions->getWidth(), $dimensions->getUnit(), LengthUnits::CM);
        $height = $this->lengthConverter->convert($dimensions->getHeight(), $dimensions->getUnit(), LengthUnits::CM);

        $volume = $length;
        $volume = $this->math->mul($volume, $width);
        $volume = $this->math->mul($volume, $height);

        $value = $this->math->div($volume, $this->getFactor());
        $value = $this->math->roundUp($value, 3);

        $weight = new Weight();
        $weight->setValue($value);
        $weight->setUnit(WeightUnits::KG);

        return $weight;
    }
}
