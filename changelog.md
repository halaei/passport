# dev branch changelog

### Migrations
Some foreign keys are defined including the user_id foreign key.
Migrations are configurable by changing the `\Laravel\Passport\PassportSchema::$config` array.

### Clients
Public clients and trusted clients are defined.
A public client does not logically has a secret, hence should never use it.
A trusted client is always authorized for all the scopes, hence no user approval is required when granting a token/authorization-code.

### Tests
Tests are improved and some issues are fixed.
