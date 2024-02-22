<?php

namespace App\Service;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Akeneo\Pim\ApiClient\Stream\UpsertResourceListResponse;
use App\Entity\Tenant;
use App\PimApi\PimApiClientFromTenantFactory;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

class GoogleVisionService
{
    public function __construct(private PimApiClientFromTenantFactory $clientFactory)
    {
    }

    private function getLabelsForImage($imagePath)
    {
        $imageAnnotator = new ImageAnnotatorClient();

        $image = file_get_contents($imagePath);

        $response = $imageAnnotator->labelDetection($image);
        $labels = $response->getLabelAnnotations();

        $result = [];
        foreach ($labels as $label) {
            $result[] = $label->getDescription();
        }

        return $result;
    }

    public function detectLabelsOnProductImages(Tenant $tenant)
    {
        $client = $this->clientFactory->getClient($tenant);
        $attributePackshot = $client->getAttributeApi()->get('packshot');
        $assetFamilyCode = $attributePackshot['reference_data_name'];
        $assetFamily = $client->getAssetFamilyApi()->get($assetFamilyCode);
        $attributeAsMainMedia = $assetFamily['attribute_as_main_media'];

        $products = $this->getProductsWithImageWithoutLabels($client);

        foreach ($products as $product) {
            $productUuid = $product['uuid'];
            $assetCode = $product['values']['packshot'][0]['data'][0];
            $asset = $client->getAssetManagerApi()->get($assetFamilyCode, $assetCode);
            if (!isset($asset['values'][$attributeAsMainMedia])) {
                continue;
            }

            $tempFile = $this->extractAssetImage($client, $asset['values'][$attributeAsMainMedia][0]['data']);

            $labels = $this->getLabelsForImage($tempFile);

            $this->updateProduct($client, $productUuid, $labels);
        }

    }

    private function getProductsWithImageWithoutLabels($client, $familyCode)
    {
        $searchBuilder = new SearchBuilder();
        $searchBuilder
            ->addFilter('product_labels', 'EMPTY', null, ['scope' => 'ecommerce', 'locale' => 'en_US'])
            ->addFilter('packshot', 'NOT EMPTY')
            ->addFilter('family', 'IN', [$familyCode]);
        $searchFilters = $searchBuilder->getFilters();

        $products = $client->getProductApi()->all(100, ['search' => $searchFilters, 'attributes' => 'product_labels', 'scope' => 'ecommerce']);

        if (iterator_count($products) == 0) {
            return [];
        }

        return $products;
    }

    private function extractAssetImage(AkeneoPimClientInterface $client, string $assetDataCode)
    {
        $mediaFileResponse = $client->getAssetMediaFileApi()->download($assetDataCode);
        $mediaContent = $mediaFileResponse->getBody();

        $tempFile = tempnam('/tmp', 'assetGoogleVision');
        file_put_contents($tempFile, $mediaContent);

        return $tempFile;
    }

    private function updateProduct(AkeneoPimClientInterface $client, mixed $productUuid, array $labels)
    {
        $product = [
            'uuid' => $productUuid,
            'values' => [
                'product_labels' =>
                    [[
                        'scope' => 'ecommerce',
                        'locale' => 'en_US',
                        'data' => implode(',', $labels)
                    ]],
            ]
        ];

        $response = $client->getProductUuidApi()->upsert($productUuid, $product);

        $this->checkUpsertResponse($response);
    }

    private function checkUpsertResponse($response)
    {
        if (is_int($response) && $response < 400) {
            return;
        }

        if ($response instanceof UpsertResourceListResponse && $response->valid()) {
            return;
        }

        $errors = $success = [];
        foreach ($response as $row) {
            if (!is_null($row) && (int)$row['status_code'] < 400) {
                $success[] = $row;
                continue;
            }
            $errors[] = $row;
        }

        if ($errors) {
            throw new RuntimeException('The response is invalid: ' . json_encode($errors, JSON_PRETTY_PRINT));
        }

        return $success;
    }

}
