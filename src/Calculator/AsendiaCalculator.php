<?php

namespace EsteIt\PackageDeliveryCalculator\Calculator;

use EsteIt\PackageDeliveryCalculator\CalculationResult;
use EsteIt\PackageDeliveryCalculator\Calculator\Asendia\Tariff;
use EsteIt\PackageDeliveryCalculator\Exception\LogicException;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;

/**
 * Class AsendiaCalculator
 */
class AsendiaCalculator implements CalculatorInterface
{
    /**
     * @var Tariff[]
     */
    protected $tariffs;

    public function __construct()
    {
        $this->tariffs = [];
    }

    /**
     * @param PackageInterface $package
     * @return mixed
     */
    public function calculate(PackageInterface $package)
    {
        $totalCost = $this->getTariff($package->getCalculationDate())->calculate($package);

        $result = new CalculationResult();
        $result->setPackage($package);
        $result->setCalculator($this);
        $result->setTotalCost($totalCost);

        return $result;
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
            throw new LogicException('Tariff was not found.');
        }

        return $currentTariff;
    }
}
