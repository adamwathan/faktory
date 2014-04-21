<?php

use AdamWathan\Facktory\Facktory;

class FacktoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Facktory::clear();
    }

    public function test_can_define_basic_factory()
    {
        Facktory::add('Album', function($f) {
            $f->name = 'Bark at the moon';
        });
        $album = Facktory::build('Album');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
    }

    public function test_can_override_attribute()
    {
        Facktory::add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = Facktory::build('Album', [
            'name' => 'Diary of a madman'
        ]);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name()
    {
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = Facktory::build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name_and_override_attribute()
    {
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = Facktory::build('album_with_artist', [
            'artist' => 'Randy Rhoads'
            ]);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_inherit_attributes()
    {
        Facktory::add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = Facktory::build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_and_override_attribute()
    {
        Facktory::add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = Facktory::build('album_with_artist', ['artist' => 'Randy Rhoads']);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_override_parent_attribute()
    {
        Facktory::add('Album', function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = Facktory::build('album_with_artist', ['name' => 'Diary of a madman']);

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_inside_named_factory()
    {
        Facktory::add(['basic_album', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->add('album_with_artist', function($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = Facktory::build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_override_parent_attribute_with_default_attribute_in_nested_factory()
    {
        Facktory::add(['basic_album', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->add('album_by_black_sabbath', function($f) {
                $f->artist = 'Black Sabbath';
            });
        });
        $album = Facktory::build('album_by_black_sabbath');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Black Sabbath', $album->artist);
    }

    public function test_can_add_calculated_attributes()
    {
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->display_title = function($f) {
                return "{$f->artist} - {$f->name}";
            };
        });
        $album = Facktory::build('album_with_artist');

        $this->assertInstanceOf('Album', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
        $this->assertSame('Ozzy Osbourne - Bark at the moon', $album->display_title);
    }

    public function test_can_add_sequenced_attribute()
    {
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->id = function($f, $i) {
                return $i;
            };
        });
        $album1 = Facktory::build('album_with_artist');
        $album2 = Facktory::build('album_with_artist');

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
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = Facktory::buildList('album_with_artist', 5);

        $this->assertSame(5, count($albums));
        foreach ($albums as $album) {
            $this->assertInstanceOf('Album', $album);
            $this->assertSame('Bark at the moon', $album->name);
            $this->assertSame('Ozzy Osbourne', $album->artist);
        }
    }

    public function test_can_can_override_attribute_when_building_list()
    {
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = Facktory::buildList('album_with_artist', 5, [
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
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = Facktory::buildList('album_with_artist', 5, [
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
        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->release_date = '1983-11-15';
        });
        $albums = Facktory::buildList('album_with_artist', 3, [
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
        Facktory::add(['hit_song', 'Song'], function($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
            $f->album = function() {
                return Facktory::build('album_with_artist');
            };
        });

        Facktory::add(['album_with_artist', 'Album'], function($f) {
            $f->name = 'Blizzard of Ozz';
            $f->artist = 'Ozzy Osbourne';
        });

        $song = Facktory::build('hit_song');

        $this->assertInstanceOf('Song', $song);
        $this->assertSame('Blizzard of Ozz', $song->album->name);
        $this->assertSame('Ozzy Osbourne', $song->album->artist);
    }
}

class Album {}

class Song {}
