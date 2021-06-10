<?php

declare(strict_types=1);

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
  protected function assertStore(
    array    $data,
    array    $expectedStoredData,
    array    $expectedResponseData = null
  ): TestResponse {
    /** @var TestResponse $response */
    $response = $this->postJson($this->routeStore(), $data);

    $response->assertStatus(201);
    $this->assertDatabaseValue($response, $expectedStoredData);
    $this->assertResponseValue(
      $response,
      $expectedStoredData,
      $expectedResponseData
    );

    return $response;
  }

  protected function assertUpdate(
    array    $data,
    array    $expectedStoredData,
    array    $expectedResponseData = null
  ): TestResponse {
    /** @var TestResponse $response */
    $response = $this->putJson($this->routeUpdate(), $data);

    $response->assertStatus(200);
    $this->assertDatabaseValue($response, $expectedStoredData);
    $this->assertResponseValue(
      $response,
      $expectedStoredData,
      $expectedResponseData
    );

    return $response;
  }

  private function assertDatabaseValue(
    TestResponse $response,
    array $expectedDatabaseData
  ) {
    $model = $this->model();
    $table = (new $model)->getTable();

    $expectedDatabaseValue = $expectedDatabaseData
      + ["id" => $response->json("id")];
    $this->assertDatabaseHas($table, $expectedDatabaseValue);
  }

  private function assertResponseValue(
    TestResponse $response,
    array $expectedDatabaseData,
    array $expectedResponseData = null
  ) {
    $expectedResponseValue = ($expectedResponseData ?? $expectedDatabaseData)
      + ["id" => $response->json("id")];

    $response->assertJsonFragment($expectedResponseValue);
  }
}
