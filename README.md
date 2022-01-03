# Watchable
Watchable is a Laravel package where you can easily pick up laravel model event and activity log inside your application. 

```
use Shipu\Watchable\Traits\WatchableTrait;
```

```
onModelCreating
onModelCreated
onModelUpdating
onModelUpdated
```


The Package stores all activity in the `activity_logs` table.
Here's a demo of how you can use it:
```php
activity()->log('Look, I logged something');
```
You can retrieve all activity using the `Shipu\Watchable\Models\Activity` model.
```
Activity::all();
```
Here's a more advanced example:
```php
activity()
   ->on($anEloquentModel)
   ->data($storeWhatYouWantTo)
   ->log('Look, I logged something');
   
$lastLoggedActivity = Activity::all()->last();

$lastLoggedActivity->model; //returns an instance of an eloquent model
$lastLoggedActivity->causer; //returns an instance of your user model
$lastLoggedActivity->remarks; //returns 'Look, I logged something'
```
Here's an example on [event logging](https://docs.spatie.be/laravel-activitylog/v2/advanced-usage/logging-model-events).

```php
$user->name = 'updated name';
$user->save();

//updating the newsItem will cause the logging of an activity
$activity = Activity::all()->last();

$activity->remarks; //returns 'User Updated'
$activity->model; //returns the instance of NewsItem that was created
```

Calling `$activity->changes` will return this array:

```php
[
   'new' => [
        'name' => 'updated name',
        'text' => 'Lorum',
    ],
    'old' => [
        'name' => 'original name',
        'text' => 'Lorum',
    ],
];
```

## Installation

You can install the package via composer:
``` bash
composer require shipu/watchable
```
You can optionally publish the config file with:
```bash
php artisan vendor:publish --provider="Shipu\Watchable\WatchableServiceProvider" --tag="shipu-watchable-config"
```
This is the contents of the published config file:
```php
return [
    'audit_columns' => [
        'creator_column' => 'creator',
        'editor_column' => 'editor',
        'default_active' => false,
    ],
    'activity_log' => [
        'model' => \Shipu\Watchable\Models\Activity::class
    ]
];
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Shipu\Watchable\WatchableServiceProvider" --tag="shipu-watchable-migrations"
```
*Note*: The default migration assumes you are using integers for your model IDs. If you are using UUIDs, or some other format, adjust the format of the subject_id and causer_id fields in the published migration before continuing.

After publishing the migration you can create the `activity_logs` table by running the migrations:
```bash
php artisan migrate
```