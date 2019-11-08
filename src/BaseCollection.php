<?php

namespace Cjpgdk\Wordbook\Api;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;

/**
 * Class BaseCollection, Just a nice base class for objects that needs to be array accessible
 * @package Cjpgdk\Wordbook\Api
 */
class BaseCollection implements ArrayAccess, JsonSerializable, Countable, IteratorAggregate
{
    /**
     * elements in the collection.
     * @var array
     */
    protected $elements = [];

    /**
     * Create a new collection.
     *
     * @param mixed $elements
     * @return void
     */
    public function __construct($elements = [])
    {
        $this->elements = (array)$elements;
    }

    /**
     * Magic method for getting name indexed elements
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }
        return null;
    }

    /**
     * Get an element from the collection by it's key.
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->elements[$key];
        }
        return $default;
    }

    /**
     * Get all elements in the collection.
     * @return array
     */
    public function elements()
    {
        return $this->elements;
    }

    /**
     * Return all the keys in this collection
     * @return static
     * @see array_keys()
     */
    public function keys()
    {
        return new static(array_keys($this->elements));
    }

    /**
     * Get the first item from the collection
     * @param callable|null $callback
     * @param mixed $default function($key, $value): return true when required element is found
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        if (empty($this->elements)) {
            return $default;
        }
        foreach ($this->elements as $key => $value) {
            if (is_callable($callback)) {
                if ($callback($key, $value)) {
                    return $value;
                }
            } else {
                return $value;
            }
        }
    }

    /**
     * Pop an element off the end of collection
     * @return mixed  the last value of the collection, or null if the collection is empty
     * @see array_pop()
     */
    public function pop()
    {
        return array_pop($this->elements);
    }

    /**
     * Merge this collection with an array of elements or an other collection.
     * @param array|BaseCollection $elements
     * @return static
     */
    public function merge(...$elements)
    {
        if ($elements instanceof BaseCollection) {
            $elements = $elements->jsonSerialize();
        }
        return new static(array_merge($this->elements, $elements));
    }

    /**
     * Shift an element off the beginning of the collection
     * @return mixed The shifted value, or null if the collection is empty
     */
    public function shift()
    {
        return array_shift($this->elements);
    }

    /**
     * Append or add a new item
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return $this
     * @see static::offsetSet
     */
    public function append($offset, $value)
    {
        $this->offsetSet($offset, $value);
        return $this;
    }

    /**
     * Split the collection into smaller chunks
     * @param int $size a positive integer
     * @return BaseCollection multidimensional numerically indexed collection starting with zero, with each dimension containing size elements.
     * @throws InvalidArgumentException "Argument \$size must be an positive integer"
     * @see array_chunk()
     */
    public function chunk(int $size)
    {
        if ($size <= 0) {
            throw new InvalidArgumentException("Argument \$size must be an positive integer");
        }
        $chunks = new static();
        foreach (array_chunk($this->elements, $size, true) as $chunk) {
            $chunks->append(null, new static($chunk));
        }
        return $chunks;
    }

    ###################################
    # Implementation of ArrayIterator #
    ###################################

    /**
     * Extract a slice of the collection
     * @param int $offset
     * @param int $length [optional]
     * @return BaseCollection
     * @see array_slice()
     */
    public function slice(int $offset, int $length = null)
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    ###############################
    # Implementation of Countable #
    ###############################

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The element count as an integer.
     * @since PHP-5.1.0
     */
    public function count()
    {
        return count($this->elements);
    }

    ######################################
    # Implementation of JsonSerializable #
    ######################################

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>
     * @since PHP-5.4.0
     */
    public function jsonSerialize()
    {
        return $this->elements;
    }

    #################################
    # Implementation of ArrayAccess #
    #################################

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return bool true on success or false on failure.
     * @since PHP-5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->elements);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     * @since PHP-5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return ArrayIterator An instance of b>ArrayIterator</b>
     * @since PHP-5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     * @since PHP-5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->elements[$offset];
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     * @since PHP-5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
}