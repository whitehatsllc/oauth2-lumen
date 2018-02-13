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
use Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity;
use Whitehatsleague\OAuth2\Server\Entity\ScopeEntity;
use Whitehatsleague\OAuth2\Server\Storage\AuthCodeInterface;

/**
 * This is the fluent auth code class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentAuthCode extends AbstractFluentAdapter implements AuthCodeInterface
{

    /**
     * Get the auth code.
     *
     * @param  string $code
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity
     */
    public function get($code)
    {
        $result = $this->getConnection()->table('mah_oauth_auth_codes')
                ->where('mah_oauth_auth_codes.id', $code)
                ->where('mah_oauth_auth_codes.expireTime', '>=', time())
                ->first();

        if (is_null($result)) {
            return;
        }

        return (new AuthCodeEntity($this->getServer()))
                        ->setId($result->id)
                        ->setRedirectUri($result->redirectUri)
                        ->setExpireTime((int) $result->expireTime);
    }

    /**
     * Get the scopes for an access token.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
     *
     * @return array Array of \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $result = $this->getConnection()->table('mah_oauth_auth_code_scopes')
                ->select('mah_oauth_scopes.*')
                ->join('mah_oauth_scopes', 'mah_oauth_auth_code_scopes.scopeId', '=', 'mah_oauth_scopes.id')
                ->where('mah_oauth_auth_code_scopes.authCodeId', $token->getId())
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
     * Associate a scope with an access token.
     *
     * @param  \Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
     * @param  \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity $scope The scope
     *
     * @return void
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $this->getConnection()->table('mah_oauth_auth_code_scopes')->insert([
            'authCodeId' => $token->getId(),
            'scopeId' => $scope->getId(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Delete an access token.
     *
     * @param  \Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity $token The access token to delete
     *
     * @return void
     */
    public function delete(AuthCodeEntity $token)
    {
        $this->getConnection()->table('mah_oauth_auth_codes')
                ->where('mah_oauth_auth_codes.id', $token->getId())
                ->delete();
    }

    /**
     * Create an auth code.
     *
     * @param string $token The token ID
     * @param int $expireTime Token expire time
     * @param int $sessionId Session identifier
     * @param string $redirectUri Client redirect uri
     *
     * @return void
     */
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        $this->getConnection()->table('mah_oauth_auth_codes')->insert([
            'id' => $token,
            'sessionId' => $sessionId,
            'redirectUri' => $redirectUri,
            'expireTime' => $expireTime,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

}
