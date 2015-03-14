# Faktory

[![Code Climate](https://codeclimate.com/github/adamwathan/faktory/badges/gpa.svg)](https://codeclimate.com/github/adamwathan/faktory)
[![Travis CI](https://travis-ci.org/adamwathan/faktory.svg)](https://travis-ci.org/adamwathan/faktory)

Faktory is a tool for easily building test objects ala [FactoryGirl](https://github.com/thoughtbot/factory_girl/), but for PHP. It's still in it's early stages, but give it a go if you're interested, and open issues for the features it's missing that you think are really important.

## Installing with Composer

You can install this package via Composer by including the following in your `composer.json`:

```json
{
    "require": {
        "adamwathan/faktory": "dev-master"
    }
}
```

> Note: Since this package is still early, I haven't tagged anything at stable yet. Make sure you drop your `minimum-stability` to `dev` if you'd like to play with this before I tag a release.

### Laravel 4

If you are using Laravel 4, you can get started very quickly by registering the included service provider.

Modify the `providers` array in `app/config/app.php` to include the `FaktoryServiceProvider`:

```php
'providers' => array(
        //...
        'AdamWathan\Faktory\FaktoryServiceProvider'
    ),
```

Add the `Faktory` facade to the `aliases` array in `app/config/app.php`:

```php
'aliases' => array(
        //...
        'Faktory' => 'AdamWathan\Faktory\Facades\Faktory'
    ),
```

You can now start using Faktory by calling methods directly on the `Faktory` facade:

```php
Faktory::define('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
});
```

### Outside of Laravel 4

To use outside of Laravel 4, just instantiate a new `Faktory`. Make sure you register this as a singleton in your favorite dependency injection container, since you probably want to be using the same instance everywhere.

```php
$faktory = new AdamWathan\Faktory\Faktory;
```

> Note: When using outside of Laravel 4 and not having access to the `Faktory` facade, you will need to make sure you `use` your `$faktory` instance in any nested closures that need to generate other objects. Sucks but that's PHP.

## Using Faktory

### Defining factories

Define factories anywhere you want.

In Laravel 4, I've been creating a `factories.php` file in my tests directory and adding it to `app/bootstrap/testing.php` like so:

```php
// app/bootstrap/testing.php
require app_path().'/tests/factories.php';
```

#### Basic definition

The most basic factory definition requires a class name and a closure that defines the default attributes for the factory. This will define a factory named after that class that generates instances of that same class.

```php
Faktory::define('Album', function($f) {
    $f->name = 'Diary of a madman';
    $f->release_date = new DateTime('1981-11-07');
});

Faktory::define('Song', function($f) {
    $f->name = 'Over the mountain';
    $f->length = 271;
});
```

### Using factories

Once you have your factories defined, you can very easily generate objects for your tests.

Objects can be generated using one of two different build strategies.

- `build` creates objects in memory only, without persisting them.
- `create` creates objects and persists them to whatever database you have set up in your testing environment.

> Note: The `create` strategy is meant for Laravel 4's Eloquent ORM, but as long as your objects implement a `save()` method and a `getKey()` method to retrieve the object's ID, it should work outside of Eloquent.

To generate an object, simply call `build` or `create` and pass in the name of the factory you want to generate the object from.

```php
// Returns an Album object with the default attribute values
$album = Faktory::build('Album');
$album->name;
// 'Diary of a madman'
$album->release_date;
// '1981-11-07'


// Create a basic instance and persist it to
// the database
$album = Faktory::create('Album');
$album->id
// 1
```

#### Overriding attributes

The real benefit of using these factories appears when you are writing a test that requires your objects to satisfy some precondition, but you don't really care about the rest of the attributes.

You can specify the values you need for the attributes that matter for the test, and let the factory handle filling out the attributes you don't care about with default data so that the object is in a valid state.

If you just need to change some simple attributes to static values, you can just pass an array of attribute overrides as a second argument:

```php
// Create an instance and override some properties
$album = Faktory::build('Album', ['name' => 'Bark at the moon']),
]);

$album->name;
// 'Bark at the moon'
$album->release_date;
// '1981-11-07'
```

If you need to do something trickier, you can pass in a closure that provides all of the same functionality you get when actually defining the factory. This is most useful when working with relationships:

```php
// Create an instance and override some properties
$album = Faktory::build('Album', function($album) {
    $album->name => 'Bark at the moon';
    $album->songs->quantity(4)->attributes(['length' => 267]);
});

$album->name;
// 'Bark at the moon'
$album->songs->count();
// 4
$album->songs[0]->length;
// 267
```

### Named factories

Factories can also be given a name, so that you can define multiple factories for the same class that generate objects in different predefined states.

To define a named factory, pass an array in the form `[factory_name, class_name]` as the first parameter instead of just a class name.

```php
Faktory::define(['album_with_copies_sold', 'Album'], function($f) {
    $f->name = 'Diary of a madman';
    $f->release_date = '1981-11-07';
    $f->copies_sold = 3200000;
});


$album = Faktory::build('album_with_copies_sold');

get_class($album);
// 'Album'
$album->name;
// 'Diary of a madman'
$album->release_date;
// '1981-11-07'
$album->copies_sold;
// 3200000
```

### Factory inheritance

You can create factories that inherit the attributes of an existing factory by nesting the definition. This allows you to define a basic factory, as well as more specific factories underneath it to generate objects in a specific state without having to redeclare the attributes that don't need to change.

```php
Faktory::define(['basic_user', 'User'], function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
    $f->is_admin = false;

    $f->define('admin', function($f) {
        $f->is_admin = true;
    });
});


$user = Faktory::build('admin');

$user->first_name;
// 'John'
$user->last_name;
// 'Doe'
$user->is_admin;
// true
```

### Lazy attributes

If you don't want an attribute to be evaluated until you try to build an object, you can define that attribute as a closure.

```php
Faktory::define('User', function($f) {
    $f->username = 'john.doe';

    $f->created_at = function() {
        return new DateTime;
    };
});


$user1 = Faktory::build('User');
$user1->created_at;
// '2014-04-22 14:10:05'

sleep(7);

$user2 = Faktory::build('User');
$user2->created_at;
// '2014-04-22 14:10:12'
```

### Dependent attributes

You can also use lazy attributes to define attributes that depend on other attributes in the factory.

```php
Faktory::define('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
    $f->email = function($f) {
        return "{$f->first_name}.{$f->last_name}@example.com";
    };
});


$user = Faktory::build('User');
$user->first_name;
// 'John'
$user->last_name;
// 'Doe'
$user->email;
// 'John.Doe@example.com'

$user = Faktory::build('User', ['first_name' => 'Bob']);
$user->first_name;
// 'Bob'
$user->last_name;
// 'Doe'
$user->email;
// 'Bob.Doe@example.com'
```

### Unique attributes

Lazy attributes to the rescue again. The closure also takes an autoincrementing integer as it's second parameter, which is really handy for ensuring that a field value is unique.

```php
Faktory::define('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
    $f->email = function($f, $i) {
        return "example{$i}@example.com";
    };
});


$user1 = Faktory::build('User');
$user1->email;
// 'example1@example.com'

$user2 = Faktory::build('User');
$user2->email;
// 'example2@example.com'
```

### Defining relationships

Faktory lets you easily define relationships between objects.

Currently, there is support for `belongsTo`, `hasOne`, and `hasMany` relationships.

#### Belongs to

Define a `belongsTo` relationship by assigning a `belongsTo` call to an attribute.

`belongsTo()` takes the name of the factory that should be used to generate the related object as the first argument, the name of the foreign key column as the second argument, and an optional array of override attributes as the third argument.

```php
$faktory->define(['song_with_album', 'Song'], function($f) {
    $f->name = 'Concatenation';
    $f->length = 257;
    $f->album = $f->belongsTo('album', 'album_id', [
        'name' => 'Chaosphere',
    ]);
});

$faktory->define(['album', 'Album'], function($f) {
    $f->name = 'Destroy Erase Improve';
});


// Build the objects in memory without persisting to the database
$song = Faktory::build('song_with_album');
$song->album;
// object(Album)(
//    'name' => 'Destroy Erase Improve'
// )
$song->album_id;
// NULL


// Save the objects to the database and set up the correct
// foreign key associations
$song = Faktory::create('song_with_album');
$song->album_id;
// 1

Album::find(1);
// object(Album)(
//    'name' => 'Destroy Erase Improve'
// )
```

#### Has one

Define a `hasOne` relationship by assigning a `hasOne` call to an attribute.

`hasOne()` takes the name of the factory that should be used to generate the related object as the first argument, the name of the foreign key column (on the related object) as the second argument, and an optional array of override attributes as the third argument.

```php
$faktory->define(['user_with_profile', 'User'], function($f) {
    $f->username = 'johndoe';
    $f->password = 'top-secret';
    $f->profile = $f->hasOne('profile', 'user_id');
});

$faktory->define(['profile', 'Profile'], function($f) {
    $f->email = 'johndoe@example.com';
});


// Build the objects in memory without persisting to the database
$user = Faktory::build('user_with_profile');
$user->profile;
// object(Profile)(
//    'email' => 'johndoe@example.com'
// )


// Save the objects to the database and set up the correct
// foreign key associations
$user = Faktory::create('user_with_profile');
$user->id;
// 1

Profile::first();
// object(Album)(
//    'user_id' => 1,
//    'email' => 'johndoe@example.com'
// )
```

#### Has many

Define a `hasMany` relationship by assigning a `hasMany` call to an attribute.

`hasMany()` takes the name of the factory that should be used to generate the related objects as the first argument, the name of the foreign key column (on the related object) as the second argument, the number of objects to generate as the third argument, and an optional array of override attributes as the final argument.

```php
$faktory->define(['album_with_songs', 'Album'], function($f) {
    $f->name = 'Master of Puppets';
    $f->release_date = new DateTime('1986-02-24');
    $f->songs = $f->hasMany('song', 'album_id', 8);
});

$faktory->define(['song', 'Song'], function($f) {
    $f->title = 'The Thing That Should Not Be';
    $f->length = 397;
});
```

#### Relationships and build strategies

Relationships are handled differently by each build strategy.

When using the `build` strategy, the related object(s) will be available directly as a property on the base object.

When using the `create` strategy, the related object(s) will be persisted to the database with the foreign key attribute set to match the ID of the base object, and nothing will actually be set on the actual attribute itself, allowing you to retrieve the related object through the methods you've actually defined in the base object's class.

#### Overriding relationship attributes

If you need to override attributes on a relationship when building or creating an object, you can do so by manipulating the actual relationship attribute itself.

```php
// Define the factories
$faktory->define(['song_with_album', 'Song'], function($f) {
    $f->name = 'Concatenation';
    $f->length = 257;
    $f->album = $f->belongsTo('album', 'album_id');
});
$faktory->define(['album', 'Album'], function($f) {
    $f->name = 'Destroy Erase Improve';
    $f->release_date = new DateTime('1995-07-25');
});


// Build a song but override the album name
$song = Faktory::build('song_with_album', function($song) {
    $song->album->name = 'Chaosphere';
});
$song->album;
// object(Album)(
//    'name' => 'Chaosphere'
// )

// Build a song but override a couple attributes at once
$song = Faktory::build('song_with_album', function($song) {
    $song->album->attributes([
        'name' => 'Chaosphere',
        'release_date' => new DateTime('1998-11-10'),
    ]);
});
$song->album;
// object(Album)(
//    'name' => 'Chaosphere'
// )
$song->album->release_date;
// '1998-11-10'
```

### Building multiple instances at once

You can use `buildList` and `createList` to generate multiple objects at once:

```php
// Create multiple instances
$albums = Faktory::buildList('Album', 5);

// Create multiple instances with some overridden properties
$songs = Faktory::buildList('Song', 5, [ 'length' => 100 ])
$songs[0]->length;
// 100
// ...
$songs[4]->length;
// 100

// Add a nested relationship where each item is different
$album = Faktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => [
        Faktory::build('Song', [ 'length' => 143 ]),
        Faktory::build('Song', [ 'length' => 251 ]),
        Faktory::build('Song', [ 'length' => 167 ]),
        Faktory::build('Song', [ 'length' => 229 ]),
    ],
]);

// Add a nested relationship where each item shares the same
// properties
$album = Faktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => Faktory::buildList('Song', 5, [ 'length' => 100 ]
    ),
]);

// Add a nested relationship where each item is different,
// but using buildList
$album = Faktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => Faktory::buildList('Song', 4, [
        'length' => [143, 251, 167, 229]
    ]),
]);

// Add a nested relationship using buildList, but wrap
// it in a collection
$album = Faktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => function() {
        return new Collection(Faktory::buildList('Song', 4, [
            'length' => [143, 251, 167, 229]
        ]));
    }
]);
```
