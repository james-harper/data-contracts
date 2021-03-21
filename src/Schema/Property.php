<?php

namespace DataContracts\Schema;

use DataContracts\Schema\Constants\Modifier;
use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;
use InvalidArgumentException;

class Property
{
    /** @var string $name The name of the property */
    protected string $name;
    /** @var array $options */
    protected array $options = [];

    /** @var $modifiers Property modifiers */
    protected array $modifiers = [
        Modifier::NOT,
        Modifier::ALL_OF,
    ];

    /**
     * Property constructor
     *
     * @param string $name
     * @param array $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;

        // Handle NOT modifier
        if (isset($options[Modifier::NOT])) {
            $options = $options[Modifier::NOT];
            $options[Option::MODIFIER] = Modifier::NOT;
        }

        // Handle ALL_OF modifier
        if (isset($options[Modifier::ALL_OF])) {
            $options[Option::MODIFIER] = Modifier::ALL_OF;
        }

        $this->options = $options;
    }

    /**
     * Get property name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get property type
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getType()
    {
        if (isset($this->options['type'])) {
            return $this->options['type'];
        }

        if ($this->canBeTypeless()) {
            return '';
        }

        throw new InvalidArgumentException('Type has not been set for ' . $this->name);
    }

    /**
     * Helper method for checking proprty type.
     * It is better to use this method than a simple equality check, because
     * this accounts for compound types (eg numeric -> int,float)
     *
     * @param string $type
     * @return boolean
     */
    public function isOfType(string $type)
    {
        $propType = $this->getType();
        switch ($type) {
            case Type::STRING:
                return $propType === Type::STRING;
            case Type::BOOLEAN:
                return $propType === Type::BOOLEAN;
            case Type::ARRAY:
                return $propType === Type::ARRAY;
            case 'numeric':
                return in_array($propType, Type::numeric());
            default:
                return in_array($propType, Type::all());
        }
    }

    /**
     * Get the specified option, if it has been set
     *
     * @param string $type
     * @return mixed
     */
    public function getOption(string $type)
    {
        if (!$this->hasOption($type)) {
            return null;
        }

        return $this->options[$type];
    }

    /**
     * Check if the specified option has been set
     *
     * @param string $type
     * @return boolean
     */
    public function hasOption(string $type) : bool
    {
        return isset($this->options[$type]);
    }

    /**
     * Determines whether it is valid for this property to not have a type
     * @todo Look through specification for other possible instances of typeless properties
     * @return bool
     */
    private function canBeTypeless()
    {
        return $this->hasOption(Option::MODIFIER) || $this->hasOption(Option::ENUM);
    }
}
