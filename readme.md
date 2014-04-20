# Facktory

## Example Usage

### Defining factories

```php
// factories.php
use AdamWathan\Facktory\Facktory;

// Specify just a class name to create a factory
// for that class that is named after that class
Facktory::add('Album', function($f) {
    $f->name = 'Diary of a madman';
});

Facktory::add('Song', function($f) {
    $f->name = 'Over the mountain';
    $f->length = 150;
});


// Specify a custom name and target class by
// passing an array
Facktory::add(['album_with_release_date', 'Album'], function($f) {
    $f->name = 'Diary of a madman';
    $f->release_date = new DateTime;
});


// Inherit properties from an existing factory
// by nesting another definition inside of it
Facktory::add('Album', function($f) {
    $f->name = 'Diary of a madman';

    $f->add('album_with_release_date', function($f) {
        $f->name = 'Diary of a madman';
        $f->release_date = new DateTime;
    });
});



// Fields that need to be unique can be defined
// as functions that take an instance of the
// current factory and an autoincrementing
// index
Facktory::add('User', function($f) {
    $f->username = function($f, $i) {
        return 'johndoe'.$i;
    };
});



// Fields that depend on other fields can be
// defined as functions that take an instance
// of the current factory and an autoincrementing
// index
Facktory::add('User', function($f) {
    $f->first_name = 'John';
    $f->last_name = 'Doe';
    $f->full_name = function($f, $i) {
        return "{$f->first_name} {$f->last_name}";
    };
});
```

### Using factories

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

// Add a nested relationship
$album = Facktory::build('Album', [
    'name' => 'Bark at the moon',
    'songs' => new Collection([
        Facktory::build('Song', [ 'length' => 143 ]),
        Facktory::build('Song', [ 'length' => 251 ]),
        Facktory::build('Song', [ 'length' => 167 ]),
        Facktory::build('Song', [ 'length' => 229 ]),
    ]),
]);
```
