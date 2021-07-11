<?php

namespace Tests;

trait ApiTestTrait
{
    private $response;
    private $responseContent;

    public function getContent()
    {
        $this->responseContent = json_decode($this->response->getContent(), true);
        return $this->responseContent;
    }

    public function assertApiModifications(array $actualData)
    {
        $this->assertApiSuccess();
        $this->getContent();

        $responseData = $this->responseContent['data'];

        $this->assertNotEmpty($responseData['id']);
    }

    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();
        $this->getContent();

        $responseData = $this->responseContent['data'];

        $this->assertNotEmpty($responseData['id']);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
