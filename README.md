# DateTime behaviorfor Yii2

This extension provides a package to help working with datetime objects in (most often) Active Record objects.

It provides magic getters and setters to work with DateTime objects instead of having to work with strings. 
It does this with proxies to keep rules (i.e.) working on the standard attributes.

```bash
$ composer require jc-it/yii2-datetime-behavior
```

or add

```
"jc-it/yii2-datetime-behavior": "^<latest version>"
```

to the `require` section of your `composer.json` file.

## Configuring

In a model:

```php
/**
 * @return array
 */
public function behaviors(): array
{
    return ArrayHelper::merge(
        parent::behaviors(),
        [
            DateTimeBehavior::class => [
                'class' => DateTimeBehavior::class
            ],
        ]
    );
}
```

For an Active Record model it will automatically detect date/time/datetime/timestamp fields and will apply the behavior to them.

The full configuration:

```php
/**
 * @return array
 */
public function behaviors(): array
{
    return ArrayHelper::merge(
        parent::behaviors(),
        [
            DateTimeBehavior::class => [
                'class' => DateTimeBehavior::class,
                'attributes' => [ // Map of attribute to type.
                    'date_attribute' => DateTimeBehavior::TYPE_DATE,
                    'datetime_attribute' => DateTimeBehavior::TYPE_DATETIME,
                    'time_attribute' => DateTimeBehavior::TYPE_TIME,
                ],
                'attributeSuffix' => 'Object', // Suffix that will be used to detect if the behavior must be triggered.
                'attributeTimezone' => 'UTC', //When storing datetime values in timestamp fields this should default timezone of your database.
                'dateTimeClass' => Carbon::class, //The class of the datetime objects to be returned. Must extend from Carbon.
            ],
        ]
    );
}
```

## Usage

If the attributes that are affected by the behavior are accessed with the suffix an object of the configured class will be returned.

```php
// Event has attribute from and until which are timestamps in the database
// Assuming in timezone Europe/Amsterdam

$event = Event::find()->one();
var_dump($event->from);
var_dump($event->fromObject);

// Will output

string '2019-01-01 12:00:00' (length=19)

object(Carbon\Carbon)
  public 'date' => string '2019-01-01 13:00:00.000000' (length=26)
  public 'timezone_type' => int 3
  public 'timezone' => string 'Europe/Amsterdam' (length=16)
```

## Credits
- [Joey Claessen](https://github.com/joester89)

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/jc-it/yii2-datetime-behavior/blob/master/LICENSE) for more information.