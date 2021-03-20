<?php

require __DIR__ . '/../../vendor/autoload.php';

use factories\GitFactory;
use KanbanBoard\Authentication;
use KanbanBoard\GithubClient;
use KanbanBoard\Application;
use utils\Utilities;

$repositories = array('wunderwaffel');
//$authentication = new \KanbanBoard\Login();
//$token = $authentication->login();
$github = new GithubClient();
$board = new Application($github, $repositories, new GitFactory(), array('waiting-for-feedback'));
$board->run();
$data = $board->getRawMilestones();
$m = new Mustache_Engine(
    array(
        'loader' => new Mustache_Loader_FilesystemLoader('../views'),
    )
);
echo $m->render('index', array('milestones' => $data));
