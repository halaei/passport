<?php

namespace Laravel\Passport;

use Illuminate\Database\QueryException;

class ClientRepository
{
    /**
     * Get a client by the given ID.
     *
     * @param  int  $id
     * @return Client|null
     */
    public function find($id)
    {
        return Client::find($id);
    }

    /**
     * Get an active client by the given ID.
     *
     * @param  int  $id
     * @return Client|null
     */
    public function findActive($id)
    {
        $client = $this->find($id);

        return $client && ! $client->revoked ? $client : null;
    }

    /**
     * Get the client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Client::where('user_id', $userId)
                        ->orderBy('name', 'asc')->get();
    }

    /**
     * Get the active client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeForUser($userId)
    {
        return $this->forUser($userId)->reject(function ($client) {
            return $client->revoked;
        })->values();
    }

    /**
     * Get the personal access token client for the application.
     *
     * @return Client
     */
    public function personalAccessClient()
    {
        if (Passport::$personalAccessClient) {
            return Client::find(Passport::$personalAccessClient);
        } else {
            return PersonalAccessClient::orderBy('id', 'desc')->firstOrFail()->client;
        }
    }

    /**
     * Store a new client.
     *
     * @param  int|null  $userId
     * @param  string  $name
     * @param  string[]  $redirect
     * @param  null|array  $scopes
     * @param  string|null  $id
     * @param  bool  $public
     * @param  bool  $personal
     * @param  bool  $password
     * @param  bool  $trusted
     * @return Client
     */
    public function create($userId, $name, array $redirect, $scopes, $id = null, $public = false, $personal = false, $password = false, $trusted = false)
    {
        $client = (new Client)->forceFill([
            'id' => $id ? $id : str_random(40),
            'user_id' => $userId,
            'name' => $name,
            'secret' => str_random(40),
            'redirect' => $redirect,
            'scopes' => $scopes,
            'public_client' => $public,
            'personal_access_client' => $personal,
            'password_client' => $password,
            'trusted_client' => $trusted,
            'revoked' => false,
        ]);

        for ($retry = 0;; $retry++) {
            try {
                $client->save();
                break;
            } catch (QueryException $e) {
                if ($retry < 3 && ! $id) {
                    $client->id = str_random(40);
                } else {
                    throw $e;
                }
            }
        }

        if ($client->personal_access_client) {
            (new PersonalAccessClient([
                'client_id' => $client->id,
            ]))->save();
        }

        return $client;
    }

    /**
     * Update the given client.
     *
     * @param  Client  $client
     * @param  string  $name
     * @param  string[]  $redirect
     * @return Client
     */
    public function update(Client $client, $name, $redirect)
    {
        $client->forceFill([
            'name' => $name, 'redirect' => $redirect,
        ])->save();

        return $client;
    }

    /**
     * Regenerate the client secret.
     *
     * @param  Client  $client
     * @return Client
     */
    public function regenerateSecret(Client $client)
    {
        $client->forceFill([
            'secret' => str_random(40),
        ])->save();

        return $client;
    }

    /**
     * Determine if the given client is revoked.
     *
     * @param  int  $id
     * @return bool
     */
    public function revoked($id)
    {
        $client = $this->find($id);

        return ! $client || $client->revoked;
    }

    /**
     * Delete the given client.
     *
     * @param  Client  $client
     * @return void
     */
    public function delete(Client $client)
    {
        $client->tokens()->update(['revoked' => true]);

        $client->forceFill(['revoked' => true])->save();
    }
}
