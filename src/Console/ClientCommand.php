<?php

namespace Laravel\Passport\Console;

use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;

class ClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:client
            {name : The name of the client}
            {--user : The user ID that the client be assigned to}
            {--redirect : Space separated list of redirect URLs}
            {--scopes : Space seperated list of scopes}
            {--public : Mark the client as public}
            {--personal : Mark the client as a personal access client}
            {--password : Mark the client as a password client}
            {--trusted : Mark the client as trusted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a client for issuing access tokens';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Passport\ClientRepository  $clients
     * @return void
     */
    public function handle(ClientRepository $clients)
    {
        $client = $clients->create(
            $this->option('user') ?: null,
            $this->argument('name'),
            $this->redirects(),
            $this->scopes(),
            (bool) $this->option('public'),
            (bool) $this->option('personal'),
            (bool) $this->option('password'),
            (bool) $this->option('trusted')
        );

        $this->info("New client ($client->name) created successfully.");
        $this->line('<comment>Client ID:</comment> '.$client->id);
        $this->line('<comment>Client secret:</comment> '.$client->secret);
    }

    /**
     * Get the list of redirect urls
     *
     * @return array
     */
    protected function redirects()
    {
        return preg_split('/\s+/', $this->option('redirect'), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get the list of scopes
     *
     * @return array|null
     */
    protected function scopes()
    {
        if ($this->hasOption('scopes')) {
            return preg_split('/\s+/', $this->option('scopes'), -1, PREG_SPLIT_NO_EMPTY);
        }

        return null;
    }
}
