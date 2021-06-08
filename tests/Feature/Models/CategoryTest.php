<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
/* use Illuminate\Foundation\Testing\RefreshDatabase; */
/* use Illuminate\Foundation\Testing\WithFaker; */
use Tests\TestCase;

class CategoryTest extends TestCase
{
  use DatabaseMigrations;

  public function testList()
  {
    factory(Category::class, 1)->create();

    $categories = Category::all();
    $categoryKeys = array_keys($categories->first()->getAttributes());

    $this->assertCount(1, $categories);
    $this->assertEqualsCanonicalizing([
      'id',
      'name',
      'description',
      'is_active',
      'created_at',
      'updated_at',
      'deleted_at'
    ], $categoryKeys);
  }

  public function testCreate()
  {
    $category = Category::create([
      'name' => 'test 1'
    ]);

    $expUuid = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
    $category->refresh();

    $this->assertEquals('test 1', $category->name);
    $this->assertNull($category->description);
    $this->assertTrue($category->is_active);

    $category = Category::create([
      'name' => 'test 1',
      'description' => null
    ]);

    $this->assertNull($category->description);

    $category = Category::create([
      'name' => 'test 1',
      'description' => 'description'
    ]);

    $this->assertEquals("description", $category->description);

    $category = Category::create([
      'name' => 'test 1',
      'is_active' => false
    ]);

    $this->assertFalse($category->is_active);

    $category = Category::create([
      'name' => 'test 1',
      'is_active' => true
    ]);

    $this->assertTrue($category->is_active);
    $this->assertTrue((bool)preg_match($expUuid, $category->id));
  }

  public function testUpdate()
  {
    /** @var Category $category */
    $category = factory(Category::class)->create([
      "description" => "test_description",
      'is_active' => false
    ])->first();

    $data = [
      'name' => 'test_name_updated',
      'description' => 'test_description_updated',
      'is_active' => true
    ];
    $category->update($data);

    foreach ($data as $key => $value) {
      $this->assertEquals($value, $category->{$key});
    }
  }

  public function testDelete()
  {
    $category = Category::create([
      "name" => "test category 1"
    ]);

    $category->delete();
    $deletedCategory = Category::onlyTrashed()->get();

    $this->assertCount(1, $deletedCategory);
  }
}
