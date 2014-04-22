<?php

use AdamWathan\Facktory\Facktory;

class FacktoryTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_define_basic_factory()
    {
        $facktory = new Facktory;
        $facktory->add('Album', function($f) {
            $f->name = 'Bark at the moon';
        });
        $album = $facktory->build('Album');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
    }

    public function test_can_override_attribute()
    {
        $facktory = new Facktory;
        $facktory->add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $facktory->build('Album', [
            'name' => 'Diary of a madman'
            ]);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $facktory->build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name_and_override_attribute()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $facktory->build('album_with_artist', [
            'artist' => 'Randy Rhoads'
            ]);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_inherit_attributes()
    {
        $facktory = new Facktory;
        $facktory->add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $facktory->build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_and_override_attribute()
    {
        $facktory = new Facktory;
        $facktory->add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $facktory->build('album_with_artist', ['artist' => 'Randy Rhoads']);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_override_parent_attribute()
    {
        $facktory = new Facktory;
        $facktory->add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $facktory->build('album_with_artist', ['name' => 'Diary of a madman']);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_inside_named_factory()
    {
        $facktory = new Facktory;
        $facktory->add(['basic_album', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $facktory->build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_override_parent_attribute_with_default_attribute_in_nested_factory()
    {
        $facktory = new Facktory;
        $facktory->add(['basic_album', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->add('album_by_black_sabbath', function($f) {
                $f->artist = 'Black Sabbath';
            });
        });
        $album = $facktory->build('album_by_black_sabbath');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Black Sabbath', $album->artist);
    }

    public function test_can_add_calculated_attributes()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->display_title = function($f) {
                return "{$f->artist} - {$f->name}";
            };
        });
        $album = $facktory->build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
        $this->assertSame('Ozzy Osbourne - Bark at the moon', $album->display_title);
    }

    public function test_can_add_sequenced_attribute()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->id = function($f, $i) {
                return $i;
            };
        });
        $album1 = $facktory->build('album_with_artist');
        $album2 = $facktory->build('album_with_artist');

        $this->assertInstanceOf('Album', $album1);
        $this->assertSame('Bark at the moon', $album1->name);
        $this->assertSame('Ozzy Osbourne', $album1->artist);
        $this->assertSame(1, $album1->id);

        $this->assertInstanceOf('Album', $album2);
        $this->assertSame('Bark at the moon', $album2->name);
        $this->assertSame('Ozzy Osbourne', $album2->artist);
        $this->assertSame(2, $album2->id);
    }

    public function test_can_build_list_of_objects()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $facktory->buildList('album_with_artist', 5);

        $this->assertSame(5, count($albums));
        foreach ($albums as $album) {
            $this->assertInstanceOf('Album', $album);
            $this->assertSame('Bark at the moon', $album->name);
            $this->assertSame('Ozzy Osbourne', $album->artist);
        }
    }

    public function test_can_can_override_attribute_when_building_list()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $facktory->buildList('album_with_artist', 5, [
            'artist' => 'Dio'
            ]);

        $this->assertSame(5, count($albums));
        foreach ($albums as $album) {
            $this->assertInstanceOf('Album', $album);
            $this->assertSame('Bark at the moon', $album->name);
            $this->assertSame('Dio', $album->artist);
        }
    }

    public function test_can_can_override_attributes_independently_when_building_list()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $facktory->buildList('album_with_artist', 5, [
            'artist' => [
            'Dio',
            'Black Sabbath',
            'Diamondhead',
            'Iron Maiden',
            'Judas Priest'
            ]
            ]);

        $this->assertSame(5, count($albums));
        $this->assertInstanceOf('Album', $albums[0]);
        $this->assertSame('Bark at the moon', $albums[0]->name);
        $this->assertSame('Dio', $albums[0]->artist);

        $this->assertInstanceOf('Album', $albums[1]);
        $this->assertSame('Bark at the moon', $albums[1]->name);
        $this->assertSame('Black Sabbath', $albums[1]->artist);

        $this->assertInstanceOf('Album', $albums[2]);
        $this->assertSame('Bark at the moon', $albums[2]->name);
        $this->assertSame('Diamondhead', $albums[2]->artist);

        $this->assertInstanceOf('Album', $albums[3]);
        $this->assertSame('Bark at the moon', $albums[3]->name);
        $this->assertSame('Iron Maiden', $albums[3]->artist);

        $this->assertInstanceOf('Album', $albums[4]);
        $this->assertSame('Bark at the moon', $albums[4]->name);
        $this->assertSame('Judas Priest', $albums[4]->artist);
    }

    public function test_can_can_override_attributes_independently_and_as_a_group_when_building_list()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->release_date = '1983-11-15';
        });
        $albums = $facktory->buildList('album_with_artist', 3, [
            'artist' => [
            'Dio',
            'Black Sabbath',
            'Diamondhead',
            'Iron Maiden',
            'Judas Priest'
            ],
            'release_date' => '2001-05-06'
            ]);

        $this->assertSame(3, count($albums));
        $this->assertInstanceOf('Album', $albums[0]);
        $this->assertSame('Bark at the moon', $albums[0]->name);
        $this->assertSame('Dio', $albums[0]->artist);
        $this->assertSame('2001-05-06', $albums[0]->release_date);

        $this->assertInstanceOf('Album', $albums[1]);
        $this->assertSame('Bark at the moon', $albums[1]->name);
        $this->assertSame('Black Sabbath', $albums[1]->artist);
        $this->assertSame('2001-05-06', $albums[1]->release_date);

        $this->assertInstanceOf('Album', $albums[2]);
        $this->assertSame('Bark at the moon', $albums[2]->name);
        $this->assertSame('Diamondhead', $albums[2]->artist);
        $this->assertSame('2001-05-06', $albums[2]->release_date);
    }

    public function test_can_lazy_evaluate_related_class_before_defining_related_factory()
    {
        $facktory = new Facktory;
        $facktory->add(['hit_song', 'Song'], function($f) use ($facktory) {
            $f->name = 'Suicide solution';
            $f->length = 125;
            $f->album = function() use ($facktory) {
                return $facktory->build('album_with_artist');
            };
        });

        $facktory->add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Blizzard of Ozz';
            $f->artist = 'Ozzy Osbourne';
        });

        $song = $facktory->build('hit_song');

        $this->assertInstanceOf('Song', $song);
        $this->assertSame('Blizzard of Ozz', $song->album->name);
        $this->assertSame('Ozzy Osbourne', $song->album->artist);
    }

    public function test_can_use_closures_as_overrides()
    {
        $facktory = new Facktory;
        $facktory->add(['hit_song', 'Song'], function($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
        });

        $song = $facktory->build('hit_song', [
            'length' => function() {
                return 50;
            }
            ]);

        $this->assertSame(50, $song->length);
    }

    public function test_closure_overrides_still_receive_params()
    {
        $facktory = new Facktory;
        $facktory->add(['hit_song', 'Song'], function($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
        });

        $song = $facktory->build('hit_song', [
            'length' => function($f, $i) {
                return $f->name . $i;
            }
            ]);

        $this->assertSame('Suicide solution1', $song->length);
    }

    /**
     * This would throw an error if any database access was attempted.
     * Weird test with no assertion, but it will catch issues if a
     * refactoring causes the build method to hit the database on a
     * relationship call.
     */
    public function test_relationship_methods_are_ignored_on_build()
    {
        $facktory = new Facktory;
        $facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->hasMany('song', 'album_id', 5);
        });
        $facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $facktory->build('album_with_5_songs');
    }
}

class Album {}

class Song {}
