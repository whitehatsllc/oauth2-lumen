<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) whitehatsllc <info@whitehats.ae>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Whitehatsllc\OAuth2Server\Middleware;

use Closure;
use Whitehatsleague\OAuth2\Server\Exception\InvalidScopeException;
use Whitehatsllc\OAuth2Server\Authorizer;

/**
 * This is the oauth middleware class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class OAuthMiddleware
{

    /**
     * The Authorizer instance.
     *
     * @var \Whitehatsllc\OAuth2Server\Authorizer
     */
    protected $authorizer;

    /**
     * Whether or not to check the http headers only for an access token.
     *
     * @var bool
     */
    protected $httpHeadersOnly = false;

    /**
     * Create a new oauth middleware instance.
     *
     * @param \Whitehatsllc\OAuth2Server\Authorizer $authorizer
     * @param bool $httpHeadersOnly
     */
    public function __construct(Authorizer $authorizer, $httpHeadersOnly = false)
    {
        $this->authorizer = $authorizer;
        $this->httpHeadersOnly = $httpHeadersOnly;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $scopesString
     *
     * @throws \Whitehatsleague\OAuth2\Server\Exception\InvalidScopeException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $scopesString = null)
    {
        $scopes = [];

        if (!is_null($scopesString)) {
            $scopes = explode('+', $scopesString);
        }

        $this->authorizer->setRequest($request);

        $this->authorizer->validateAccessToken($this->httpHeadersOnly);
        $this->validateScopes($scopes);

        return $next($request);
    }

    /**
     * Validate the scopes.
     *
     * @param $scopes
     *
     * @throws \Whitehatsleague\OAuth2\Server\Exception\InvalidScopeException
     */
    public function validateScopes($scopes)
    {
        if (!empty($scopes) && !$this->authorizer->hasScope($scopes)) {
            $InvalidScopeException = new InvalidScopeException(implode(',', $scopes));
            abort($InvalidScopeException->httpStatusCode, $InvalidScopeException->errorMessage);
        }
    }

}
