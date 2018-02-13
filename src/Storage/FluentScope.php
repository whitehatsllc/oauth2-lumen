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

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Whitehatsleague\OAuth2\Server\Entity\ScopeEntity;
use Whitehatsleague\OAuth2\Server\Storage\ScopeInterface;

/**
 * This is the fluent scope class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentScope extends AbstractFluentAdapter implements ScopeInterface
{
    /*
     * Limit clients to scopes.
     *
     * @var bool
     */
    protected $limitClientsToScopes = false;

    /*
     * Limit scopes to grants.
     *
     * @var bool
     */
    protected $limitScopesToGrants = false;

    /**
     * Create a new fluent scope instance.
     *
     * @param \Illuminate\Database\ConnectionResolverInterface $resolver
     * @param bool|false $limitClientsToScopes
     * @param bool|false $limitScopesToGrants
     */
    public function __construct(Resolver $resolver, $limitClientsToScopes = false, $limitScopesToGrants = false)
    {
        parent::__construct($resolver);
        $this->limitClientsToScopes = $limitClientsToScopes;
        $this->limitScopesToGrants = $limitScopesToGrants;
    }

    /**
     * Set limit clients to scopes.
     *
     * @param bool|false $limit
     */
    public function limitClientsToScopes($limit = false)
    {
        $this->limitClientsToScopes = $limit;
    }

    /**
     * Set limit scopes to grants.
     *
     * @param bool|false $limit
     */
    public function limitScopesToGrants($limit = false)
    {
        $this->limitScopesToGrants = $limit;
    }

    /**
     * Check if clients are limited to scopes.
     *
     * @return bool|false
     */
    public function areClientsLimitedToScopes()
    {
        return $this->limitClientsToScopes;
    }

    /**
     * Check if scopes are limited to grants.
     *
     * @return bool|false
     */
    public function areScopesLimitedToGrants()
    {
        return $this->limitScopesToGrants;
    }

    /**
     * Return information about a scope.
     *
     * Example SQL query:
     *
     * <code>
     * SELECT * FROM oauth_scopes WHERE scope = :scope
     * </code>
     *
     * @param string $scope The scope
     * @param string $grantType The grant type used in the request (default = "null")
     * @param string $clientId The client id used for the request (default = "null")
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\ScopeEntity|null If the scope doesn't exist return false
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        $query = $this->getConnection()->table('mah_oauth_scopes')
                    ->select('mah_oauth_scopes.id as id', 'mah_oauth_scopes.description as description')
                    ->where('mah_oauth_scopes.id', $scope);

        if ($this->limitClientsToScopes === true && !is_null($clientId)) {
            $query = $query->join('mah_oauth_client_scopes', 'mah_oauth_scopes.id', '=', 'mah_oauth_client_scopes.scopeId')
                           ->where('mah_oauth_client_scopes.clientId', $clientId);
        }

        if ($this->limitScopesToGrants === true && !is_null($grantType)) {
            $query = $query->join('mah_oauth_grant_scopes', 'mah_oauth_scopes.id', '=', 'mah_oauth_grant_scopes.scopeId')
                           ->join('mah_oauth_grants', 'mah_oauth_grants.id', '=', 'mah_oauth_grant_scopes.grantId')
                           ->where('mah_oauth_grants.id', $grantType);
        }

        $result = $query->first();

        if (is_null($result)) {
            return;
        }

        $scope = new ScopeEntity($this->getServer());
        $scope->hydrate([
            'id' => $result->id,
            'description' => $result->description,
        ]);

        return $scope;
    }
}
