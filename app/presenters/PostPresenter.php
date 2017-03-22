<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\ArrayHash;
use Nette\Database\Connection;
use Nette\Security\IIdentity;
use Nette\Security\User;
use Nette\Utils\Image;
use Nette\Http\FileUpload;
use Nette\Utils\Random;
use Nette\Database\Table\ActiveRow;

class PostPresenter extends Nette\Application\UI\Presenter{
    
    
    private $database;
    private $user;
    
    
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    
    public function renderShow($carId){
        \Tracy\Debugger::barDump($carId);
        $car = $this->database->table('automobil')->get($carId);
        if (!$car){
            $this->error('Stránka nebyla nalezena');
        }
        $this->template->auto = $car;
        
        $this->template->uzivatel = $car->ref('uzivatel','id_uzivatele');
        $this->template->komentar = $car->related('komentar')->order('vytvoreno DESC');    
    }
    
    
    

    protected function createComponentCommentForm(){
       $form = new Form;
       $form->addTextArea('obsah','Dotaz pro prodejce:')
               ->setRequired();
       $form->addSubmit('send','Zveřejnit komentář');
       
       $form->onSuccess[] = [$this, 'commentFormSucceeded'];
       return $form;
   
    }
    
    public function commentFormSucceeded($form, $values){
        $carId = $this->getParameter('carId');
        $userID = $this->getUser()->getId();
       
        $this->database->table('komentar')->insert([
            'id_uzivatele' => $userID,
            'id_automobilu' => $carId,
            'obsah' => $values->obsah,     
        ]);

        $this->flashMessage('Komentář byl přidán','success');
        $this->redirect('this');     
    }
    
    
    
    protected function createComponentPostForm() {
        
        $form = new Form;
    
        $form->addSelect('znacka','Automobil:', brand)
                ->setPrompt('Zvolte značku');      
        $form->addText('model','Model:')->setRequired();
        $form->addText('rok_vyroby','Rok výroby:')->setRequired();
        $form->addText('cena','Cena:')->setRequired();
        $form->addText('vykon','Výkon:')->setRequired();
        $form->addSelect('palivo','Palivo:', fuel)
                ->setPrompt('Zvolte palivo')
                ->setRequired();
        $form->addText('najeto_km','Najeto:')->setRequired();
        $form->addTextArea('popis','Doplňující popis:');
        $form->addSelect('typ_vystaveni','Typ vystavení:',advertising)
                ->setPrompt('Zvolte typ vystavení')
                ->setRequired();
        $form->addUpload('image', 'Fotografie');
        
        $form->addSubmit('send', 'Zveřejnit');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];
        
        return $form; 
    }
    
    protected function createComponentPostFormPartOfCar(){
        
        $form = new Form;
        $form->addSelect('druh_autodilu','Typ dílu:',type)
                ->setPrompt('Zvolte typ autodílu')
                ->setRequired();
        $form->addSelect('znacka_autodilu','Značka:',brand)
                ->setPrompt('Zvolte značku')
                ->setRequired();
        $form->addText('model_autodilu','Model značky:')->setRequired();
        $form->addText('cena','Cena:')->setRequired();
        $form->addText('stav','Stav:');
        $form->addSelect('typ_vystaveni','Typ vystavení:',advertising)
                ->setPrompt('Zvolte typ vystavení')
                ->setRequired();
        
        $form->addSubmit('send', 'Zveřejnit');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];
        
        return $form;
        
    }
    
    public function postFormSucceededPartOfCar(){
        
    }
    //\Tracy\Debugger::barDump($values);
    public function postFormSucceeded ($form, $values){
        $carId = $this->getParameter('carId');
        $userID = $this->getUser()->getId();

        if ($carId){
            $car = $this->database->table('automobil')->get($carId);
            
            $car->update($values);
        } else {    
            $car = $this->database->table('automobil')->insert([
                        'znacka' => $values->znacka,
                        'model' => $values->model,
                        'rok_vyroby' => $values->rok_vyroby,
                        'cena' => $values->cena,
                        'vykon' => $values->vykon,
                        'palivo' => $values->palivo,
                        'najeto_km' => $values->najeto_km,
                        'popis' => $values->popis,
                        'typ_vystaveni' => $values->typ_vystaveni,
                        'id_uzivatele' => $userID,
                        //'id_auto_obrazek' => $imageID,
                        ]);
            if ($carId){
                $car = $this->database->table('automobil_obrazek')->get($carId);
                $car->update($values);
            } else {
                $car = $this->database->table('automobil_obrazek')->insert([
                    'adresaURL' => $values->image->move($this->context->parameters['wwwDir'] . '/images/' .$filename),
                    'id_automobilu' => $car,
                ]);
            } 
        }

        //z netu - zápis k adresáři
        //$file->move(UPLOAD_DIR . '/data/'. $file_name);
        //z netu - nette forum - zápis k adresáři
        //$file->move($this->context->parameters['wwwDir'] . '/vystavy/' . $values->exhibition .'/img/' . $type . '/' . $file_name);
        
            if (!empty($values->image->hasFile())){
                
                if(!$values->image->isImage()){
                $this->flashMessage('Soubor nelze nahrát');
                $this->redirect('this');
                }
                
                $filenameExploded = explode('.', $values->image->getSanitizedName());
                $extension = end($filenameExploded);
                $filename = md5(\Nette\Utils\Random::generate() . time()) . '.' . $extension;
                
                $fullImage = \Nette\Utils\Image::fromFile($values->image->getTemporaryFile());
                
                $path = '/www/images' . $filename;
                
                $fullImage->save(WWW_DIR . $path);
 
            $thumbnail = $fullImage->resize(400,NULL);
            $thumbnailFilename = md5(\Nette\Utils\Random::generate() . time()) . '-thumb.' . $extension;
                    
            $thumbnailPath = '/www/images/thumbnails/' . $thumbnailFilename;
            $thumbnail->save(WWW_DIR . $thumbnailPath);
            }
        $this->flashMessage('Váš inzerát byl vložen!', 'success');
        $this->redirect('show',$car->id_automobilu);
    }
    

    public function actionCreate(){
    if (!$this->getUser()->isLoggedIn()) {
        $this->redirect('Sign:in');
        }
    }
    
    public function actionEdit($carId){
        $car = $this->database->table('automobil')->get('$carId');
        if (!$car){
            $this->error("Inzerát nenalezen");
        }
        $this['postForm']->setDefaults($car->toArray());
    }
    


}

