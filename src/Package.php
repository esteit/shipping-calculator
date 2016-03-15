<?php

namespace EsteIt\ShippingCalculator;

class Package
{
    protected $weight;
    protected $senderAddress;
    protected $recipientAddress;
    protected $dimensions;

    /**
     * @param Weight $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return Weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param Dimensions $dimensions
     * @return $this
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * @return Dimensions
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setSenderAddress($address)
    {
        $this->senderAddress = $address;

        return $this;
    }

    /**
     * @return Address
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setRecipientAddress($address)
    {
        $this->recipientAddress = $address;

        return $this;
    }

    /**
     * @return Address
     */
    public function getRecipientAddress()
    {
        return $this->recipientAddress;
    }
}
