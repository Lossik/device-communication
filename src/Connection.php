<?php declare(strict_types=1);


namespace Lossik\Device\Communication;

const IMPOSSIBLE_LOGIN = 2;

class Connection implements IConnection
{


	/**
	 * @var IDefinition
	 */
	protected $definition;

	/**
	 * @var IOptions
	 */
	protected $options;


	protected $socket;

	protected $loged;

	protected $ip;


	/**
	 * @param IOptions $options
	 * @param IDefinition $definition
	 *
	 */
	public function __construct(IOptions $options, IDefinition $definition)
	{
		$this->options    = $options;
		$this->definition = $definition;
	}


	public function connect($ip, $login, $password)
	{
		$this->ip     = $ip;
		$this->socket = $this->definition->socket($this->options, $this->ip);

		if ($this->hasSocket()) {
			if (is_array($login) && is_array($password)) {
				$this->multilogin($login, $password);
			}
			else {
				$this->login($login, $password);
			}
		}
		if ($this->isLoged()) {
			return $this;
		}
		else {
			throw $this->error("Login impossible", IMPOSSIBLE_LOGIN);
		}
	}


	protected function hasSocket()
	{
		return is_resource($this->socket);
	}


	protected function multilogin(array $logins, array $passwords)
	{
		foreach ($logins as $login) {
			foreach ($passwords as $password) {
				if ($this->login($login, $password)) {
					return true;
				}
			}
		}

		return $this->isLoged();
	}


	protected function login($login, $password)
	{
		if ($this->isLoged()) {
			return true;
		}

		if (!$this->hasSocket()) {
			if (!$this->socket = $this->definition->socket($this->options, $this->ip)) {
				throw $this->error("", 2);
			}
		}
		$this->loged = $this->definition->login($this->socket, $login, $password);
		$this->loged || $this->disconnect();

		return $this->loged;
	}


	public function isLoged()
	{
		return $this->loged;
	}


	public function isConnected()
	{
		return is_resource($this->socket);
	}


	protected function error($str, $no)
	{
		return new RuntimeException($str, $no);
	}


	public function comm($com, $arr = [])
	{
		return $this->definition->comm($this->socket, $com, $arr);
	}


	public function __destruct()
	{
		$this->disconnect();
	}


	public function disconnect()
	{
		if (is_resource($this->socket)) {
			fclose($this->socket);
			$this->socket = null;
		}
	}


	public function version()
	{
		throw new LogicException();
	}


	public function Command($menu): ICommand
	{
		throw new LogicException();
	}
}
