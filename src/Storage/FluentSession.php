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
use Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity;
use Whitehatsleague\OAuth2\Server\Entity\ScopeEntity;
use Whitehatsleague\OAuth2\Server\Entity\SessionEntity;
use Whitehatsleague\OAuth2\Server\Storage\SessionInterface;

/**
 * This is the fluent session class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentSession extends AbstractFluentAdapter implements SessionInterface
{
    /**
     * Get a session from it's identifier.
     *
     * @param string $sessionId
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\SessionEntity
     */
    public function get($sessionId)
    {
        $result = $this->getConnection()->table('mah_oauth_sessions')
                    ->where('mah_oauth_sessions.id', $sessionId)
                    ->first();

        if (is_null($result)) {
            return;
        }

        return (new SessionEntity($this->getServer()))
               ->setId($result->id)
               ->setOwner($result->ownerType, $result->ownerId);
    }

    /**
     * Get a session from an access token.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AccessTokenEntity $accessToken The access token
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\SessionEntity
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $result = $this->getConnection()->table('mah_oauth_sessions')
                ->select('mah_oauth_sessions.*')
                ->join('mah_oauth_access_tokens', 'mah_oauth_sessions.id', '=', 'mah_oauth_access_tokens.sessionId')
                ->where('mah_oauth_access_tokens.id', $accessToken->getId())
                ->first();

        if (is_null($result)) {
            return;
        }

        return (new SessionEntity($this->getServer()))
               ->setId($result->id)
               ->setOwner($result->ownerType, $result->ownerId);
    }

    /**
     * Get a session's scopes.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\SessionEntity
     *
     * @return array Array of \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(SessionEntity $session)
    {
        // TODO: Check this before pushing
        $result = $this->getConnection()->table('mah_oauth_session_scopes')
                  ->select('mah_oauth_scopes.*')
                  ->join('mah_oauth_scopes', 'mah_oauth_session_scopes.scopeId', '=', 'mah_oauth_scopes.id')
                  ->where('mah_oauth_session_scopes.sessionId', $session->getId())
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
     * Create a new session.
     *
     * @param string $ownerType Session owner's type (user, client)
     * @param string $ownerId Session owner's ID
     * @param string $clientId Client ID
     * @param string $clientRedirectUri Client redirect URI (default = null)
     *
     * @return int The session's ID
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        return $this->getConnection()->table('mah_oauth_sessions')->insertGetId([
            'clientId' => $clientId,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId,
            'clientRedirectUri' => $clientRedirectUri,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Associate a scope with a session.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\SessionEntity $session
     * @param \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity $scope The scopes ID might be an integer or string
     *
     * @return void
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $this->getConnection()->table('mah_oauth_session_scopes')->insert([
            'sessionId' => $session->getId(),
            'scopeId' => $scope->getId(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Get a session from an auth code.
     *
     * @param \Whitehatsleague\OAuth2\Server\Entity\AuthCodeEntity $authCode The auth code
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\SessionEntity
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $result = $this->getConnection()->table('mah_oauth_sessions')
            ->select('mah_oauth_sessions.*')
            ->join('mah_oauth_auth_codes', 'mah_oauth_sessions.id', '=', 'mah_oauth_auth_codes.sessionId')
            ->where('mah_oauth_auth_codes.id', $authCode->getId())
            ->first();

        if (is_null($result)) {
            return;
        }

        return (new SessionEntity($this->getServer()))
               ->setId($result->id)
               ->setOwner($result->ownerType, $result->ownerId);
    }
}
