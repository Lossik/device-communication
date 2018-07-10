<?php declare(strict_types=1);


namespace Lossik\Device\Communication;


abstract class Command implements ICommand
{

	protected $menu;


	/**
	 * @var IConnection
	 */
	protected $connection;


	/**
	 * @param $menu
	 */
	public function __construct($menu)
	{
		$this->menu = $menu;
	}


	/**
	 * @param IConnection $connection
	 */
	public function setConnection(IConnection $connection): void
	{
		$this->connection = $connection;
	}


	public function getVersion()
	{
		return $this->connection->version();
	}

}