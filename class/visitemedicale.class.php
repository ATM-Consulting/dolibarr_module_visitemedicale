<?php

class TVisiteMedicale extends TObjetStd {
    function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'visitemedicale');
        parent::add_champs('date_visite,date_next_visite','type=date;index;');
		parent::add_champs('delai_next_visite', 'type=integer;');
        parent::add_champs('type,personnel','type=chaine;');
        parent::add_champs('commentaire','type=text;');
        parent::add_champs('fk_user','type=entier;index;');
        
        
        parent::_init_vars();
        parent::start();    
        
        $this->TPersonnel=array(
            'medecin'=>'Médecin'
            ,'infirmier'=>'Infirmier(e)'
        );
        
        $this->TType=array(
            'embauche'=> $langs->trans('Embauche'),
            'suite'=> $langs->trans('Suite maladie'),
            'autre'=> $langs->trans('Autre')
        );
         
    }
    
}
    