<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
  use DatabaseMigrations, TestValidations, TestSaves;

  private $category;

  protected  function setUp(): void
  {
    parent::setUp();
    $this->category = factory(Category::class)->create();
  }

  public function testIndex()
  {
    $response = $this->get(route('categories.index'));

    $response
      ->assertStatus(200)
      ->assertJson([$this->category->toArray()]);
  }

  public function testShow()
  {
    $response = $this->get(
      route(
        'categories.show',
        ['category' => $this->category->id]
      )
    );

    $response
      ->assertStatus(200)
      ->assertJson($this->category->toArray());
  }

  public function testStore()
  {
    $data = ["name" => "test post 1"];
    $expectedData = $data + [
      "is_active" => true,
      "description" => null,
      "deleted_at" => null
    ];

    $response = $this->assertStore($data, $expectedData);
    $response->assertJsonStructure([
      "created_at", "updated_at"
    ]);

    $data = [
      "name" => "test post 2",
      "description" => "description",
      "is_active" => false
    ];
    $expectedData = $data;

    $this->assertStore($data, $expectedData);
  }

  public function testUpdate()
  {
    $this->category = factory(Category::class)->create([
      "name" => "test update 0",
      "description" => "description 1",
      "is_active" => false
    ]);

    $data =  [
      "name" => "test update 1",
      "description" => "description 2"
    ];

    $response = $this->assertUpdate($data, $data);
    $response->assertJsonStructure([
      "created_at", "updated_at", "deleted_at"
    ]);

    $data = [
      "name" => "test update 2",
      "is_active" => true,
      "description" => ""
    ];
    $expectedData = array_merge(
      $data,
      ["deleted_at" => null, "description" => null]
    );

    $this->assertUpdate($data, $expectedData);

    $data = [
      "name" => "test update 3",
      "is_active" => true,
      "description" => "         description 3      "
    ];
    $expectedData = array_merge(
      $data,
      ["deleted_at" => null, "description" => "description 3"]
    );

    $this->assertUpdate($data, $expectedData);
  }

  public function testDestroy()
  {
    $response = $this->deleteJson(
      route(
        "categories.destroy",
        ["category" => $this->category->id]
      )
    );

    $response->assertStatus(204);
    $this->assertNull(Category::find($this->category->id));
    $this->assertNotNull(Category::withTrashed()->find($this->category->id));
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
    return route('categories.store');
  }

  protected function routeUpdate()
  {
    return route('categories.update', ["category" => $this->category->id]);
  }

  protected function model()
  {
    return Category::class;
  }
}
