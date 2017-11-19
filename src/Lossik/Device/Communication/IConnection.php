<?php


namespace Lossik\Device\Communication;


interface IConnection
{


	public function connect($ip, $login, $password);

	public function comm($com, $arr = []);

}