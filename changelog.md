# dev branch changelog

### Migrations
Some foreign keys are defined including the user_id foreign key.
Migrations are configurable by changing the `\Laravel\Passport\PassportSchema::$config` array.

### Clients

#### Public clients
A public client does not logically has a secret, hence should never use it.
Public clients does not have access to grant types that require client authentication.
When requesting a password/authorization_code/refresh_token grant type, a secret is only required for non-public (confidential) clients.

#### Trusted clients
A trusted client is always authorized for all the scopes, hence no user approval is required when requesting a token/authorization-code.

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

#TODO
1. Delete personal access client feature.
2. Change passport:client console command.
3. Let clients can be identified by something other that auto-generated id.
