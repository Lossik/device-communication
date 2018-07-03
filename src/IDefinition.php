<?php declare(strict_types=1);


namespace Lossik\Device\Communication;


interface IDefinition
{


	public function login($socket, $login, $password);


	public function comm($socket, $com, $arr = []);


	public function socket($options, $ip);


}