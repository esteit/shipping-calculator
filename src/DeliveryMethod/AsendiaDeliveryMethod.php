<?php

namespace Rage\PackageDeliveryCalculator\DeliveryMethod;

use Rage\PackageDeliveryCalculator\CalculationResult;
use Rage\PackageDeliveryCalculator\DeliveryMethod\Asendia\Tariff;
use Rage\PackageDeliveryCalculator\Exception\InvalidArgumentException;
use Rage\PackageDeliveryCalculator\Exception\LogicException;
use Rage\PackageDeliveryCalculator\Package\PackageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AsendiaDeliveryMethod
 */
class AsendiaDeliveryMethod implements DeliveryMethodInterface
{
    /**
     * @var Tariff[]
     */
    protected $tariffs;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = $this->resolveConstructOptions($options);

        $this->tariffs = [];
        foreach ($options['tariffs'] as $tariff) {
            $this->addTariff($tariff);
        }
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
        $result->setDeliveryMethod($this);
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

    protected function resolveConstructOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'tariffs',
        ]);
        $resolver->setDefaults([
            'tariffs' => [],
        ]);
        $resolver->setAllowedTypes([
            'tariffs' => ['array'],
        ]);

        return $resolver->resolve($options);
    }
}
