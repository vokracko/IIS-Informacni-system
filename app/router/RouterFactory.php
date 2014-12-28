<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		// $router[] = new Route('doc.html', 'doc.html', Route::ONE_WAY); //TODO
		$router[] = new Route('<presenter>/<action>[/<id>]', 'User:login');
		return $router;
	}

}
