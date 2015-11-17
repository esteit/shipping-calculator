<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Exception\LogicException;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Moriony\Trivial\Collection\CollectionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Closure;

class SelectiveCalculator extends AbstractCalculator
{
    protected $options;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'calculators',
                'selector'
            ])
            ->setAllowedTypes([
                'calculators' => 'Moriony\Trivial\Collection\CollectionInterface',
                'selector' => 'Closure',
            ]);

        $this->options = $resolver->resolve($options);
    }

    public function visit(CalculationResultInterface $result, PackageInterface $package)
    {
        $selector = $this->getSelector();
        $calculator = $selector($this->getCalculators(), $package);
        if (!$calculator instanceof CalculatorInterface) {
            throw new LogicException('Calculator was not found.');
        }
        $calculator->visit($result, $package);
    }

    /**
     * @return CollectionInterface|CalculatorInterface[]
     */
    public function getCalculators()
    {
        return $this->options['calculators'];
    }

    /**
     * @return Closure
     */
    public function getSelector()
    {
        return $this->options['selector'];
    }
}
