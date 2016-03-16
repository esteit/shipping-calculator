<?php

namespace EsteIt\ShippingCalculator;

/**
 * Class Violation
 */
class Violation
{
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';

    /**
     * @var string
     */
    protected $message;
    /**
     * @var string
     */
    protected $level;

    /**
     * @param string $message
     * @param string $level
     */
    public function __construct($message, $level = self::LEVEL_ERROR)
    {
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }
}
