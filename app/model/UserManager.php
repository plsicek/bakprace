<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Object;
use Nette\Database\Context;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'uzivatel',
		COLUMN_ID = 'id_uzivatele',
		COLUMN_NAME = 'prezdivka',
		COLUMN_PASSWORD_HASH = 'heslo',
		COLUMN_EMAIL = 'email',
                COLUMN_PHONE = 'telefon',
                COLUMN_WHERE = 'odkud',
		COLUMN_ROLE = 'role';


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

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update([
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			]);
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);
		return new Nette\Security\Identity($row[self::COLUMN_ID],  $arr);
                //$row[self::COLUMN_ROLE], - patří před $arr
	}
         
        
        
       /* public function register($data){
            $data["prezdivka"] = "prezdivka";
            $data["heslo"] = "heslo";
            $data["email"] = "email";
            $data["telefon"] = "telefon";
            $data["odkud"] = "odkud"; 
            
            $this->database->table('uzivatel')->insert($data);
        }*/


	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws DuplicateNameException
	 */
        
        
	public function add($username, $password, $email, $phone, $where)
	{
		try {
			$this->database->table(self::TABLE_NAME)->insert([
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
				self::COLUMN_EMAIL => $email,
                                self::COLUMN_PHONE => $phone,
                                self::COLUMN_WHERE => $where,
                                
			]);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}

}



class DuplicateNameException extends \Exception
{}
