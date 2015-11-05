<?php

namespace EsteIt\ShippingCalculator\Package;

use EsteIt\ShippingCalculator\Address\AddressInterface;

/**
 * Class BasePackage
 */
class BasePackage implements PackageInterface
{
    protected $weight;
    protected $calculationDate;
    protected $senderAddress;
    protected $recipientAddress;

    /**
     * @param mixed $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param \DateTime $calculationDate
     * @return $this
     */
    public function setCalculationDate($calculationDate)
    {
        $this->calculationDate = $calculationDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCalculationDate()
    {
        return $this->calculationDate;
    }

    /**
     * @param AddressInterface $address
     * @return $this
     */
    public function setSenderAddress($address)
    {
        $this->senderAddress = $address;

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    /**
     * @param AddressInterface $address
     * @return $this
     */
    public function setRecipientAddress($address)
    {
        $this->recipientAddress = $address;

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getRecipientAddress()
    {
        return $this->recipientAddress;
    }
}
