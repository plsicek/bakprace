<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignInFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;


	public function __construct(FormFactory $factory, User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('username', 'Přezdívka:')
			->setRequired('Vložte přezdívku.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Vložte heslo.');

		$form->addCheckbox('remember', 'Zůstat trvale přihlášen');

		$form->addSubmit('send', 'Přihlásit se');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->user->setExpiration($values->remember ? '2 minutes' : '1 minute');
				$this->user->login($values->username, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError('Chybně zadané heslo nebo uživatelské jméno.');
				return;
			}
			$onSuccess();
		};
		return $form;
	}

}
