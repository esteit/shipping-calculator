<?php

namespace EsteIt\ShippingCalculator\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\VolumetricWeight;
use EsteIt\ShippingCalculator\Model\VolumetricWeightInterface;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Unit\LengthUnits;
use Moriony\Trivial\Unit\WeightUnits;

/**
 * Class DhlVolumetricWeightCalculator
 */
class DhlVolumetricWeightCalculator implements VolumetricWeightCalculatorInterface
{
    /**
     * @var MathInterface
     */
    protected $math;
    protected $weightConverter;
    protected $lengthConverter;

    public function __construct(MathInterface $math, WeightConverter $weightConverter, LengthConverter $lengthConverter)
    {
        $this->math = $math;
        $this->lengthConverter = $lengthConverter;
        $this->weightConverter = $weightConverter;
    }

    /**
     * @param DimensionsInterface $dimensions
     * @param string $toWeightUnit
     * @return VolumetricWeightInterface
     */
    public function calculate(DimensionsInterface $dimensions, $toWeightUnit)
    {
        $length = $this->lengthConverter->convert($dimensions->getLength(), $dimensions->getUnit(), LengthUnits::CM);
        $width = $this->lengthConverter->convert($dimensions->getWidth(), $dimensions->getUnit(), LengthUnits::CM);
        $height = $this->lengthConverter->convert($dimensions->getHeight(), $dimensions->getUnit(), LengthUnits::CM);

        $volume = $length;
        $volume = $this->math->mul($volume, $width);
        $volume = $this->math->mul($volume, $height);

        $value = $this->math->div($volume, 5000);
        $value = $this->weightConverter->convert($value, WeightUnits::KG, $toWeightUnit);
        $value = $this->math->roundUp($value, 3);

        $volumetricWeight = new VolumetricWeight();
        $volumetricWeight->setMassUnit($toWeightUnit);
        $volumetricWeight->setDimensionsUnit($dimensions->getUnit());
        $volumetricWeight->setValue($value);

        return $volumetricWeight;
    }
}
