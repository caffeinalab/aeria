<?php

namespace Aeria\Config\Interfaces;

interface DriverInterface
{
	public function parse(string $path) : array;
	public function getDriverName() : string;
}