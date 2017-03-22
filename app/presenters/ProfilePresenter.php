<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Presenters;

use Nette;
use App\Forms;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Security\IIdentity;

Class ProfilePresenter extends Nette\Application\UI\Presenter {
    
    private $database;
    
    
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    public function renderShow($userID){
        $user = $this->database->table('uzivatel')->get($userID);
        if (!$user){
            $this->error('Uživatel nenelazen');
        }
        $this->template->uziv = $user;
        
        $this->template->hodnoceni_uzivatele = $user->related('hodnoceni_uzivatele')->order('vytvoreno DESC'); 
    }

    protected function createComponentEvaluateForm() {
        $form = new Form;
        
        $form->addTextArea('hodnoceni','Hodnocení uživatele:')
                ->setRequired();
        $form->addSubmit('send','Odeslat hodnocení');
        
        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    public function commentFormSucceeded($form, $values){
        
        $user2 = $this->getUser()->getId();
        $userID = $this->getParameter('userID');
        $this->database->table('hodnoceni_uzivatele')->insert([
            'hodnoceni' => $values->hodnoceni,
            'id_uzivatele' => $userID,
            'id_uzivatele2' => $user2,
        ]);
        
        $this->flashMessage('Komentář byl přidán','success');
        $this->redirect('this'); 
    }

    
}

