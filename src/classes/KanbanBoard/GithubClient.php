<?php

namespace KanbanBoard;

use Github\Client;
use Github\HttpClient\CachedHttpClient;

class GithubClient
{
    private $client;
    private $milestone_api;
    public static $account_name = 'ZielonyKazik';

    public function __construct($token, $account)
    {
        require '../../vendor/autoload.php';
        $this->account = $account;
        $this->client = new \Github\Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        $this->client->authenticate($token, \Github\Client::AUTH_HTTP_TOKEN);
        $this->milestone_api = $this->client->api('issues')->milestones();
    }

    public function milestones($repository)
    {
        return $this->milestone_api->all(static::$account_name, $repository);
    }

    public function issues($repository, $milestone_id)
    {
        $issue_parameters = array('milestone' => $milestone_id, 'state' => 'all');
        return $this->client->api('issue')->all(static::$account_name, $repository, $issue_parameters);
    }
}