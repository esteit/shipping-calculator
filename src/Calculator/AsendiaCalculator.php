<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Calculator\Asendia\Tariff;
use EsteIt\ShippingCalculator\Exception\LogicException;
use EsteIt\ShippingCalculator\Package\PackageInterface;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;

/**
 * Class AsendiaCalculator
 */
class AsendiaCalculator extends AbstractCalculator
{
    /**
     * @var Tariff[]
     */
    protected $tariffs;

    public function __construct()
    {
        $this->tariffs = [];
    }

    protected function calculateTotalCost(PackageInterface $package)
    {
        $tariff = $this->getTariff($package->getCalculationDate());
        return $tariff->calculate($package);
    }

    /**
     * @param Tariff $tariff
     * @return $this
     */
    public function addTariff(Tariff $tariff)
    {
        $this->tariffs[] = $tariff;

        return $this;
    }

    /**
     * @param array $tariffs
     * @return $this
     */
    public function addTariffs(array $tariffs)
    {
        foreach ($tariffs as $tariff) {
            $this->addTariff($tariff);
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return Tariff
     */
    public function getTariff(\DateTime $date)
    {
        /** @var Tariff $currentTariff */
        $currentTariff = null;

        foreach ($this->tariffs as $tariff) {
            if ($date >= $tariff->getDate()) {
                if (is_null($currentTariff) || $tariff->getDate() > $currentTariff->getDate()) {
                    $currentTariff = $tariff;
                }
            }
        }

        if (is_null($currentTariff)) {
            throw new LogicException('Tariff was not found.', BasicExceptionInterface::CODE_CAN_NOT_CALCULATE);
        }

        return $currentTariff;
    }
}
