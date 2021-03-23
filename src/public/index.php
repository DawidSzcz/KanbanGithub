<?php

require __DIR__ . '/../../vendor/autoload.php';

use factories\GitFactory;
use KanbanBoard\Authentication;
use KanbanBoard\GithubClient;
use KanbanBoard\Application;
use utils\Utilities;

$repositories = explode('|', Utilities::env('GH_REPOSITORIES'));
//$authentication = new \KanbanBoard\Login();
//$token = $authentication->login();
$github = new GithubClient();
$board = new Application($github, $repositories, new GitFactory(), ['waiting-for-feedback']);
$board->run();
$data = $board->getRawMilestones();
$m = new Mustache_Engine(
    [
        'loader' => new Mustache_Loader_FilesystemLoader('../views'),
    ]
);
echo $m->render('index', ['milestones' => $data]);
