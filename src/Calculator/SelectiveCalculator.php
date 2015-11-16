<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Moriony\Trivial\Collection\CollectionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Closure;

/**
 * Class SelectiveCalculator
 */
class SelectiveCalculator extends AbstractCalculator
{
    protected $options;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'calculators',
                'filter'
            ])
            ->setAllowedTypes([
                'calculators' => 'Moriony\Trivial\Collection\CollectionInterface',
                'filter' => 'Closure',
            ]);

        $this->options = $resolver->resolve($options);
    }

    public function visit(CalculationResultInterface $result, PackageInterface $package)
    {
        $calculator = $this->getCalculators()->filter($this->getFilter())->first();
        return $calculator->calculate($package);
    }

    /**
     * @return CollectionInterface|CalculatorInterface[]
     */
    public function getCalculators()
    {
        $this->options['calculators'];

        return $this;
    }

    /**
     * @return Closure
     */
    public function getFilter()
    {
        $this->options['filter'];

        return $this;
    }
}
