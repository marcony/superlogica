<?php
/**
 * Created by PhpStorm.
 * User: Marcony
 * Date: 14/03/14
 * Time: 14:31
 */

require_once __DIR__."/../vendor/autoload.php";

$usuario = "";
$senha = "";
$conta = "";
//error_reporting(~E_NOTICE);
$api = new \Superlogica\Api('https://superlogica.net/financeiro/atual');
$api->login($usuario, $senha, $conta);

print_r($api);