<?php
$Module = array( 'name' => 'Social User' );

$ViewList = array();

$ViewList['setting'] = array(
    'script' =>	'setting.php',
    'params' => array( "ID" ),
    'ui_context' => 'administration',
    'unordered_params' => array(),
    'functions' => array( 'setting' )
);

$ViewList['alert'] = array(
    'script' =>	'alert.php',
    'ui_context' => 'administration',
    'params' => array(),
    'functions' => array( 'signup' )
);

$ViewList['signup'] = array(
    'script' =>	'signup.php',
    'params' => array(),
    'functions' => array( 'signup' )
);

$ViewList['activate'] = array(
    'script' =>	'activate.php',
    'ui_context' => 'authentication',
    'params' => array( 'Hash', 'MainNodeID' ),
    'functions' => array( 'signup' )
);

$ViewList['zombies'] = array(
    'script' =>	'zombies.php',
    'ui_context' => 'administration',
    'params' => array( 'ZombieID' ),
    'functions' => array( 'setting' )
);


//$ViewList['login'] = array(
//    'script' =>	'login.php',
//    'params' => array(),
//    'functions' => array( 'login' )
//);

$FunctionList = array();
//$FunctionList['login'] = array();
$FunctionList['signup'] = array();
$FunctionList['setting'] = array();