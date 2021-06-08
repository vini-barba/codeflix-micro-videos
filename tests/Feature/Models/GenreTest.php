<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
/* use Illuminate\Foundation\Testing\RefreshDatabase; */
/* use Illuminate\Foundation\Testing\WithFaker; */
use Tests\TestCase;

class GenreTest extends TestCase
{
  use DatabaseMigrations;

  public function testList()
  {
    factory(Genre::class, 1)->create();

    $genres = Genre::all();
    $genreKeys = array_keys($genres->first()->getAttributes());

    $this->assertCount(1, $genres);
    $this->assertEqualsCanonicalizing([
      'id',
      'name',
      'is_active',
      'created_at',
      'updated_at',
      'deleted_at'
    ], $genreKeys);
  }

  public function testCreate()
  {
    $genre = Genre::create([
      'name' => 'test genre 1'
    ]);
    $expUuid = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
    $genre->refresh();

    $this->assertEquals('test genre 1', $genre->name);
    $this->assertTrue($genre->is_active);

    $genre = Genre::create([
      'name' => 'test genre 1',
      'is_active' => false
    ]);
    $this->assertFalse($genre->is_active);

    $genre = Genre::create([
      'name' => 'test genre 1',
      'is_active' => true
    ]);
    $this->assertTrue($genre->is_active);
    $this->assertTrue((bool)preg_match($expUuid, $genre->id));
  }

  public function testUpdate()
  {
    /** @var Genre $genre */
    $genre = factory(Genre::class)->create([
      'name' => 'test genre 1',
      'is_active' => false
    ]);

    $genre->update(['name' => 'test genre 2']);

    $this->assertEquals('test genre 2', $genre->name);
    $this->assertFalse($genre->is_active);

    $genre->update(['is_active' => true]);

    $this->assertTrue($genre->is_active);
  }

  public function testDelete()
  {
    $genre = Genre::create([
      'name' => 'test genre 1'
    ]);

    $genre->delete();
    $deletedGenre = Genre::onlyTrashed()->get();

    $this->assertCount(1, $deletedGenre);
  }
}
