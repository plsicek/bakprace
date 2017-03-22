<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use Nette\Application\UI;
use Nette\Application\UI\Form;

class SignPresenter extends BasePresenter
{
    
    /** @var Forms\SignInFormFactory @inject */
	public $signInFactory;

	/** @var Forms\SignUpFormFactory @inject */
	public $signUpFactory;
        
	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
        
        
	protected function createComponentSignInForm()
	{
		return $this->signInFactory->create(function () {
			$this->redirect('Homepage:');
		});
	}

	/**
	 * Sign-up form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignUpForm()
	{

		return $this->signUpFactory->create(function () {
			$this->redirect('Homepage:');
		});
	}
        
        public function registrationFormSucceeded(UI\Form $form, $values){
        // ...
        $this->flashMessage('Byl jste úspěšně registrován.');
        $this->redirect('Homepage:');
    }


	public function actionOut()
	{
		$this->getUser()->logout();
                $this->flashMessage('Odhlášení bylo úspěšné.');
                $this->redirect('Homepage:default');
                
	}

}
