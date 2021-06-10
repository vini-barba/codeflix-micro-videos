<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;

trait TestValidations
{
  protected function assertInvalidUpdateAction(
    array $data,
    string $rule,
    $params = []
  ) {
    $response = $this->putJson(
      $this->routeUpdate(),
      $data
    );

    $fields = array_keys($data);
    $this->assertInvalidFields($response, $fields, $rule, $params);
  }

  protected function assertInvalidStoreAction(
    array $data,
    string $rule,
    $params = []
  ) {
    $response = $this->postJson(
      $this->routeStore(),
      $data
    );

    $fields = array_keys($data);
    $this->assertInvalidFields($response, $fields, $rule, $params);
  }

  protected function assertInvalidFields(
    TestResponse $response,
    array $fields,
    string $rule,
    array $params = []
  ) {
    $response
      ->assertStatus(422)
      ->assertJsonValidationErrors($fields);

    foreach ($fields as $field) {
      $fieldname = str_replace("_", " ", $field);
      $response->assertJsonFragment([
        Lang::get(
          "validation.{$rule}",
          ["attribute" => $fieldname] + $params
        )
      ]);
    }
  }
}
