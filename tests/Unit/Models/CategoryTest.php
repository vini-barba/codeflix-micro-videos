<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use PHPUnit\Framework\TestCase;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;

class CategoryTest extends TestCase
{
  private $category;

  protected function setUp(): void
  {
    parent::setUp();
    $this->category = new Category();
  }

  public function testFillableAttribute()
  {
    $fillable = ['name', 'description', 'is_active'];

    $this->assertEquals(
      $fillable,
      $this->category->getFillable()
    );
  }

  public function testUseTraits()
  {
    $traits = [
      SoftDeletes::class,
      Uuid::class
    ];
    $categoryTraits = array_keys(class_uses(Category::class));

    $this->assertEquals($traits, $categoryTraits);
  }

  public function testCastsAttribute()
  {
    $casts = [
      "id" => "string",
      "is_active" => "boolean"
    ];

    $this->assertEquals($casts, $this->category->getCasts());
  }

  public function testDatesAttribute()
  {
    $dates = ["deleted_at", "created_at", "updated_at"];

    $this->assertEquals(count($dates), count($this->category->getDates()));

    foreach ($dates as $date) {
      $this->assertContains($date, $this->category->getDates());
    }
  }

  public function testIncrementing()
  {

    $this->assertFalse($this->category->incrementing);
  }
}
