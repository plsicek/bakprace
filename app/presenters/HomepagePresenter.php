<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends Nette\Application\UI\Presenter
{       
        private $database;
        
        public function __construct(Nette\Database\Context $database) {
            $this->database = $database;
        }
        

	public function renderDefault()
	{
		$this->template->automobil = $this->database->table('automobil')
                        ->order('vytvoreno ASC');
	}

}
