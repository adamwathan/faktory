<?php

use AdamWathan\Faktory\Faktory;

class FaktoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->faktory = new Faktory;
    }

    public function test_can_define_basic_factory()
    {
        $this->faktory->define('BuildAlbum', function ($f) {
            $f->name = 'Bark at the moon';
        });
        $album = $this->faktory->build('BuildAlbum');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
    }

    public function test_can_override_attribute()
    {
        $this->faktory->define('BuildAlbum', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $this->faktory->build('BuildAlbum', [
            'name' => 'Diary of a madman'
            ]);

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_define_factory_with_name_and_override_attribute()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $album = $this->faktory->build('album_with_artist', [
            'artist' => 'Randy Rhoads'
            ]);

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_inherit_attributes()
    {
        $this->faktory->define('BuildAlbum', function ($f) {
            $f->name = 'Bark at the moon';
            $f->define('album_with_artist', function ($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_and_override_attribute()
    {
        $this->faktory->define('BuildAlbum', function ($f) {
            $f->name = 'Bark at the moon';
            $f->define('album_with_artist', function ($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $this->faktory->build('album_with_artist', ['artist' => 'Randy Rhoads']);

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Randy Rhoads', $album->artist);
    }

    public function test_can_nest_factory_and_override_parent_attribute()
    {
        $this->faktory->define('BuildAlbum', function ($f) {
            $f->name = 'Bark at the moon';
            $f->define('album_with_artist', function ($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $this->faktory->build('album_with_artist', ['name' => 'Diary of a madman']);

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Diary of a madman', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_nest_factory_inside_named_factory()
    {
        $this->faktory->define('BuildAlbum', 'basic_album', function ($f) {
            $f->name = 'Bark at the moon';
            $f->define('album_with_artist', function ($f) {
                $f->artist = 'Ozzy Osbourne';
            });
        });
        $album = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
    }

    public function test_can_override_parent_attribute_with_default_attribute_in_nested_factory()
    {
        $this->faktory->define('BuildAlbum', 'basic_album', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->define('album_by_black_sabbath', function ($f) {
                $f->artist = 'Black Sabbath';
            });
        });
        $album = $this->faktory->build('album_by_black_sabbath');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Black Sabbath', $album->artist);
    }

    public function test_can_add_calculated_attributes()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->display_title = function ($f) {
                return "{$f->artist} - {$f->name}";
            };
        });
        $album = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertSame('Bark at the moon', $album->name);
        $this->assertSame('Ozzy Osbourne', $album->artist);
        $this->assertSame('Ozzy Osbourne - Bark at the moon', $album->display_title);
    }

    public function test_calculated_attributes_can_use_other_calculated_attributes()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->date_released = function () {
                return new DateTime('1983-11-15');
            };
            $f->display_date = function ($f) {
                return $f->date_released->format('F j, Y');
            };
        });

        $album = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album);
        $this->assertEquals(new DateTime('1983-11-15'), $album->date_released);
        $this->assertEquals('November 15, 1983', $album->display_date);
    }

    public function test_can_add_sequenced_attribute()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->id = function ($f, $i) {
                return $i;
            };
        });
        $album1 = $this->faktory->build('album_with_artist');
        $album2 = $this->faktory->build('album_with_artist');

        $this->assertInstanceOf('BuildAlbum', $album1);
        $this->assertSame('Bark at the moon', $album1->name);
        $this->assertSame('Ozzy Osbourne', $album1->artist);
        $this->assertSame(1, $album1->id);

        $this->assertInstanceOf('BuildAlbum', $album2);
        $this->assertSame('Bark at the moon', $album2->name);
        $this->assertSame('Ozzy Osbourne', $album2->artist);
        $this->assertSame(2, $album2->id);
    }

    public function test_can_build_list_of_objects()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $this->faktory->buildMany('album_with_artist', 5);

        $this->assertSame(5, count($albums));
        foreach ($albums as $album) {
            $this->assertInstanceOf('BuildAlbum', $album);
            $this->assertSame('Bark at the moon', $album->name);
            $this->assertSame('Ozzy Osbourne', $album->artist);
        }
    }

    public function test_can_can_override_attribute_when_building_list()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $this->faktory->buildMany('album_with_artist', 5, [
            'artist' => 'Dio'
            ]);

        $this->assertSame(5, count($albums));
        foreach ($albums as $album) {
            $this->assertInstanceOf('BuildAlbum', $album);
            $this->assertSame('Bark at the moon', $album->name);
            $this->assertSame('Dio', $album->artist);
        }
    }

    public function test_can_can_override_attributes_independently_when_building_list()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
        });
        $albums = $this->faktory->buildMany('album_with_artist', 5, [
            'artist' => [
            'Dio',
            'Black Sabbath',
            'Diamondhead',
            'Iron Maiden',
            'Judas Priest'
            ]
            ]);

        $this->assertSame(5, count($albums));
        $this->assertInstanceOf('BuildAlbum', $albums[0]);
        $this->assertSame('Bark at the moon', $albums[0]->name);
        $this->assertSame('Dio', $albums[0]->artist);

        $this->assertInstanceOf('BuildAlbum', $albums[1]);
        $this->assertSame('Bark at the moon', $albums[1]->name);
        $this->assertSame('Black Sabbath', $albums[1]->artist);

        $this->assertInstanceOf('BuildAlbum', $albums[2]);
        $this->assertSame('Bark at the moon', $albums[2]->name);
        $this->assertSame('Diamondhead', $albums[2]->artist);

        $this->assertInstanceOf('BuildAlbum', $albums[3]);
        $this->assertSame('Bark at the moon', $albums[3]->name);
        $this->assertSame('Iron Maiden', $albums[3]->artist);

        $this->assertInstanceOf('BuildAlbum', $albums[4]);
        $this->assertSame('Bark at the moon', $albums[4]->name);
        $this->assertSame('Judas Priest', $albums[4]->artist);
    }

    public function test_can_can_override_attributes_independently_and_as_a_group_when_building_list()
    {
        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Bark at the moon';
            $f->artist = 'Ozzy Osbourne';
            $f->release_date = '1983-11-15';
        });
        $albums = $this->faktory->buildMany('album_with_artist', 3, [
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
        $this->assertInstanceOf('BuildAlbum', $albums[0]);
        $this->assertSame('Bark at the moon', $albums[0]->name);
        $this->assertSame('Dio', $albums[0]->artist);
        $this->assertSame('2001-05-06', $albums[0]->release_date);

        $this->assertInstanceOf('BuildAlbum', $albums[1]);
        $this->assertSame('Bark at the moon', $albums[1]->name);
        $this->assertSame('Black Sabbath', $albums[1]->artist);
        $this->assertSame('2001-05-06', $albums[1]->release_date);

        $this->assertInstanceOf('BuildAlbum', $albums[2]);
        $this->assertSame('Bark at the moon', $albums[2]->name);
        $this->assertSame('Diamondhead', $albums[2]->artist);
        $this->assertSame('2001-05-06', $albums[2]->release_date);
    }

    public function test_can_lazy_evaluate_related_class_before_defining_related_factory()
    {
        $this->faktory->define('BuildSong', 'hit_song', function ($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
            $f->album = function () {
                return $this->faktory->build('album_with_artist');
            };
        });

        $this->faktory->define('BuildAlbum', 'album_with_artist', function ($f) {
            $f->name = 'Blizzard of Ozz';
            $f->artist = 'Ozzy Osbourne';
        });

        $song = $this->faktory->build('hit_song');

        $this->assertInstanceOf('BuildSong', $song);
        $this->assertSame('Blizzard of Ozz', $song->album->name);
        $this->assertSame('Ozzy Osbourne', $song->album->artist);
    }

    public function test_can_use_closures_as_overrides()
    {
        $this->faktory->define('BuildSong', 'hit_song', function ($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
        });

        $song = $this->faktory->build('hit_song', [
            'length' => function () {
                return 50;
            }
            ]);

        $this->assertSame(50, $song->length);
    }

    public function test_closure_overrides_still_receive_params()
    {
        $this->faktory->define('BuildSong', 'hit_song', function ($f) {
            $f->name = 'Suicide solution';
            $f->length = 125;
        });

        $song = $this->faktory->build('hit_song', [
            'length' => function ($f, $i) {
                return $f->name . $i;
            }
            ]);

        $this->assertSame('Suicide solution1', $song->length);
    }

    public function test_belongs_to_adds_public_property_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
        });
        $this->faktory->define('BuildSong', 'song_with_album', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
            $f->album = $f->belongsTo('album', 'album_id');
        });

        $song = $this->faktory->build('song_with_album');
        $album = $song->album;
        $this->assertSame('Destroy Erase Improve', $album->name);
    }

    public function test_belongs_to_can_have_overrides_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
        });
        $this->faktory->define('BuildSong', 'song_with_album', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
            $f->album = $f->belongsTo('album', 'album_id');
        });

        $song = $this->faktory->build('song_with_album', function ($song) {
            $song->album->attributes(['name' => 'Chaosphere']);
        });
        $album = $song->album;
        $this->assertSame('Chaosphere', $album->name);
    }

    public function test_has_many_adds_public_property_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album_with_5_songs', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id');
        });
        $this->faktory->define('BuildSong', 'song', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->faktory->build('album_with_5_songs');
        $songs = $album->songs;
        $this->assertSame(5, count($songs));
    }

    public function test_has_many_can_have_attribute_overrides_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album_with_5_songs', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id');
        });
        $this->faktory->define('BuildSong', 'song', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->faktory->build('album_with_5_songs', function ($album) {
            $album->songs->quantity(2);
        });
        $songs = $album->songs;
        $this->assertSame(2, count($songs));
    }

    public function test_has_one_adds_public_property_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album_with_song', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->song = $f->hasOne('song', 'album_id');
        });
        $this->faktory->define('BuildSong', 'song', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->faktory->build('album_with_song');
        $song = $album->song;
        $this->assertSame('Concatenation', $song->name);
    }

    public function test_has_one_can_have_attribute_overrides_on_build()
    {
        $this->faktory->define('BuildAlbum', 'album_with_song', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->song = $f->hasOne('song', 'album_id');
        });
        $this->faktory->define('BuildSong', 'song', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->faktory->build('album_with_song', function ($album) {
            $album->song->attributes(['name' => 'Future Breed Machine']);
        });
        $song = $album->song;
        $this->assertSame('Future Breed Machine', $song->name);
    }

    public function test_relationship_attributes_can_be_altered_as_properties()
    {
        $this->faktory->define('BuildAlbum', 'album_with_song', function ($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->song = $f->hasOne('song', 'album_id');
        });
        $this->faktory->define('BuildSong', 'song', function ($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->faktory->build('album_with_song', function ($album) {
            $album->song->name = 'Future Breed Machine';
        });
        $song = $album->song;
        $this->assertSame('Future Breed Machine', $song->name);
    }

    public function test_overriding_with_closure_doesnt_permanently_alter_factory()
    {
        $this->faktory->define('BuildUser', 'user', function ($user) {
            $user->first_name = 'John';
            $user->last_name = 'Doe';
            $user->full_name = function ($user) {
                // Concern is that this might override the real $user->first_name...
                $user->first_name = 'Bob';
                return "{$user->first_name} {$user->last_name}";
            };
        });

        $user = $this->faktory->build('user');

        $this->assertSame('John', $user->first_name);
        $this->assertSame('Doe', $user->last_name);
        $this->assertSame('Bob Doe', $user->full_name);
    }

    /**
     * @expectedException \AdamWathan\Faktory\FactoryNotRegisteredException
     */
    public function test_trying_to_build_an_undefined_factory_throws_an_exception()
    {
        $user = $this->faktory->build('user');
    }
}

class BuildAlbum {}

class BuildSong {}

class BuildUser {}
