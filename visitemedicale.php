<?php
require 'config.php';
require_once 'class/visitemedicale.class.php';
require_once 'lib/visitemedicale.lib.php';

global $langs;

$langs->Load("visitemedicale@visitemedicale");

$PDOdb = new TPDOdb;

$action = __get('action', 'list');
$user_id = __get('fk_user');

// View
llxHeader('', $langs->trans('VisiteMedicale'),'','');

dol_fiche_head(null, 'visitemedicale', $langs->trans('VisiteMedicale'), 0, 'visitemedicale@visitemedicale');

switch ($action) {
	case 'new':
		$visite = new TVisiteMedicale;
		
		if (!empty(__get('date_visite'))) {
			$visite->date_visite = strtotime(str_replace('/', '-', __get('date_visite')));
		}
		
		_fiche_visite($PDOdb, $visite, 'new');
		break;
	case 'load_last':
		// Récupération de la dernière visite médicale
		$sql = '
			SELECT MAX(rowid) as rowid, MAX(date_visite) as date_visite, MAX(date_next_visite) as date_next_visite
			FROM ' . MAIN_DB_PREFIX . 'visitemedicale
			WHERE fk_user = ' . $user_id . '
			GROUP BY fk_user
			HAVING MAX(date_visite)
		';
		print $sql;
		$result = $PDOdb->Execute($sql);
		$v = $result->fetch(PDO::FETCH_OBJ);
		
		$visite = new TVisiteMedicale;
		$visite->load($PDOdb, $v->rowid);
		
		_fiche_visite($PDOdb, $visite);
		break;
	case 'view':
	case 'edit':
		if (!empty($_REQUEST['id'])) {
			$visite = new TVisiteMedicale;
			$visite->load($PDOdb, $_REQUEST['id']);
			
			_fiche_visite($PDOdb, $visite, $action);
		}
		break;
	case 'list':
		_liste_visites($PDOdb);
		break;
	case 'save':
		if (empty($_REQUEST['cancel'])) {
			$visite = new TVisiteMedicale;
			if(!empty($_REQUEST['id'])) $visite->load($PDOdb, $_REQUEST['id'], false);

			$TData = $_REQUEST;
			
			$TData['date_visite'] = str_replace('/', '-', $TData['date_visite']);
			$date = date('Y-m-d '.$TData['horaire_date_visite'], strtotime($TData['date_visite']));
			$TData['date_visite'] = $date;
			
			if (!empty($TData['date_next_visite'])) {
				$TData['date_next_visite'] = str_replace('/', '-', $TData['date_next_visite']);
				$date = date('Y-m-d '.$TData['horaire_date_next_visite'], strtotime($TData['date_next_visite']));
				$TData['date_next_visite'] = $date;
			}
			
			/*
			if (isset($TData['duree_avant_prochaine'])) {
				
				$TData['date_next_visite'] = date('d/m/Y H:i:s', strtotime($TData['date_visite'] . ' + ' . $TData['duree_avant_prochaine'] . 'month'));
			}
			*/
			$visite->set_values($TData);
			$visite->save($PDOdb);
			
			$visite = new TVisiteMedicale;
			if(!empty($_REQUEST['id'])) $visite->load($PDOdb, $_REQUEST['id'], false);
		}
		
		$redirect = dirname($_SERVER['PHP_SELF']) . '/visitemedicale.php';
		
		if(!empty($_REQUEST['id'])) $redirect .= '?action=view&id=' . $_REQUEST['id'];
		?>
		<script language="javascript">
			document.location.href="<?php echo $redirect; ?>";					
		</script>
		<?php
		break;
	default:
		break;	
}

dol_fiche_end();

llxFooter();

$db->close();

function _fiche_visite(&$PDOdb, &$visite, $mode = 'view') {
	global $db;
	
	$usr = new User($db);
	if (!empty($visite->fk_user)) {
		$usr->fetch($visite->fk_user);
	}
	
	if ($mode == 'new') {
		$sql = '
			SELECT rowid, lastname
			FROM ' . MAIN_DB_PREFIX . 'user
		';
		
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		$TUsers = array();
		foreach ($Tab as $u) {
			$TUsers[$u->rowid] = $u->lastname;
		}
	}
	
	$f = new Form($db);
	$form=new TFormCore($_SERVER['PHP_SELF'], 'form_visite' ,'POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('id', $visite->rowid);
	
	if ($mode == 'new' || $mode == 'edit') {
		echo $form->hidden('action', 'save');
	}
	
	$TBS = new TTemplateTBS();

	print $TBS->render('./tpl/fiche.tpl.php',
		array(),
		array(
			'visite' => array(
				'rowid' => $visite->rowid,
				'user' => ($mode == 'new' ? $form->combo('', 'fk_user', $TUsers, $visite->fk_user) : $usr->getnomurl(1)),
				'date_visite' => $form->calendrier('', 'date_visite', date('d/m/Y', $visite->date_visite), 12, 12) . ' ' . $form->timepicker('', 'horaire_date_visite', date('H:i', $visite->date_visite), 12, 12),
				'date_next_visite' => ($mode == 'new' ? $form->texte('', 'duree_avant_prochaine', 0, 8, 255) : $form->calendrier('', 'date_next_visite', date('d/m/Y', $visite->date_next_visite), 12, 12) . ' ' . $form->timepicker('', 'horaire_date_next_visite', date('H:i', $visite->date_next_visite), 12, 12)),
				'type' => $form->combo('', 'type', $visite->TType, $visite->type),
				'personnel' => $form->combo('', 'personnel', $visite->TPersonnel, $visite->personnel),
				'commentaire' => $form->zonetexte('', 'commentaire', $visite->commentaire, 50)
			)
			,'view'=>array(
				'mode' => $mode
			)
		)
	);
	
	//echo $form->end_form();
}

function _liste_visites(&$PDOdb) {
	global $db;
	
	$visite = new TVisiteMedicale;
	$r = new TSSRenderControler($visite);

	$THide = array('lastname', 'date_visite');
	
	$sql = '
		SELECT user.lastname, user.rowid as user_id, DATE_FORMAT(date_visite, "%d/%m/%Y %H:%i") AS date_visite, visitemedicale.rowid as visite_id, date_next_visite, type, personnel
		FROM ' . MAIN_DB_PREFIX . 'visitemedicale as visitemedicale
		INNER JOIN ' . MAIN_DB_PREFIX . 'user as user ON user.rowid = visitemedicale.fk_user
		ORDER BY date_visite;
	';

	$r->liste($PDOdb, $sql, array(
		'limit' => array(
			'nbLine' => '30'
		)
		,'subQuery' => array()
		,'link' => array(
			'user_id' => '<a href="' . DOL_URL_ROOT . '/user/fiche.php?id=@val@">@lastname@</a>',
			'visite_id' => '<a href="' . DOL_URL_ROOT . '/custom/visitemedicale/visitemedicale.php?action=view&id=@visite_id@">@date_visite@</a>'
		)
		,'translate' => array()
		,'hide' => $THide
		,'type' => array(
			'date_visite' => 'datetime'
			,'date_next_visite' => 'datetime'
		)
		,'liste' => array(
			'titre'=> 'Liste des visites médicales'
			,'image' => img_picto('','title.png', '', 0)
			,'picto_precedent' => img_picto('','back.png', '', 0)
			,'picto_suivant' => img_picto('','next.png', '', 0)
			,'messageNothing' => 'Aucune visite médicale.'
		)
		,'title' => array(
			'user_id' => 'Utilisateur'
			,'visite_id' => 'Date de la visite'
			,'date_next_visite' => 'Date prochaine visite'
			,'type' => 'Type de visite'
			,'personnel' => 'Personnel'
		)
	));
	
	//if ($user->rights->visitemedicale->create) {
		 print '<div class="tabsAction">';
		 print '<a class="butAction" href="visitemedicale.php?action=new">Créer une visite médicale</a>';
		 print '</div>';
	//}
}
