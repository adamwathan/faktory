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
    $f->email = function($f) {
        return "{$f->first_name}.{$f->last_name}@example.com";
    };
});
```

### Unique attributes

Lazy attributes to the rescue again. The closure also takes an autoincrementing integer as it's second parameter, which is really handy for ensuring that a field value is unique.

```php
Facktory::add('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';

    // First generated object will be example1@example.com,
    // second will be example2@example.com, etc.
    $f->email = function($f, $i) {
        return "example{$i}@example.com";
    };
});
```

### Defining relationships

Facktory lets you easily define relationships between objects.

Currently, there is support for `belongsTo`, `hasOne`, and `hasMany` relationships.

#### Belongs to

Define a `belongsTo` relationship by assigning a `belongsTo` call to an attribute.

`belongsTo()` takes the name of the factory that should be used to generate the related object as the first argument, the name of the foreign key column as the second argument, and an optional array of override attributes as the third argument.

```php
$facktory->add(['song_with_album', 'Song'], function($f) {
    $f->name = 'Concatenation';
    $f->length = 257;
    $f->album = $f->belongsTo('album', 'album_id', [
        'name' => 'Chaosphere',
    ]);
});

$facktory->add(['album', 'Album'], function($f) {
    $f->name = 'Destroy Erase Improve';
    $f->release_date = new DateTime;
});
```

#### Has one

Define a `hasOne` relationship by assigning a `hasOne` call to an attribute.

`hasOne()` takes the name of the factory that should be used to generate the related object as the first argument, the name of the foreign key column (on the related object) as the second argument, and an optional array of override attributes as the third argument.

```php
$facktory->add(['user_with_profile', 'User'], function($f) {
    $f->username = 'johndoe';
    $f->password = 'top-secret';
    $f->profile = $f->hasOne('profile', 'user_id');
});

$facktory->add(['profile', 'Profile'], function($f) {
    $f->email = 'johndoe@example.com';
    $f->date_of_birth = new DateTime('1985-01-01');
});
```

#### Has many

Define a `hasMany` relationship by assigning a `hasMany` call to an attribute.

`hasMany()` takes the name of the factory that should be used to generate the related objects as the first argument, the name of the foreign key column (on the related object) as the second argument, the number of objects to generate as the third argument, and an optional array of override attributes as the final argument.

```php
$facktory->add(['album_with_songs', 'Album'], function($f) {
    $f->name = 'Master of Puppets';
    $f->release_date = new DateTime('1986-02-24');
    $f->songs = $f->hasMany('song', 'album_id', 8);
});

$facktory->add(['song', 'Song'], function($f) {
    $f->title = 'The Thing That Should Not Be';
    $f->length = 397;
});
```

#### Relationships and build strategies

Relationships are handled differently by each build strategy.

When using the `build` strategy, the related object(s) will be available directly as a property on the base object.

When using the `create` strategy, the related object(s) will be persisted to the database with the foreign key attribute set to match the ID of the base object, and nothing will actually be set on the actual attribute itself, allowing you to retrieve the related object through the methods you've actually defined in the base object's class.

## Using factories

Once you have your factories defined, you can very easily generate objects for your tests.

Objects can be generated using one of two different build strategies.

- `build` creates objects in memory only, without persisting them.
- `create` creates objects and persists them to whatever database you have set up in your testing environment.

> Note: The `create` strategy is meant for Laravel 4's Eloquent ORM, but as long as your objects implement a `save()` method, it should work outside of Eloquent.

### Basic usage

To generate an object, simply call `build` or `create` and pass in the name of the factory you want to generate the object from.

```php
// Returns an Album object with the default attribute values
$album = Facktory::build('Album');

// Create a basic instance from a named factory and save it
$album = Facktory::create('album_with_release_date');
```

You can optionally pass an array of attribute overrides as a second argument:

```php
// Create an instance and override some properties
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    ]),
]);
```

### Building multiple instances at once

You can use `buildList` and `createList` to generate multiple objects at once:

```php
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
