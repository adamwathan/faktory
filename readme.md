# Facktory

## Example Usage

```php
// factories.php
use AdamWathan\Facktory\Facktory;

Facktory::add('Album', function($f) {
	$f->name = 'Diary of a madman';
	$f->release_date = new DateTime;
});

Facktory::add('Song', function($f) {
	$f->name = 'Over the mountain';
	$f->length = 150;
});

// AlbumTest.php
public function testCanDetermineTotalAlbumLength()
{
    $album = Facktory::build('Album', [
        'name' => 'Bark at the moon',
        'songs' => new Collection([
            Facktory::build('Song', [ 'length' => 143 ]),
            Facktory::build('Song', [ 'length' => 251 ]),
            Facktory::build('Song', [ 'length' => 167 ]),
            Facktory::build('Song', [ 'length' => 229 ]),
        ]),
    ]);

    $expected = 790;
    $this->assertSame($expected, $album->getTotalLength());
}
```
