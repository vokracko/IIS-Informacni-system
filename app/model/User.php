<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


/**
 * Users management.
 */
class User extends Nette\Object implements Nette\Security\IAuthenticator
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table('user')->where('username = ?', $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Neplatné uživatelské jméno.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row['password'])) {
			throw new Nette\Security\AuthenticationException('Neplatné heslo.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row['password'])) {
			$row->update(array(
				'password' => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr['password']);
		return new Nette\Security\Identity($row['id'], $row['role'], $arr);
	}


	public function insert($values)
	{
		return $this->database->table('user')->insert(
			array(
			'username' => $values['username'],
			'password' => Passwords::hash($values['password']),
			'first_name' => $values['first_name'],
			'last_name' => $values['last_name'],
			'city_id' => $values['city_id'],
			'role' => $values['role'],
		));
	}

	public function update($id, $values)
	{
		return $this->database->table('user')->get($id)->update(
			array(
			'username' => $values['username'],
			'password' => Passwords::hash($values['password']),
			'first_name' => $values['first_name'],
			'last_name' => $values['last_name'],
			'city_id' => $values['city_id'],
			'role_id' => $values['role_id'],
		));
	}

	public function get($id)
	{
		return $this->database->table('user')->get($id);
	}

	public function getAll()
	{
		return $this->database->table('user')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('user')->where('username LIKE ? ', '%'.$keyword.'%');
	}

	public function changePassword($id, $password)
	{
		$this->database->table('user')->get($id)->update(array('password' => Passwords::hash($password)));
	}

}
