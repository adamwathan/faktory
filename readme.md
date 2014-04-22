# Facktory

Facktory is a tool for easily building test objects ala [FactoryGirl](https://github.com/thoughtbot/factory_girl/), but for PHP. It's still in it's early stages, but give it a go if you're interested, and open issues for the features it's missing that you think are really important.

## Installing with Composer

You can install this package via Composer by including the following in your `composer.json`:

```json
{
    "require": {
        "adamwathan/facktory": "dev-master"
    }
}
```

> Note: Since this package is still early, I haven't tagged anything at stable yet. Make sure you drop your `minimum-stability` to `dev` if you'd like to play with this before I tag a release.

### Laravel 4

If you are using Laravel 4, you can get started very quickly by registering the included service provider.

Modify the `providers` array in `app/config/app.php` to include the `FacktoryServiceProvider`:

```php
'providers' => array(
        //...
        'AdamWathan\Facktory\FacktoryServiceProvider'
    ),
```

Add the `Facktory` facade to the `aliases` array in `app/config/app.php`:

```php
'aliases' => array(
        //...
        'Facktory' => 'AdamWathan\Facktory\Facades\Facktory'
    ),
```

You can now start using Facktory by calling methods directly on the `Facktory` facade:

```php
Facktory::add('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
});
```

### Outside of Laravel 4

To use outside of Laravel 4, just instantiate a new `Facktory`. Make sure you register this as a singleton in your favorite dependency injection container, since you probably want to be using the same instance everywhere.

```php
$facktory = new AdamWathan\Facktory\Facktory;
```

> Note: When using outside of Laravel 4 and not having access to the `Facktory` facade, you will need to make sure you `use` your `$facktory` instance in any nested closures that need to generate other objects. Sucks but that's PHP.

## Defining factories

Define factories anywhere you want.

In Laravel 4, I've been creating a `factories.php` file in my tests directory and adding it to `app/bootstrap/testing.php` like so:

```php
// app/bootstrap/testing.php
require app_path().'/tests/factories.php';
```

### Basic definition

The most basic factory definition requires a class name and a closure that defines the default attributes for the factory. This will define a factory named after that class that generates instances of that same class.

```php
Facktory::add('Album', function($f) {
    $f->name = 'Diary of a madman';
    $f->release_date = new DateTime('1981-11-07');
});

Facktory::add('Song', function($f) {
    $f->name = 'Over the mountain';
    $f->length = 271;
});
```

### Named factories

Factories can also be given a name, so that you can define multiple factories for the same class that generate objects in different predefined states.

To define a named factory, pass an array in the form `[factory_name, class_name]` as the first parameter instead of just a class name.

```php
Facktory::add(['album_with_copies_sold', 'Album'], function($f) {
    $f->name = 'Diary of a madman';
    $f->release_date = new DateTime;
    $f->copies_sold = 3200000;
});
```

### Factory inheritance

You can create factories that inherit the attributes of an existing factory by nesting the definition. This allows you to define a basic factory, as well as more specific factories underneath it to generate objects in a specific state without having to redeclare the attributes that don't need to change.

```php
Facktory::add(['basic_user', 'User'], function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';

    // This will define another User factory under the name 'admin'
    // that inherits the first_name and last_name attributes from
    // the parent definition, while specifying that the 'is_admin'
    // flag should be set to true.
    $f->add('admin', function($f) {
        $f->is_admin = true;
    });
});
```

### Lazy attributes

If you don't want an attribute to be evaluated until you try to build an object, you can define that attribute as a closure.

```php
Facktory::add('User', function($f) {
    $f->username = 'john.doe';

    // This will re-evaluate every time you build an object,
    // always giving you the DateTime at the time of object
    // build instead of the DateTime at the time the factory
    // is defined.
    $f->created_at = function() {
        return new DateTime;
    };
});
```

### Dependent attributes

You can also use lazy attributes to define attributes that depend on other attributes in the factory.

```php
Facktory::add('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';

    // The current factory will be passed as the first parameter
    // to the closure, allowing you to use other attributes in
    // the factory.
    $f->full_name = function($f) {
        return "{$f->first_name} {$f->last_name}";
    };
});
```



```php
// You can lazy evaluate anything by sticking it in
// a function, including adding a related object from
// a factory that hasn't even been defined yet.
Facktory::add(['hit_song', 'Song'], function($f) {
    $f->name = 'Suicide solution';
    $f->length = 125;

    // This would throw an error
    $f->album = Facktory::build('album_with_artist');

    // But this will work
    $f->album = function() {
        return Facktory::build('album_with_artist');
    };
});

Facktory::add(['album_with_artist', 'Album'], function($f) {
    $f->name = 'Blizzard of Ozz';
    $f->artist = 'Ozzy Osbourne';
});
```

## Using factories

```php
// Create a basic instance
$album = Facktory::build('Album');

// Create a basic instance from a named factory
$album = Facktory::build('album_with_release_date');

// Create an instance and override some properties
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    ]),
]);

// Create multiple instances
$albums = Facktory::buildList('Album', 5);

// Create multiple instances with some overridden properties
$songs = Facktory::buildList('Song', 5, [ 'length' => 100 ])

// Add a nested relationship where each item is different
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => [
        Facktory::build('Song', [ 'length' => 143 ]),
        Facktory::build('Song', [ 'length' => 251 ]),
        Facktory::build('Song', [ 'length' => 167 ]),
        Facktory::build('Song', [ 'length' => 229 ]),
    ],
]);

// Add a nested relationship where each item shares the same
// properties
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => Facktory::buildList('Song', 5, [ 'length' => 100 ]
    ),
]);

// Add a nested relationship where each item is different,
// but using buildList
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => Facktory::buildList('Song', 4, [
        'length' => [143, 251, 167, 229]
    ]),
]);

// Add a nested relationship using buildList, but wrap
// it in a collection
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => function() {
        return new Collection(Facktory::buildList('Song', 4, [
            'length' => [143, 251, 167, 229]
        ]));
    }
]);
```
