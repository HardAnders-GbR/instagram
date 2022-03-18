<?php

declare(strict_types=1);

namespace Hardanders\Instagram\Client;

use Hardanders\Instagram\Domain\Model\Longlivedaccesstoken;
use Psr\Http\Message\RequestFactoryInterface;

final class InstagramApiClient
{
    private array $defaultMediaFields = [
        'id',
        'media_type',
        'thumbnail_url',
        'caption',
        'timestamp',
        'username',
        'media_url',
        'permalink',
    ];

    private RequestFactoryInterface $requestFactory;

    private string $accesstoken;

    private string $apiBaseUrl;

    public function __construct(RequestFactoryInterface $requestFactory, string $apiBaseUrl)
    {
        $this->requestFactory = $requestFactory;
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function getAuthorizationLink(string $instagramAppId, string $returnUri): string
    {
        return sprintf(
            '%s/oauth/authorize/?client_id=%s&redirect_uri=%s&response_type=code&scope=user_profile,user_media',
            $this->apiBaseUrl,
            $instagramAppId,
            $returnUri
        );
    }

    public function getUserdata(string $userId): array
    {
        $endpoint = sprintf(
            '%s/%s/?access_token=%s&fields=id,username',
            $this->apiBaseUrl,
            $userId,
            $this->accesstoken
        );

        return $this->request($endpoint);
    }

    public function getImages(string $userId): array
    {
        $endpoint = sprintf('%s/%s/media/?access_token=%s', $this->apiBaseUrl, $userId, $this->accesstoken);

        return $this->request($endpoint);
    }

    public function getMedia(int $mediaId, array $fields = null)
    {
        $fields = $fields ?? $this->defaultMediaFields;
        $fieldsString = strtolower(implode(',', $fields));

        $endpoint = sprintf(
            '%s/%s?fields=%s&access_token=%s',
            $this->apiBaseUrl,
            $mediaId,
            $fieldsString,
            $this->accesstoken
        );

        return $this->request($endpoint);
    }

    /**
     * @return int[]
     */
    public function getChildrenMediaIds(int $mediaId): array
    {
        $endpoint = sprintf('%s/%s/children?access_token=%s', $this->apiBaseUrl, $mediaId, $this->accesstoken);
        $response = $this->request($endpoint);

        $return = [];

        foreach ($response['data'] as $imageData) {
            $return[] = (int)$imageData['id'];
        }

        return $return;
    }

    public function updateLongLivedAccessToken(Longlivedaccesstoken $token)
    {
        $endpoint = sprintf(
            '%s/refresh_access_token/?grant_type=ig_refresh_token&access_token=%s',
            $this->apiBaseUrl,
            $token->getToken()
        );

        return $this->request($endpoint);
    }

    public function setAccesstoken(string $accesstoken): self
    {
        $this->accesstoken = $accesstoken;

        return $this;
    }

    public function getAccessToken(string $clientId, string $clientSecret, string $redirectUri, string $code): array
    {
        $endpoint = sprintf('%s/oauth/access_token', $this->apiBaseUrl);

        $queryParams = [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => rtrim($code, '#_'),
        ];

        $queryString = http_build_query($queryParams);

        $endpoint .= $queryString;

        return $this->request($endpoint, 'POST');
    }

    public function requestLongLivedAccessToken(
        string $clientSecret,
        string $accessToken
    ): array {
        $endpoint = sprintf(
            '%s/access_token/?grant_type=ig_exchange_token&client_secret=%s&access_token=%s',
            $this->apiBaseUrl,
            $clientSecret,
            $accessToken
        );

        return $this->request($endpoint);
    }

    /**
     * @throws \Exception
     *
     * @return mixed[]
     */
    private function request(string $url, string $method = 'GET', array $additionalOptions = []): array
    {
        $response = $this->requestFactory->request($url, $method, $additionalOptions);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception($response);
        }

        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }
}
