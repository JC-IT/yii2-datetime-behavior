<?php

namespace JCIT\behaviors;

use Carbon\Carbon;
use yii\base\Behavior;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\ActiveRecord;

/**
 * Class DateTimeBehavior
 * @package JCIT\behaviors
 */
class DateTimeBehavior extends Behavior
{
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIME = 'time';

    /**
     * Suffix to recognize when to get or set an object
     *
     * @var string
     */
    public $attributeSuffix = 'Object';

    /**
     * Map of attributes to type
     *
     * @var string[]
     */
    public $attributes = [];

    /**
     * Timezone the raw attributes are stored in
     *
     * @var string
     */
    public $attributeTimezone = 'UTC';

    /**
     * @var string
     */
    public $dateTimeClass = Carbon::class;

    /**
     * Formats of how to (de)serialize the property
     *
     * @var array
     */
    public $typeFormats = [
        self::TYPE_DATETIME => 'Y-m-d H:i:s',
        self::TYPE_DATE => 'Y-m-d',
        self::TYPE_TIME => 'H:i:s',
    ];

    /**
     * @param string $name
     * @return mixed
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (($realName = $this->isBehaviorProperty($name)) !== false) {
            $type = $this->attributes[$realName];
            switch ($type) {
                case self::TYPE_DATETIME:
                    $timezone = (new Carbon())->timezone->getName();
                    $result = $this->dateTimeClass::createFromFormat($this->typeFormats[$type], $this->owner->{$realName}, $this->attributeTimezone);
                    $new = new $this->dateTimeClass($this->owner->{$realName}, 'UTC');
                    var_dump($new);
                    $new->shiftTimezone('Europe/Amsterdam');
                    var_dump($new);
                    var_dump($result);
                    $result->shiftTimezone($timezone);
                case self::TYPE_DATE:
                case self::TYPE_TIME:
                    $result = Carbon::createFromFormat($this->typeFormats[$type], $this->owner->{$realName});
                    break;
                default:
                    throw new UnknownPropertyException('Unknown type');
            }

            return $result;
        }

        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if (($realName = $this->isBehaviorProperty($name)) !== false) {
            if (!$value instanceof Carbon) {
                throw new InvalidArgumentException('Can only set objects instance of ' . Carbon::class);
            }
            $clonedValue = $value->clone();

            $type = $this->attributes[$realName];
            switch ($type) {
                case self::TYPE_DATETIME:
                    $clonedValue->setTimezone($this->attributeTimezone);
                case self::TYPE_DATE:
                case self::TYPE_TIME:
                    $name = $realName;
                    $value = $clonedValue->format($this->typeFormats[$type]);
                default:
                    throw new UnknownPropertyException('Unknown type');
            }
        }

        parent::__set($name, $value); // TODO: Change the autogenerated stub
    }

    /**
     * @param \yii\base\Component $owner
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        if (empty($this->attributes) && $owner instanceof ActiveRecord) {
            $map = [
                'date' => self::TYPE_DATE,
                'datetime' => self::TYPE_DATETIME,
                'time' => self::TYPE_TIME,
                'timestamp' => self::TYPE_DATETIME
            ];

            $attributes = $owner::getTableSchema()->columns;
            foreach ($attributes as $attribute => $columnSchema) {
                if (isset($map[$columnSchema->type])) {
                    $this->attributes[$attribute] = $map[$columnSchema->type];
                }
            }
        }

        parent::attach($owner);
    }


    /**
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return $this->isBehaviorProperty($name)
            || parent::canGetProperty($name, $checkVars);
    }

    /**
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return $this->isBehaviorProperty($name)
            || parent::canSetProperty($name, $checkVars);
    }

    /**
     * TODO By default, if the owner is instance of ActiveRecord, find attributes of type timestamp, datetime, date and time
     */
    public function init()
    {
        if ($this->dateTimeClass !== Carbon::class && !is_subclass_of($this->dateTimeClass, Carbon::class)) {
            throw new InvalidConfigException('DateTimeClass must be instance of ' . Carbon::class);
        }

        parent::init();
    }

    /**
     * @param $name
     * @return bool|string
     */
    protected function isBehaviorProperty($name)
    {
        $realName = $this->realName($name);
        return
            $realName . $this->attributeSuffix === $name && isset($this->attributes[$realName])
                ? $realName
                : false;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function realName(string $name): string
    {
        if (strpos($name, $this->attributeSuffix) !== false) {
            return substr($name, 0, -(strlen($this->attributeSuffix)));
        }
        return $name;
    }
}