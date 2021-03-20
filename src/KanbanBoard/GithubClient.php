<?php

namespace KanbanBoard;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use utils\Utilities;

class GithubClient
{
    private $client;
    private $issues_api;
    private $account_name;

    public function __construct()
    {
        $this->account_name = Utilities::env('GH_USER_NAME');
        $password = Utilities::env('GH_PASSWORD');
        $this->client = new Client(
            new CachedHttpClient(['cache_dir' => '/tmp/github-api-cache'])
        );
        $this->client->authenticate($this->account_name, $password, Client::AUTH_HTTP_PASSWORD);
        $this->issues_api = $this->client->api('issues');
    }

    public function milestones($repository)
    {
        return $this->issues_api->milestones()->all($this->account_name, $repository);
    }

    public function issues($repository, $milestone_id)
    {
        $issue_parameters = ['milestone' => $milestone_id, 'state' => 'all'];
        return $this->issues_api->all($this->account_name, $repository, $issue_parameters);
    }
}