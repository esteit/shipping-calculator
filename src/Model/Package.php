<?php

namespace EsteIt\ShippingCalculator\Model;

class Package implements PackageInterface
{
    protected $weight;
    protected $senderAddress;
    protected $recipientAddress;
    protected $dimensions;

    /**
     * @param WeightInterface $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return WeightInterface
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param DimensionsInterface $dimensions
     * @return $this
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * @return DimensionsInterface
     */
    public function getDimensions()
    {
        return $this->dimensions;
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
