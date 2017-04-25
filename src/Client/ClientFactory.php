<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;

/**
 * Client Factory
 */
class ClientFactory {

	public function create($db, $type, $inputData) {

		$className = __NAMESPACE__ . '\\' . 'Client' .  ucfirst(strtolower($type));
		
		if ( !class_exists($className)) {
			throw new Exception("Client with class [$className] not found!");
		}
		
		return new $className($db, $inputData);	
	}
}