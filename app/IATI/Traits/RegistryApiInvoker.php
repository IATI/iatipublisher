<?php

namespace App\IATI\Traits;

use App\Exceptions\PublisherNotFound;
use App\IATI\Repositories\ApiLog\ApiLogRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class RegistryApiInvoker.
 */
trait RegistryApiInvoker
{
    /**
     * Make an api request to the given action.
     *
     * @param $action
     * @param $requestParameter
     * @param $apiKey
     *
     * @return mixed
     */
    protected function request($action, $requestParameter = null, $apiKey = null): mixed
    {
        $clientConfig = ['base_uri' => env('IATI_API_ENDPOINT')];
        $requestConfig = [
            'http_errors' => false,
            'query'       => ['id' => $requestParameter ?? ''],
        ];

        if (env('APP_ENV') != 'production') {
            $clientConfig['headers']['X-CKAN-API-Key'] = env('IATI_API_KEY');
            $requestConfig['auth'] = [env('IATI_USERNAME'), env('IATI_PASSWORD')];
        }

        if ($apiKey) {
            $clientConfig['headers']['authorization'] = $apiKey;
        }

        $clientConfig['headers']['User-Agent'] = 'iati-publisher';

        $client = new Client($clientConfig);

        $res = $client->get(sprintf('%s/action/%s', env('IATI_API_ENDPOINT'), $action), $requestConfig);

        app(ApiLogRepository::class)->store(generateApiInfo('GET', sprintf('%s/action/%s', env('IATI_API_ENDPOINT'), $action), $requestConfig, $res));

        return $res->getBody()->getContents();
    }

    /**
     * Search for a publisher with a specific publisherId.
     *
     * @param $publisherId
     *
     * @return string
     *
     * @throws \Exception
     */
    public function searchForPublisher($publisherId): string
    {
        try {
            return $this->request('organization_show', $publisherId);
        } catch (\Exception $e) {
            if ($e instanceof RequestException) {
                if ($e->getResponse()->getStatusCode() == 404) {
                    throw new PublisherNotFound('Publisher not found');
                }
            }

            logger()->error($e);

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }
}
