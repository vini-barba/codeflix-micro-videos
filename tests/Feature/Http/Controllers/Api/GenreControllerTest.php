<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
  use DatabaseMigrations, TestValidations, TestSaves;

  private $genre;

  protected  function setUp(): void
  {
    parent::setUp();
    $this->genre = factory(Genre::class)->create();
  }

  public function testIndex()
  {
    $response = $this->get(route('genres.index'));

    $response
      ->assertStatus(200)
      ->assertJson([$this->genre->toArray()]);
  }

  public function testShow()
  {
    $response = $this->get(
      route(
        "genres.show",
        ["genre" => $this->genre->id]
      )
    );

    $response
      ->assertStatus(200)
      ->assertJson($this->genre->toArray());
  }

  public function testStore()
  {
    $data = ["name" => "test post"];
    $expectedData = $data + ["is_active" => true];
    $response = $this->assertStore($data, $expectedData);
    $response->assertJsonStructure(["created_at", "updated_at", "deleted_at"]);

    $data = ["name" => "test post 2", "is_active" => false];
    $expectedData = $data;
    $this->assertStore($data, $expectedData);
  }

  public function  testUpdate()
  {
    $this->genre = factory(Genre::class)->create([
      "name" => "test genre 0",
      "is_active" => false
    ]);

    $data = [
      "name" => "test genre 1",
      "is_active" => true
    ];
    $expectedData = [
      "name" => "test genre 1",
      "is_active" => true
    ];

    $response = $this->assertUpdate($data, $expectedData);
    $response->assertJsonStructure(["created_at", "updated_at", "deleted_at"]);
  }

  public function testDestroy()
  {
    $response = $this->deleteJson(
      route(
        "genres.destroy",
        ["genre" => $this->genre->id]
      )
    );

    $response
      ->assertStatus(204);

    $this->assertNull(Genre::find($this->genre->id));
    $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
  }

  public function testInvalidData()
  {
    $data = ["name" => ""];
    $this->assertInvalidStoreAction($data, "required");
    $this->assertInvalidUpdateAction($data, "required");

    $data = [
      "name" => str_repeat("a", 256)
    ];
    $this->assertInvalidStoreAction($data, "max.string", ["max" => 255]);
    $this->assertInvalidUpdateAction($data, "max.string", ["max" => 255]);

    $data = [
      "is_active" => "true"
    ];
    $this->assertInvalidStoreAction($data, "boolean");
    $this->assertInvalidUpdateAction($data, "boolean");
  }

  protected function routeStore()
  {
    return route('genres.store');
  }

  protected function routeUpdate()
  {
    return route('genres.update', ["genre" => $this->genre->id]);
  }

  protected function model()
  {
    return Genre::class;
  }
}
