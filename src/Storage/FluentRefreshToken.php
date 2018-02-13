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
use Whitehatsleague\OAuth2\Server\Entity\RefreshTokenEntity;
use Whitehatsleague\OAuth2\Server\Storage\RefreshTokenInterface;

/**
 * This is the fluent refresh token class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentRefreshToken extends AbstractFluentAdapter implements RefreshTokenInterface
{

    /**
     * Return a new instance of \Whitehatsleague\OAuth2\Server\Entity\RefreshTokenEntity.
     *
     * @param string $token
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\RefreshTokenEntity
     */
    public function get($token)
    {
        $result = $this->getConnection()->table('mah_oauth_refresh_tokens')
                ->where('mah_oauth_refresh_tokens.id', $token)
                ->where('mah_oauth_refresh_tokens.expireTime', '>=', time())
                ->first();

        if (is_null($result)) {
            return;
        }

        return (new RefreshTokenEntity($this->getServer()))
                        ->setId($result->id)
                        ->setAccessTokenId($result->accessTokenId)
                        ->setExpireTime((int) $result->expireTime);
    }

    /**
     * Create a new refresh token_name.
     *
     * @param  string $token
     * @param  int $expireTime
     * @param  string $accessToken
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\RefreshTokenEntity
     */
    public function create($token, $expireTime, $accessToken)
    {
        $this->getConnection()->table('mah_oauth_refresh_tokens')->insert([
            'id' => $token,
            'expireTime' => $expireTime,
            'accessTokenId' => $accessToken,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        return (new RefreshTokenEntity($this->getServer()))
                        ->setId($token)
                        ->setAccessTokenId($accessToken)
                        ->setExpireTime((int) $expireTime);
    }

    /**
     * Delete the refresh token.
     *
     * @param  \Whitehatsleague\OAuth2\Server\Entity\RefreshTokenEntity $token
     *
     * @return void
     */
    public function delete(RefreshTokenEntity $token)
    {
        $this->getConnection()->table('mah_oauth_refresh_tokens')
                ->where('id', $token->getId())
                ->delete();
    }

}
