<?php

require __DIR__ . '/../../vendor/autoload.php';

use factories\GitFactory;
use KanbanBoard\Authentication;
use KanbanBoard\GithubClient;
use KanbanBoard\Application;
use utils\Utilities;

$repositories = explode('|', Utilities::env('GH_REPOSITORIES'));
$authentication = new Authentication();
$token = $authentication->login();
$github = new GithubClient($token, Utilities::env('GH_ACCOUNT'));
$board = new Application($github, $repositories, new GitFactory(), array('waiting-for-feedback'));
$board->run();

$m = new Mustache_Engine(
    array(
        'loader' => new Mustache_Loader_FilesystemLoader('../views'),
    )
);
echo $m->render('index', array('milestones' => $board->getRawMilestones()));
