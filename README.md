## Laravel Gamify  🕹 🏆

[![Latest Version on Packagist](https://img.shields.io/packagist/v/qcod/laravel-gamify.svg)](https://packagist.org/packages/qcod/laravel-gamify)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/qcod/laravel-gamify/master.svg)](https://travis-ci.org/qcod/laravel-gamify)
[![Total Downloads](https://img.shields.io/packagist/dt/qcod/laravel-gamify.svg)](https://packagist.org/packages/qcod/laravel-gamify)

Use `qcod/laravel-gamify` to quickly add reputation point &amp; badges in your Laravel app.

### Installation

**1** - You can install the package via composer:

```bash
$ composer require qcod/laravel-gamify
```

**2** - If you are installing on Laravel 5.4 or lower you will be needed to manually register Service Provider by adding it in `config/app.php` providers array.

```php
'providers' => [
    //...
    QCod\Gamify\GamifyServiceProvider::class
]
```

In Laravel 5.5 and above the service provider automatically.

**3** - Now publish the migration for gamify tables:

```
php artisan vendor:publish --provider="QCod\Gamify\GamifyServiceProvider" --tag="migrations"
```

*Note:* It will generate migration for `reputations`, `badges` and `user_badges` tables along with add reputation field migration for `users` table to store the points, you will need to run `composer require doctrine/dbal` in order to support dropping and adding columns.

```
php artisan migrate
```

You can publish the config file:
```
php artisan vendor:publish --provider="QCod\Gamify\GamifyServiceProvider" --tag="config"
```

If your payee (model who will be getting the points) model is `App\User` then you don't have to change anything in `config/gamify.php`.

### Getting Started

**1.** After package installation now add the **Gamify** trait on `App\User` model or any model who acts as **user** in your app.

```php
use QCod\Gamify\Gamify;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, Gamify;
```

## ⭐️ 👑 Reputation Point

**2.** Next step is to create a point.

```bash
php artisan gamify:point PostCreated
```

It will create a PointType class named `PostCreated` under `app/Gamify/Points/` folder.

```php
<?php

namespace App\Gamify\Points;

use QCod\Gamify\PointType;

class PostCreated extends PointType
{
    /**
     * Number of points
     *
     * @var int
     */
    public $points = 20;

    /**
     * Point constructor
     *
     * @param $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * User who will be receive points
     *
     * @return mixed
     */
    public function payee()
    {
        return $this->getSubject()->user;
    }
}
```

### Give point to User

Now in your Controller where a Post is created you can give points like this:

```php
$user = $request->user();
$post = $user->posts()->create($request->only(['title', 'body']));

// you can use helper function
givePoint(new PostCreated($post));

// or via HasReputation trait method
$user->givePoint(new PostCreated($post));
```

### Undo a given point

In some cases you would want to undo a given point, for example, a user deletes his post.

```php
// via helper function
undoPoint(new PostCreated($post));
$post->delete();

// or via HasReputation trait method
$user->undoPoint(new PostCreated($post));
$post->delete();
```

You can also pass second argument as $user in helper function `givePoint(new PostCreated($post), $user)`, default is auth()->user().

**Pro Tip 👌** You could also hook into the Eloquent model event and give point on `created` event. Similarly, `deleted` event can be used to undo the point.

### Get total reputation

To get the total user reputation you have `$user->getPoints($formatted = false)` method available. Optioally you can pass `$formatted = true` to get reputation as 1K+, 2K+ etc.

```php
// get integer point
$user->getPoints(); // 20

// formatted result
$user->getPoints(true); // if point is more than 1000 1K+
```

### Get reputation history

Since package stores all the reputation event log so you can get the history of reputation via the following relation:

```php
foreach($user->reputations as $reputation) {
    // name of the point type 
    $reputation->name
    
    // payee user
    $reputation->payee
    
    // how many points
    $reputation->point
    
    // model on which point was given 
    $reputation->subject
}
``` 

If you want to get all the points given on a `subject` model. You should define a `morphMany` relations. For example on post model.

```php
    /**
     * Get all the post's reputation.
     */
    public function reputations()
    {
        return $this->morphMany('QCod\Gamify\Reputation', 'subject');
    }
```

Now you can get all the reputation given on a `Post` using `$post->reputations`.

### Configure a Point Type

#### Point payee
In most of the case your subject model which you pass into point `new PostCreated($post)` will be related to the User via some relation.

```php
class PostCreated extends PointType
{
    public $points = 20;
    
    protected $payee = 'user';
    
    // dont need this, payee property will return subject realtion 
    // public function payee()
    // {
    //    return $this->getSubject()->user;
    // }
}
```

#### Dynamic point

If a point is calculated based on some logic you should add `getPoints()` method to do the calculation and always return an integer.

```php
class PostCreated extends PointType
{
    protected $payee = 'user';
    
    public function getPoints()
    {
        return $this->getSubject()->user->getPoint() * 10;
    }
}
```

#### Point qualifier

This is an optional method which returns boolean if its true then this point will be given else it will be ignored. 
It's will be helpful if you want to determine the qualification for point dynamically.

#### Prevent duplicate reputation

By default, you can give points multiple times for same model subject. But you can prevent it by adding the following property to the class:

```php
class PostCreated extends PointType
{
    // prevent duplicate point
    public $allowDuplicates = false;

    protected $payee = 'user';
}
```

#### Event on reputation changed

Whenever user point changes it fires `\QCod\Gamify\Events\ReputationChanged` event which has the following payload:

```php
class ReputationChanged implements ShouldBroadcast {
    
    ...
    public function __construct(Model $user, int $point, bool $increment)
    {
        $this->user = $user;
        $this->point = $point;
        $this->increment = $increment;
    }
}
```

This event also broadcast in configured channel name so you can listen to it from your frontend via socket to live update reputation points.

## 🏆 🏅 Achievement Badges

Similar to Point type you have badges. They can be given to users based on rank or any other criteria. You should define badge level in `config/gamify.php`.

```php
// All the levels for badge
'badge_levels' => [
    'beginner' => 1,
    'intermediate' => 2,
    'advanced' => 3,
],

// Default level
'badge_default_level' => 1
```

Badge levels are stored as `tinyint` so keep the value as an integer value. It will be faster to do the sorting when needed. 

### Create a Badge

To generate a badge you can run following provided command:

```bash
php artisan gamify:badge FirstContribution
```

It will create a BadgeType class named `FirstContribution` under `app/Gamify/Badges/` folder.

```php
<?php

namespace App\Gamify\Badges;

use QCod\Gamify\BadgeType;

class FirstContribution extends BadgeType
{
    /**
     * Description for badge
     *
     * @var string
     */
    protected $description = '';

    /**
     * Check is user qualifies for badge
     *
     * @param $user
     * @return bool
     */
    public function islevelArchived($user)
    {
        return $user->getPoints() >= 1000;
    }
}
```

As you can see this badge has a `$description` field and a `islevelArchived($user)` method. 
Gamify package will listen for any change in reputation point and it will run the user against all the available badges and assign all the badges user is qualified.

#### Change badge name

By default, badge name will be a pretty version on the badge class name. In the above case it will be `First Contribution`. 
You can change it by adding a `$name` property in class or you can override `getName()` method if you want to name it dynamically.

#### Change badge icon

Similar to name you can change it by `$icon` property or by `getIcon()` method. When you define icon on the class you need to specify full path with extension. 
`config/gamify.php` folder `badge_icon_folder` and `badge_icon_extension` won't be used.

#### Change badge level

You have same `$level` property or by `getLevel()` method to change it.
Its like category of badges, all badges are defined in `config/gamify.php` as `badge_levels`. If none is specified then `badge_default_level` will be used from config.

**Warning ⚠️** Don't forget to clear the cache whenever you make any changes add or remove badges by running `php artisan cache:forget gamify.badges.all`. ⚠️ 

#### Get badges of user
You can get a users badges by calling `$user->badges` which will return collection of badges for a user.

### Use without Badge

If your app doesn't need **Badges** you should just use `HasReputations` trait instead of `Gamify`.  

### Use without reputation history

If you dont need to maintain the history of all the point user has rewarded and you just want to increment and decrement reputation, you should use following method:

```php
// to add point
$user->addPoint($point = 1);

// to reduce point
$user->reducePoint($point = 1);

// to reset point back to zero
$user->resetPoint();
```

You dont need to generate point class for this.  

### Config Gamify

```php
<?php

return [
    // Model which will be having points, generally it will be User
    'payee_model' => '\App\User',

    // Reputation model
    'reputation_model' => '\QCod\Gamify\Reputation',

    // Allow duplicate reputation points
    'allow_reputation_duplicate' => true,

    // Broadcast on private channel
    'broadcast_on_private_channel' => true,

    // Channel name prefix, user id will be suffixed
    'channel_name' => 'user.reputation.',

    // Badge model
    'badge_model' => '\QCod\Gamify\Badge',

    // Where all badges icon stored
    'badge_icon_folder' => 'images/badges/',

    // Extention of badge icons
    'badge_icon_extension' => '.svg',

    // All the levels for badge
    'badge_levels' => [
        'beginner' => 1,
        'intermediate' => 2,
        'advanced' => 3,
    ],

    // Default level
    'badge_default_level' => 1
];
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

### Testing

The package contains some integration/smoke tests, set up with Orchestra. The tests can be run via phpunit.

```bash
$ composer test
```

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email saquibweb@gmail.com instead of using the issue tracker.

### Credits

- [Mohd Saqueib Ansari](https://github.com/saqueib) (Author)

### About QCode.in

QCode.in (https://www.qcode.in) is a blog by [Saqueib](https://github.com/saqueib) which covers All about Full Stack Web Development.

### License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
