<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
  use DatabaseMigrations;
  public function testIndex()
  {
    $genre = factory(Genre::class)->create();

    $response = $this->get(route('genres.index'));

    $response
      ->assertStatus(200)
      ->assertJson([$genre->toArray()]);
  }

  public function testShow()
  {
    $genre = factory(Genre::class)->create();

    $response = $this->get(
      route(
        "genres.show",
        ["genre" => $genre->id]
      )
    );

    $response
      ->assertStatus(200)
      ->assertJson($genre->toArray());
  }

  public function testStore()
  {
    $response = $this->postJson(
      route('genres.store'),
      ["name" => "test post"]
    );

    $genre = Genre::find($response->json("id"));

    $response
      ->assertStatus(201)
      ->assertJson($genre->toArray());
    $this->assertTrue($response->json("is_active"));

    $response = $this->postJson(
      route('genres.store'),
      [
        "name" => "test post",
        "is_active" => false
      ]
    );

    $response
      ->assertStatus(201)
      ->assertJsonFragment([
        "is_active" => false,
      ]);
  }

  public function  testUpdate()
  {
    $genre = factory(Genre::class)->create([
      "name" => "test genre 1",
      "is_active" => false
    ]);

    $response = $this->putJson(
      route(
        "genres.update",
        ["genre" => $genre->id]
      ),
      [
        "name" => "test genre 2",
        "is_active" => true
      ]
    );

    $response
      ->assertStatus(200)
      ->assertJsonFragment([
        "name" => "test genre 2",
        "is_active" => true
      ]);
  }

  public function testDestroy()
  {
    $genre = factory(Genre::class)->create();

    $response = $this->deleteJson(
      route(
        "genres.destroy",
        ["genre" => $genre->id]
      )
    );

    $response
      ->assertStatus(204);

    $this->assertNull(Genre::find($genre->id));
    $this->assertNotNull(Genre::withTrashed()->find($genre->id));
  }

  public function testInvalidData()
  {
    $response = $this->post(
      route('genres.store'),
      [],
      ["Accept" => "application/json"]
    );

    $this->assertRequiredName($response);

    $response = $this->post(
      route('genres.store'),
      [
        "name" => str_repeat("a", 256)
      ],
      ["Accept" => "application/json"]
    );

    $this->assertMaxSizeName($response);

    $response = $this->post(
      route('genres.store'),
      [
        "name" => "test genre",
        "is_active" => "true"
      ],
      ["Accept" => "application/json"]
    );

    $this->assertTypeIsActive($response);

    $genre = factory(Genre::class)->create();

    $response = $this->putJson(
      route(
        'genres.update',
        ['genre' => $genre->id]
      ),
      []
    );

    $this->assertRequiredName($response);

    $response = $this->put(
      route(
        'genres.update',
        ['genre' => $genre->id]
      ),
      [
        "name" => str_repeat("a", 256)
      ],
      ["Accept" => "application/json"]
    );

    $this->assertMaxSizeName($response);

    $response = $this->put(
      route(
        'genres.update',
        ['genre' => $genre->id]
      ),
      [
        "name" => "test genre",
        "is_active" => "true"
      ],
      ["Accept" => "application/json"]
    );

    $this->assertTypeIsActive($response);
  }

  protected function assertRequiredName(TestResponse $response)
  {
    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['name'])
      ->assertJsonMissingValidationErrors(["is_active"])
      ->assertJsonFragment([
        Lang::get("validation.required", ["attribute" => "name"])
      ]);
  }

  protected function assertMaxSizeName(TestResponse $response)
  {
    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['name'])
      ->assertJsonFragment([
        Lang::get("validation.max.string", [
          "attribute" => "name",
          "max" => 255
        ])
      ]);
  }

  protected function assertTypeIsActive(TestResponse $response)
  {
    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors(['is_active'])
      ->assertJsonFragment([
        Lang::get("validation.boolean", [
          "attribute" => "is active"
        ])
      ]);
  }
}
