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
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Whitehatsleague\OAuth2\Server\Entity\ClientEntity;
use Whitehatsleague\OAuth2\Server\Entity\SessionEntity;
use Whitehatsleague\OAuth2\Server\Storage\ClientInterface;

/**
 * This is the fluent client class.
 *
 * @author whitehatsllc <info@whitehats.ae>
 */
class FluentClient extends AbstractFluentAdapter implements ClientInterface
{

    /**
     * Limit clients to grants.
     *
     * @var bool
     */
    protected $limitClientsToGrants = false;

    /**
     * Create a new fluent client instance.
     *
     * @param \Illuminate\Database\ConnectionResolverInterface $resolver
     * @param bool $limitClientsToGrants
     */
    public function __construct(Resolver $resolver, $limitClientsToGrants = false)
    {
        parent::__construct($resolver);
        $this->limitClientsToGrants = $limitClientsToGrants;
    }

    /**
     * Check if clients are limited to grants.
     *
     * @return bool
     */
    public function areClientsLimitedToGrants()
    {
        return $this->limitClientsToGrants;
    }

    /**
     * Whether or not to limit clients to grants.
     *
     * @param bool $limit
     */
    public function limitClientsToGrants($limit = false)
    {
        $this->limitClientsToGrants = $limit;
    }

    /**
     * Get the client.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $grantType
     *
     * @return null|\Whitehatsleague\OAuth2\Server\Entity\ClientEntity
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $query = null;

        if (!is_null($redirectUri) && is_null($clientSecret)) {
            $query = $this->getConnection()->table('mah_oauth_clients')
                    ->select(
                            'mah_oauth_clients.id as id', 'mah_oauth_clients.secret as secret', 'mah_oauth_client_endpoints.redirectUri as redirect_uri', 'mah_oauth_clients.name as name')
                    ->join('mah_oauth_client_endpoints', 'mah_oauth_clients.id', '=', 'mah_oauth_client_endpoints.clientId')
                    ->where('mah_oauth_clients.id', $clientId)
                    ->where('mah_oauth_client_endpoints.redirectUri', $redirectUri);
        } elseif (!is_null($clientSecret) && is_null($redirectUri)) {
            $query = $this->getConnection()->table('mah_oauth_clients')
                    ->select(
                            'mah_oauth_clients.id as id', 'mah_oauth_clients.secret as secret', 'mah_oauth_clients.name as name')
                    ->where('mah_oauth_clients.id', $clientId)
                    ->where('mah_oauth_clients.secret', $clientSecret);
        } elseif (!is_null($clientSecret) && !is_null($redirectUri)) {
            $query = $this->getConnection()->table('mah_oauth_clients')
                    ->select(
                            'mah_oauth_clients.id as id', 'mah_oauth_clients.secret as secret', 'mah_oauth_client_endpoints.redirectUri as redirect_uri', 'mah_oauth_clients.name as name')
                    ->join('mah_oauth_client_endpoints', 'mah_oauth_clients.id', '=', 'mah_oauth_client_endpoints.clientId')
                    ->where('mah_oauth_clients.id', $clientId)
                    ->where('mah_oauth_clients.secret', $clientSecret)
                    ->where('mah_oauth_client_endpoints.redirectUri', $redirectUri);
        }

        if ($this->limitClientsToGrants === true && !is_null($grantType)) {
            $query = $query->join('mah_oauth_client_grants', 'mah_oauth_clients.id', '=', 'mah_oauth_client_grants.clientId')
                    ->join('mah_oauth_grants', 'mah_oauth_grants.id', '=', 'mah_oauth_client_grants.grantId')
                    ->where('mah_oauth_grants.id', $grantType);
        }

        $result = $query->first();

        if (is_null($result)) {
            return;
        }

        return $this->hydrateEntity($result);
    }

    /**
     * Get the client associated with a session.
     *
     * @param  \Whitehatsleague\OAuth2\Server\Entity\SessionEntity $session The session
     *
     * @return null|\Whitehatsleague\OAuth2\Server\Entity\ClientEntity
     */
    public function getBySession(SessionEntity $session)
    {
        $result = $this->getConnection()->table('mah_oauth_clients')
                ->select(
                        'mah_oauth_clients.id as id', 'mah_oauth_clients.secret as secret', 'mah_oauth_clients.name as name')
                ->join('mah_oauth_sessions', 'mah_oauth_sessions.clientId', '=', 'mah_oauth_clients.id')
                ->where('mah_oauth_sessions.id', '=', $session->getId())
                ->first();

        if (is_null($result)) {
            return;
        }

        return $this->hydrateEntity($result);
    }

    /**
     * Create a new client.
     *
     * @param string $name The client's unique name
     * @param string $id The client's unique id
     * @param string $secret The clients' unique secret
     *
     * @return string
     */
    public function create($name, $id, $secret)
    {
        return $this->getConnection()->table('mah_oauth_clients')->insertGetId([
                    'id' => $id,
                    'name' => $name,
                    'secret' => $secret,
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Hydrate the entity.
     *
     * @param $result
     *
     * @return \Whitehatsleague\OAuth2\Server\Entity\ClientEntity
     */
    protected function hydrateEntity($result)
    {
        $client = new ClientEntity($this->getServer());
        $client->hydrate([
            'id' => $result->id,
            'name' => $result->name,
            'secret' => $result->secret,
            'redirectUri' => (isset($result->redirectUri) ? $result->redirectUri : null),
        ]);

        return $client;
    }

}
