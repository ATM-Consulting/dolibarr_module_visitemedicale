<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		core/boxes/mybox.php
 * 	\ingroup	visitemedicale
 * 	\brief		This file is a sample box definition file
 * 				Put some comments here
 */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 */
class visitemedicale_box extends ModeleBoxes
{

    public $boxcode = "visitemedicale2";
    public $boximg = "visitemedicale@visitemedicale";
    public $boxlabel;
    public $depends = array("visitemedicale");
    public $db;
    public $param;
    public $info_box_head = array();
    public $info_box_contents = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        global $langs;
        $langs->load("boxes");
        $langs->load("visitemedicale@visitemedicale");

        $this->boxlabel = $langs->transnoentitiesnoconv("VisitesMedicalesToPlannif");
    }

    /**
     * Load data into info_box_contents array to show array later.
     *
     * 	@param		int		$max		Maximum number of records to load
     * 	@return		void
     */
    public function loadBox($max = 5)
    {
        global $conf, $user, $langs, $db;

		if ($user->rights->visitemedicale->read_all || $user->rights->visitemedicale->read_own) {
			$this->max = $max;

	        //include_once DOL_DOCUMENT_ROOT . "/visitemedicale/class/visitemedicale.class.php";

	        $text = $langs->trans("VisitesMedicalesToPlannifDescrip", $max);
	        $this->info_box_head = array(
	            'text' => $text,
	            'limit' => dol_strlen($text)

	        );

	        define('INC_FROM_DOLIBARR',true);
	        dol_include_once('/visitemedicale/config.php');

	        $PDOdb = new TPDOdb();
	        $Tab = $PDOdb->ExecuteAsArray("SELECT u.rowid as fk_user 
	        ,(SELECT MAX(date_next_visite) FROM ".MAIN_DB_PREFIX."visitemedicale
	            WHERE fk_user=u.rowid
	        ) as date_next
	        ,(SELECT MAX(date_visite) FROM ".MAIN_DB_PREFIX."visitemedicale
	            WHERE fk_user=u.rowid
	        ) as date_last
	        
	        FROM ".MAIN_DB_PREFIX."user u WHERE statut=1 
	        ");

	        $this->info_box_contents=array();

	        foreach($Tab as $row) {
	            $u=new User($db);
	            $u->fetch($row->fk_user);

				if ($user->rights->visitemedicale->read_own && !$user->rights->visitemedicale->read_all && $u->id != $user->id)
					continue;

	            $t_next = strtotime($row->date_next);
	            $t_last = strtotime($row->date_last);


	            if($t_last>time() && $t_last<strtotime("+2month")) {
	                $date = date('d/m/Y', $t_last);
	                $url=dol_buildpath('/visitemedicale/visitemedicale.php?action=load_last&fk_user='.$u->id,1);
	                $statut = img_picto('','statut4');
	            }
	            else if($t_next<strtotime("+2month") ) { // la prochaine visite est dans moins de 2 mois

	                if($t_next<time()) {
	                    $date="En retard !";
	                    $url=dol_buildpath('/visitemedicale/visitemedicale.php?action=new&fk_user='.$u->id,1);
	                    $statut = img_picto('','statut8');
	                }
	                else{
	                    $date = date('d/m/Y', $t_next);
	                    $url=dol_buildpath('/visitemedicale/visitemedicale.php?action=new&fk_user='.$u->id.'&date_visite='.$date,1);
	                    $statut = img_picto('','statut0');
	                }


	            }
	            else{
	                continue;
	            }

	            $this->info_box_contents[] = array(
	                array(
	                    'td' => 'align="left"'
	                    ,'text' => $u->getFullName($langs)
	                    ,'url'=>dol_buildpath('/user/fiche.php?id='.$u->id,1)
	                    ,'logo'=>'user'
	                )
	                ,array(
	                    'td' => 'align="right"'
	                    ,'text' =>$date
	                    ,'url'=>$url
	                )
	                ,array(
	                    'td' => 'align="left"'
	                    ,'text' =>$statut
	                    ,'url'=>''

	                )
	            );

	        }

	        $this->info_box_contents[] =array(
	                   array(
	                    'td' => 'align="right" colspan="3"',
	                    'text' => "Gérer les visites médicales",
	                    'url' => dol_buildpath('/visitemedicale/visitemedicale.php',1)
	                   ));
		}
    }

    /**
     *    Method to show box
     *
     * @param array   $head Array with properties of box title
     * @param array   $contents Array with properties of box lines
     * @param boolean $noOutput
     *
     * @return    void
     */
    public function showBox($head = null, $contents = null, $noOutput=false)
    {
        parent::showBox($this->info_box_head, $this->info_box_contents, $noOutput);
    }
}