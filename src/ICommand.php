<?php declare(strict_types=1);


namespace Lossik\Device\Communication;


interface ICommand
{


	public function command($com, array $arr = []);


	public function get(array $where = [], $filterCallback = null, $onlyFirstItem = false);


	public function add(array $record);


	public function update(array $where, array $record, $filterCallback = null, $onlyFirstItem = false);


	public function del(array $where, $filterCallback = null, $onlyFirstItem = false);


}