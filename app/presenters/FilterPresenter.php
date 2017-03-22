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
use Nette\SmartObject;


class FilterPresenter extends Nette\Application\UI\Presenter{

    private $database;
    public $znacka;
    
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    public function getFilter(){
        return array(
            'znacka' => $this->znacka,
        );
    }
    
    
    //z nete fora - pokus
    public function createComponentFilterForm() {
        $fuel = ['benzin' => 'Benzín', 'nafta' => 'Nafta', 'lpg' => 'LPG'    
        ];
        $form = new Form;
    
        $form->addSelect('znacka','Automobil:', SELF::brand)
                ->setPrompt('Zvolte značku')
                ->setDefaultValue($this->znacka);
        $form->addText('model','Model:');
        $form->addText('rok_vyroby','Rok výroby:');
        $form->addText('cena','Cena:');
        $form->addText('vykon','Výkon:');
        $form->addSelect('palivo','Palivo:', $fuel)
                ->setPrompt('Zvolte palivo');
        $form->addText('najeto_km','Najeto:')->setRequired();
        $form->addSelect('typ_vystaveni','Typ vystavení:',SELF::advertising)
                ->setPrompt('Zvolte typ vystavení');

        
        $form->addSubmit('find', 'Hledat');
        $form->onSuccess[] = array($this, 'FilterFormSubmitted');
        
        return $form; 
        
    }
    //z nete fora - pokus
    public function FilterFormSubmitted($form){
        $values = $form->getValues();
        $this->redirect('this', array('znacka' => $values->znacka));
    }
    
    
    
    
}

