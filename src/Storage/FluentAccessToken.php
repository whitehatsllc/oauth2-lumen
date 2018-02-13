<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) whitehatsllc <info@whitehats.ae>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Whitehatsllc\OAuth2Server\Storage;

use Carbon\Carbon;
use Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity;
use Whitehatsleague\OAuth2\Server\Entity\ScopeEntity;
use Whitehatsleague\OAuth2\Server\Storage\AccessTokenInterface;

/**
 * This is the fluent access token class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentAccessToken extends AbstractFluentAdapter implements AccessTokenInterface
{

    /**
     * Get an instance of Entities\AccessToken.
     *
     * @param string $token The access token
     *
     * @return null|AbstractTokenEntity
     */
    public function get($token)
    {
        $result = $this->getConnection()->table('mah_oauth_access_tokens')
                ->where('mah_oauth_access_tokens.id', $token)
                ->first();

        if (is_null($result)) {
            return;
        }

        return (new AccessTokenEntity($this->getServer()))
                        ->setId($result->id)
                        ->setExpireTime((int) $result->expireTime);
    }

    /*
      public function getByRefreshToken(RefreshTokenEntity $refreshToken)
      {
      $result = $this->getConnection()->table('oauth_access_tokens')
      ->select('oauth_access_tokens.*')
      ->join('oauth_refresh_tokens', 'oauth_access_tokens.id', '=', 'oauth_refresh_tokens.access_token_id')
      ->where('oauth_refresh_tokens.id', $refreshToken->getId())
      ->first();

      if (is_null($result)) {
      return null;
      }

      return (new AccessTokenEntity($this->getServer()))
      ->setId($result->id)
      ->setExpireTime((int)$result->expire_time);
      }
     */

    /**
     * Get the scopes for an access token.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     *
     * @return array Array of \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(AccessTokenEntity $token, $clientId)
    {
        $result = $this->getConnection()->table('mah_oauth_client_scopes')
                ->select('mah_oauth_scopes.*')
                ->join('mah_oauth_scopes', 'mah_oauth_client_scopes.scopeId', '=', 'mah_oauth_scopes.id')
                ->where('mah_oauth_client_scopes.clientId', $clientId)
                ->get();

        $scopes = [];

        foreach ($result as $scope) {
            $scopes[] = (new ScopeEntity($this->getServer()))->hydrate([
                'id' => $scope->id,
                'description' => $scope->description,
            ]);
        }

        return $scopes;
    }

    /**
     * Creates a new access token.
     *
     * @param string $token The access token
     * @param int $expireTime The expire time expressed as a unix timestamp
     * @param string|int $sessionId The session ID
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity
     */
    public function create($token, $expireTime, $sessionId)
    {
        $this->getConnection()->table('mah_oauth_access_tokens')->insert([
            'id' => $token,
            'expireTime' => $expireTime,
            'sessionId' => $sessionId,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        return (new AccessTokenEntity($this->getServer()))
                        ->setId($token)
                        ->setExpireTime((int) $expireTime);
    }

    /**
     * Associate a scope with an access token.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     * @param \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity $scope The scope
     *
     * @return void
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $this->getConnection()->table('mah_oauth_access_token_scopes')->insert([
            'accessTokenId' => $token->getId(),
            'scopeId' => $scope->getId(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Delete an access token.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity $token The access token to delete
     *
     * @return void
     */
    public function delete(AccessTokenEntity $token)
    {
        $this->getConnection()->table('mah_oauth_access_tokens')
                ->where('mah_oauth_access_tokens.id', $token->getId())
                ->delete();
    }

}
