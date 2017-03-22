<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model;


class SignUpFormFactory
{
	use Nette\SmartObject;

	const PASSWORD_MIN_LENGTH = 6;

	/** @var FormFactory */
	private $factory;

	/** @var Model\UserManager */
	private $userManager;


	public function __construct(FormFactory $factory, Model\UserManager $userManager)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
	}


	/**
	 * @return Form
	 */
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		
                $form->addText('prezdivka','Přezdívka:')
                        ->setRequired('Zadejte prosím přezdívku');
                $form->addPassword('heslo','Heslo:')
                        ->setRequired('Zadejte prosím heslo')
                        ->setOption('description', 'Alespoň 6 znaků')
                        ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 6);
              
                
                $form->addEmail('email','E-mail:')
                        ->setRequired('Zadejte prosím e-mail');
                $form->addText('telefon','Telefonní číslo:')
                        ->setRequired('Zadejte prosím telefonní číslo')
                        ->addRule(Form::INTEGER, 'Telefon musí obsahovat pouze čísla');
                $form->addText('odkud','Odkud:')
                        ->setRequired('Zadejte odkud jste');

		$form->addSubmit('send', 'Zaregistrovat');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
			try {
				$this->userManager->add($values->prezdivka, $values->heslo, $values->email, $values->telefon, $values->odkud);
			} catch (Model\DuplicateNameException $e) {
				$form->addError('Přezdívka jíž existuje, zvolte si prosím jinou.');
				return;
			}
			$onSuccess();
		};
		return $form;
	}

}
