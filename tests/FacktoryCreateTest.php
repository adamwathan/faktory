<?php

use Illuminate\Database\Capsule\Manager as DB;
use AdamWathan\Facktory\Facktory;

class FacktoryCreateTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->migrate();
        $this->facktory = new Facktory;
    }

    protected function migrate()
    {
        $this->migrateAlbumsTable();
        $this->migrateSongsTable();
        $this->migratePostsTable();
        $this->migrateCommentsTable();
        $this->migrateCategoriesTable();
        $this->migrateCategoryPostsTable();
    }

    protected function migrateAlbumsTable()
    {
        DB::schema()->create('albums', function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->date('release_date');
            $table->timestamps();
        });
    }

    protected function migrateSongsTable()
    {
        DB::schema()->create('songs', function($table)
        {
            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->string('name');
            $table->integer('length')->unsigned();
            $table->timestamps();
        });
    }

    protected function migratePostsTable()
    {
        DB::schema()->create('posts', function($table)
        {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    protected function migrateCommentsTable()
    {
        DB::schema()->create('comments', function($table)
        {
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('body');
            $table->timestamps();
        });
    }

    protected function migrateCategoriesTable()
    {
        DB::schema()->create('categories', function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function migrateCategoryPostsTable()
    {
        DB::schema()->create('category_posts', function($table)
        {
            $table->increments('id');
            $table->integer('category_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->timestamps();
        });
    }

    public function test_saved_has_many_get_correct_foreign_id()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id');
        });
        $this->facktory->add(['album_with_7_songs', 'Album'], function($f) {
            $f->name = 'Destroy Erase Improve';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 7, 'album_id');
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs');
        $songs = $album->songs;

        $this->assertSame(5, $songs->count());

        $album = $this->facktory->create('album_with_7_songs');
        $songs = $album->songs;

        $this->assertSame(7, $songs->count());
    }

    public function test_saved_has_many_get_correct_foreign_id_different_classes()
    {
        $this->facktory->add(['comment', 'Comment'], function($f) {
            $f->body = 'This post is great';
        });
        $this->facktory->add(['post_with_5_comments', 'Post'], function($f) {
            $f->title = 'Sweet post';
            $f->comments = $f->hasMany('comment', 5, 'post_id');
        });
        $this->facktory->add(['post_with_7_comments', 'Post'], function($f) {
            $f->title = 'Sweet post';
            $f->comments = $f->hasMany('comment', 7, 'post_id');
        });

        $post = $this->facktory->create('post_with_5_comments');
        $comments = $post->comments;

        $this->assertSame(5, $comments->count());

        $post = $this->facktory->create('post_with_7_comments');
        $comments = $post->comments;

        $this->assertSame(7, $comments->count());
    }

    public function test_saved_has_many_can_have_attributes()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs');
        $songs = $album->songs;

        $this->assertSame(5, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(100, $song->length);
        }
    }

    public function test_saved_has_many_can_have_different_attributes_for_each_instance_specified_in_one_array()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 2, 'album_id', ['length' => [100, 200]]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs');
        $songs = $album->songs;

        $this->assertSame(2, $songs->count());
        $this->assertEquals(100, $songs[0]->length);
        $this->assertEquals(200, $songs[1]->length);
    }

    public function test_saved_belongs_to_gets_correct_foreign_id()
    {
        $this->facktory->add(['song_with_album', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
            $f->album = $f->belongsTo('album', 'album_id');
        });
        $this->facktory->add(['album', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
        });

        $song = $this->facktory->create('song_with_album');
        $album = $song->album;

        $this->assertEquals($song->album_id, $album->id);

        $this->facktory->add(['comment_with_post', 'Comment'], function($f) {
            $f->body = 'Great post';
            $f->post = $f->belongsTo('post', 'post_id');
        });
        $this->facktory->add(['post', 'Post'], function($f) {
            $f->title = 'The post to rule all posts';
        });

        $comment = $this->facktory->create('comment_with_post');
        $post = $comment->post;

        $this->assertEquals($comment->post_id, $post->id);
    }

    public function test_saved_belongs_to_can_have_attribute_overrides()
    {
        $this->facktory->add(['song_with_album', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
            $f->album = $f->belongsTo('album', 'album_id', [
                'name' => 'Contradictions Collapse'
                ]);
        });
        $this->facktory->add(['album', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
        });

        $song = $this->facktory->create('song_with_album');
        $album = $song->album;

        $this->assertEquals('Contradictions Collapse', $album->name);
    }

    public function test_saved_has_one_gets_correct_foreign_id()
    {
        $this->facktory->add(['album_with_song', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->song = $f->hasOne('song', 'album_id');
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_song');
        $song = $album->song;

        $this->assertEquals($song->album_id, $album->id);
    }

    public function test_can_override_attributes_on_create_with_array()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', [
            'name' => 'Destroy Erase Improve',
            'release_date' => new DateTime('1995-07-25'),
            ]);

        $this->assertSame('Destroy Erase Improve', $album->name);
        $this->assertTrue(new DateTime('1995-07-25') == $album->release_date);
        $songs = $album->songs;
        $this->assertSame(5, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(100, $song->length);
        }
    }

    public function test_can_override_attributes_on_create_with_closure()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime('2001-01-01');
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs = $f->hasMany('song', 2, 'album_id', ['length' => 150]);
        });

        $this->assertTrue(new DateTime('1998-11-10') == $album->release_date);
        $this->assertSame('Chaosphere', $album->name);
        $songs = $album->songs;
        $this->assertSame(2, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(150, $song->length);
        }
    }

    public function test_overriding_with_closure_doesnt_permanently_alter_factory()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime('2001-01-01');
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs = $f->hasMany('song', 2, 'album_id', ['length' => 150]);
        });

        $album = $this->facktory->create('album_with_5_songs');

        $this->assertTrue(new DateTime('2001-01-01') == $album->release_date);
        $this->assertSame('Chaosphere', $album->name);
        $songs = $album->songs;
        $this->assertSame(5, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(100, $song->length);
        }
    }

    public function test_can_alter_has_many_relationship_amount_without_overriding_entire_relationship()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs->amount(2);
        });

        $songs = $album->songs;
        $this->assertSame(2, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(100, $song->length);
        }
    }

    public function test_can_alter_has_many_relationship_attribute_without_overriding_entire_relationship()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs->attributes(['length' => 150]);
        });

        $songs = $album->songs;
        $this->assertSame(5, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(150, $song->length);
        }
    }

    public function test_can_chain_changes_on_has_many_relationship()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs->amount(2)->attributes(['length' => 150]);
        });

        $songs = $album->songs;
        $this->assertSame(2, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(150, $song->length);
        }

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs->attributes(['length' => 150])->amount(2);
        });

        $songs = $album->songs;
        $this->assertSame(2, $songs->count());
        foreach ($songs as $song) {
            $this->assertEquals(150, $song->length);
        }
    }

    public function test_can_alter_has_many_relationship_attribute_with_independent_values_per_related_object()
    {
        $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->songs = $f->hasMany('song', 5, 'album_id', ['length' => 100]);
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_5_songs', function($f) {
            $f->release_date = new DateTime('1998-11-10');
            $f->songs->amount(2)->attributes(['length' => [150, 250]]);
        });

        $songs = $album->songs;
        $this->assertSame(2, $songs->count());
        $this->assertEquals(150, $songs[0]->length);
        $this->assertEquals(250, $songs[1]->length);
    }

    public function test_can_override_belongs_to_attributes_on_create()
    {
        $this->facktory->add(['song_with_album', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
            $f->album = $f->belongsTo('album', 'album_id', [
                'name' => 'Contradictions Collapse'
                ]);
        });
        $this->facktory->add(['album', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
        });

        $song = $this->facktory->create('song_with_album', function($f) {
            $f->album->attributes(['name' => 'None']);
        });
        $album = $song->album;
        $this->assertEquals('None', $album->name);
    }

    public function test_can_override_has_one_relationship_attributes_on_create()
    {
        $this->facktory->add(['album_with_song', 'Album'], function($f) {
            $f->name = 'Chaosphere';
            $f->release_date = new DateTime;
            $f->song = $f->hasOne('song', 'album_id');
        });
        $this->facktory->add(['song', 'Song'], function($f) {
            $f->name = 'Concatenation';
            $f->length = 257;
        });

        $album = $this->facktory->create('album_with_song', function($f) {
            $f->song->attributes(['length' => 100]);
        });
        $song = $album->song;

        $this->assertEquals($song->album_id, $album->id);
        $this->assertEquals(100, $song->length);
    }

    // public function test_saved_has_many_can_guess_correct_foreign_keys()
    // {
    //     $this->facktory->add(['album_with_5_songs', 'Album'], function($f) {
    //         $f->name = 'Chaosphere';
    //         $f->release_date = new DateTime;
    //         $f->songs = $f->hasMany('song', 'album_id', 5);
    //     });
    //     $this->facktory->add(['album_with_7_songs', 'Album'], function($f) {
    //         $f->name = 'Destroy Erase Improve';
    //         $f->release_date = new DateTime;
    //         $f->songs = $f->hasMany('song', 'album_id', 7);
    //     });
    //     $this->facktory->add(['song', 'Song'], function($f) {
    //         $f->name = 'Concatenation';
    //         $f->length = 257;
    //     });

    //     $album = $this->facktory->create('album_with_5_songs');
    //     $songs = $album->songs;

    //     $this->assertSame(5, $songs->count());

    //     $album = $this->facktory->create('album_with_7_songs');
    //     $songs = $album->songs;

    //     $this->assertSame(7, $songs->count());
    // }
}



class Album extends Illuminate\Database\Eloquent\Model
{
    protected $dates = ['release_date'];
    public function songs()
    {
        return $this->hasMany('Song');
    }

    public function song()
    {
        return $this->hasOne('Song');
    }

    public function getTotalLength()
    {
        return $this->songs->sum('length');
    }
}

class Song extends Illuminate\Database\Eloquent\Model
{
    public function album()
    {
        return $this->belongsTo('Album');
    }
}

class Post extends Illuminate\Database\Eloquent\Model
{
    public function comments()
    {
        return $this->hasMany('Comment');
    }

    public function categories()
    {
        return $this->belongsToMany('Category');
    }
}

class Comment extends Illuminate\Database\Eloquent\Model
{
    public function post()
    {
        return $this->belongsTo('Post');
    }
}

class Category extends Illuminate\Database\Eloquent\Model
{
    public function posts()
    {
        return $this->belongsToMany('Post');
    }
}
