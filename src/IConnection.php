<?php declare(strict_types=1);


namespace Lossik\Device\Communication;


interface IConnection
{


	public function connect($ip, $login, $password);

	public function comm($com, $arr = []);

	public function version();

	public function Command($menu): ICommand;

}