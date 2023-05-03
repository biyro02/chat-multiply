<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:59 PM
 */

namespace App\Http\Services\Slack\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

class BaseModel implements Arrayable, JsonSerializable, \ArrayAccess
{
    /**
     * @var array|\stdClass
     */
    protected $attributes = [];
    protected $rawData = [];
    protected $responseKey = '';
    public $fields = [];

    /**
     * BaseModel constructor.
     * @param $attributes
     */
    public function __construct($attributes = [])
    {
        $this->rawData = ($this->responseKey) ? $attributes[$this->responseKey] : $attributes;
        foreach ($this->getFields() as $key => $defaultValue)
        {
            $this->attributes[$key] = $defaultValue;
            if(isset($this->rawData[$key])){
                $this->attributes[$key] = $this->rawData[$key];
            }
        }
    }

    /**
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        if(array_key_exists($property, $this->attributes)){
            return $this->attributes[$property];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        if(!method_exists($this, $name)){
            if(Str::startsWith($name, 'get')){
                $propertyName = Str::snake(Str::substr($name, 3));
                if(isset($this->attributes[$propertyName])){
                    return $this->attributes[$propertyName];
                }
            }
        }

        return null;
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset)){
            return $this->attributes[$offset];
        }
        return null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if($this->offsetExists($offset)){
            $this->attributes[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if($this->offsetExists($offset)){
            unset($this->attributes[$offset]);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->attributes;
    }
}