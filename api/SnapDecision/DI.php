<?php

/**
 * Copyright (C) 2013 Tyler Romeo, Krzysztof Jordan, Nicholas Bevaqua
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace SnapDecision;

/**
 * Dependency injection container for the application
 */
class DI
{
	/**
	 * Construct the container
	 *
	 * @param \PDO $db Database object
	 * @param array $config SnapDecision configuration options
	 */
	public function __construct( \PDO $db, \Google_Client $google, array $config ) {
		$this->config = $config;
		$this->db = $db;
		$this->google = $google;
	}

	/**
	 * SnapDecision configuration options
	 *
	 * @var array
	 */
	public $config;

	/**
	 * Database object
	 *
	 * @var \PDO
	 */
	public $db;

	/**
	 * Google client
	 *
	 * @var \Google_Client
	 */
	public $google;
}
