<?php

/**
 * Factory definitions
 */

// Basic definition
// Pretty fine with this, the first array parameter thing
// is kind of weird but I don't have a better answer
Facktory::add(['song', 'Song'], function($f) {
    $f->name = 'Concatenation';
    $f->length = 257;
});


Facktory::add(['album_with_5_songs', 'Album'], function($f) {
    $f->name = 'Chaosphere';
    $f->release_date = new DateTime;
    $f->songs = $f->hasMany('song', 'album_id', 5, ['length' => 100]);
});


/**
 * Using the Factory
 */

// Option 1: Array of attribute overrides
// Pros:
// - Simple
// Cons:
// - Relationships are gross
$album = Facktory::create('album_with_5_songs', [
    'release_date' => new DateTime('1998-11-10');
    'songs' => ['count' => 3, 'attributes' => ['length' => 150]],
    ]);

// Option 2: Callback with complete override for relationships
// Pros:
// - Consistent with factory declaration
// Cons:
// - Pretty verbose if you are just overriding one attribute
// - Crappy to have to redeclare an entire relationship
$album = Facktory::create('album_with_5_songs', function($f) {
    $f->release_date = new DateTime('1998-11-10');
    $f->songs = $f->hasMany('song', 'album_id', 2, ['length' => 150]);
});

// Option 3: Callback where you chain changes to the relationship
// Pros:
// - More DRY, only need to call methods to change what you need to
// Cons:
// - Introduces totally new syntax, sort of breaks the symmetry
//   since there is no longer an assignment happening.
$album = Facktory::create('album_with_5_songs', function($f) {
    $f->release_date = new DateTime('1998-11-10');

    $f->songs->amount(2)->attributes(['length' => 150]);
    // or
    $f->songs->amount(2)->length(150);
    $f->songs->amount(2)->length([150, 200]);
});
