<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
  use DatabaseMigrations;

  public function testIndex()
  {
    $category = factory(Category::class)->create();

    $response = $this->get(route('categories.index'));

    $response
      ->assertStatus(200)
      ->assertJson([$category->toArray()]);
  }

  public function testShow()
  {
    $category = factory(Category::class)->create();

    $response = $this->get(route(
      'categories.show',
      ['category' => $category->id]
    ));

    $response
      ->assertStatus(200)
      ->assertJson($category->toArray());
  }

  public function testStore()
  {
    $response = $this->postJson(
      route('categories.store'),
      [
        "name" => "test post"
      ]
    );

    $category = Category::find($response->json("id"));

    $response
      ->assertStatus(201)
      ->assertJson($category->toArray());
    $this->assertTrue($response->json("is_active"));
    $this->assertNull($response->json("description"));

    $response = $this->postJson(
      route('categories.store'),
      [
        "name" => "test post",
        "description" => "description",
        "is_active" => false
      ]
    );

    $response
      ->assertStatus(201)
      ->assertJsonFragment([
        "is_active" => false,
        "description" => "description"
      ]);
  }

  public function testUpdate()
  {
    $category = factory(Category::class)->create([
      "name" => "name",
      "description" => "description 1",
      "is_active" => true
    ]);

    $response = $this->putJson(
      route(
        'categories.update',
        ['category' => $category->id]
      ),
      [
        "name" => "test update",
        "description" => "description 2"
      ]
    );

    $updatedCategory = Category::find($response->json("id"));

    $response
      ->assertStatus(200)
      ->assertJson($updatedCategory->toArray());

    $response = $this->putJson(
      route(
        'categories.update',
        ['category' => $category->id]
      ),
      [
        "name" => "test update",
        "is_active" => false,
        "description" => ""
      ]
    );

    $response
      ->assertStatus(200)
      ->assertJsonFragment([
        "is_active" => false,
        "description" => null
      ]);
  }

  public function testDestroy()
  {
    $category = factory(Category::class)->create();

    $response = $this->deleteJson(
      route(
        "categories.destroy",
        ["category" => $category->id]
      )
    );

    $response
      ->assertStatus(204);

    $this->assertNull(Category::find($category->id));
    $this->assertNotNull(Category::withTrashed()->find($category->id));
  }

  public function testInvalidData()
  {
    $response = $this->post(
      route('categories.store'),
      [],
      ["Accept" => "application/json"]
    );

    $this->assertRequiredName($response);

    $response = $this->post(
      route('categories.store'),
      [
        "name" => str_repeat("a", 256)
      ],
      ["Accept" => "application/json"]
    );

    $this->assertMaxSizeName($response);

    $response = $this->post(
      route('categories.store'),
      [
        "name" => "test category",
        "is_active" => "true"
      ],
      ["Accept" => "application/json"]
    );

    $this->assertTypeIsActive($response);

    $category = factory(Category::class)->create();

    $response = $this->putJson(
      route(
        'categories.update',
        ['category' => $category->id]
      ),
      []
    );

    $this->assertRequiredName($response);

    $response = $this->put(
      route(
        'categories.update',
        ['category' => $category->id]
      ),
      [
        "name" => str_repeat("a", 256)
      ],
      ["Accept" => "application/json"]
    );

    $this->assertMaxSizeName($response);

    $response = $this->put(
      route(
        'categories.update',
        ['category' => $category->id]
      ),
      [
        "name" => "test category",
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
