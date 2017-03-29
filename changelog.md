# dev branch changelog

### Migrations
Some foreign keys are defined including the user_id foreign key.
Migrations are configurable by changing the `\Laravel\Passport\PassportSchema::$config` array.

### Clients

#### Public and trusted clients
1. A public client does not logically has a secret, hence should never use it.
    - When requesting a password grant type, a secret is only required for non-public (confidential) clients.
2. A trusted client is always authorized for all the scopes, hence no user approval is required when requesting a token/authorization-code.

#### Grant types
Some changes are done for determining if a client can handle the given grant type.
Personal access grants and routes can be disabled by setting `Passport::$personalAccessGrantEnabled` to `false`.

#### Redirect URLs
A client can have multiple redirect urls.

#### Scopes
A client can be limited to specific set of scopes. By default all scopes are accessible.
To limit a client, change `scopes` of the corresponding record in the `oauth_clients` table into a json array.

### Tests
Tests are improved and some issues are fixed.
