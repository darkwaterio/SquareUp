<?php

namespace SocialiteProviders\Stripe;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'SQUAREUP';

    protected $scopes = ['read_write'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://connect.squareup.com/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://connect.squareup.com/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://connect.squareup.com/oauth2/status',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $nickname = null;
        if (isset($user['settings']['dashboard']['display_name'])) { // 2019-02-19 API change
            $nickname = $user['settings']['dashboard']['display_name'];
        } elseif (isset($user['display_name'])) { // original location
            $nickname = $user['display_name'];
        }

        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $nickname,
            'name'     => null,
            'email'    => $user['email'] ?? null,
            'avatar'   => null,
        ]);
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $url = $this->getTokenUrl();
        $data = $this->getTokenFields($code);

        $response = $this->getHttpClient()->post($url, [
        'json' => $data,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Client ' . $this->clientSecret,
        ],
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    public function revokeAccessToken() {
        
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code);
    }
}
