<?php

class AdminCalendarController extends AdminCalendarControllerCore
{
	

	function Calendrier($month,$year,$links) {
		$MonthNames = array(1 => "Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre");
		$monthname = $MonthNames[$month+0];
		$html="";
		// on ouvre la table
		$html.= '<table class="cal" cellspacing="10" data-year="'.$year.'" data-month="'.$month.'">';

		// Première ligne = mois et année ou link[0]
		$title = $monthname.' '.$year;
		$html.= '<tr><td colspan="7" class="cal_titre">'.$title.'</td>'."</tr>\n";

		// Seconde lignes = initiales des jours de la semaine
		$DayNames = array("L","M","M","J","V","S","D");
		$html.= '<tr>'; foreach ($DayNames as $d) $html.= '<th>'.$d.'</th>'; $html.= "</tr>\n";

		// On regarde si aujourd'hui est dans ce mois pour mettre un style particulier
		if ($year == date('Y') && $month == date('m'))
			$today = date('d');
		  else
			$today = 0;

		$time = mktime(0,0,0,$month,1,$year);	// timestamp du 1er du mois demandé
		$days_in_month = date('t',$time);		// nombre de jours dans le mois
		$firstday = date('w',$time);			// jour de la semaine du 1er du mois
		if ($firstday == 0) $firstday = 7;		// attention, en php, dimanche = 0

		$daycode = 1; // ($daycode % 7) va nous indiquer le jour de la semaine.
						// on commence par le lundi, c'est-à-dire 1.

		// on ouvre une première ligne pour le calendrier proprement dit :
		$html.= '<tr>';

		// on met des cases blanches jusqu'à la veille du 1er du mois :
		for ( ; $daycode<$firstday; $daycode++) $html.= '<td>&nbsp;</td>';

		// boucle sur tous les jours du mois :
		for ($numday = 1; $numday <= $days_in_month; $numday++, $daycode++) {
			// si on en est au lundi (sauf le 1er), 
			// on ferme la ligne précédente et on en ouvre une nouvelle 
			if ($daycode%7 == 1 && $numday != 1) $html.= "</tr>\n".'<tr>';
			// on ouvre la case (avec un style particulier s'il s'agit d'aujourd'hui)
			$class = ($numday == $today) ? 'today ' : '';
			$class .= (isset($links->$numday) ? 'open' : '');
			// $html.= '<td';
			// $html.= ($numday == $today ? ' class="today">' : '>');
			// $html.= (in_array($numday, $links) ? ' class="open">' : '>');

			$html.= '<td'.(!empty($class) ? ' class="'.$class.'"': '').' data-day="'.$numday.'" data-val="'.(isset($links->$numday) ? $links->$numday : 2).'">';
			// on affiche le numéro du jour
			$html.= '<div class="desc">
				<strong>'.$numday.' '.$title.'</strong> <br>
				Ouvert à la livraison <input type="checkbox" class="check" '.(isset($links->$numday) ? ' checked' : '').'> <br>
				Jours de différence avant la fin de commande <input type="text" class="text" value="'.(isset($links->$numday) ? $links->$numday : 2).'"> <span class="okk">Ok</span>
			</div>'; 
			$html.= $numday;
			// on ferme la case
			$html.= '</td>';
		}

		// on met des cases blanches pour completer la dernière semaine si besoin :
		for ( ; $daycode%7 != 1; $daycode++) $html.= '<td>&nbsp;</td>';

		// on ferme la dernière ligne, et la table.
		$html.= '</tr>'; $html.= "</table>\n\n";

		return $html;
	}

	public function addColumnZoneProche(&$str) {
		$procheFields = ZoneCustom::getListProche();
		$html = '<div id="cp-zone-list">
		<label>Code postaux</label>
		<table>
			<tr><th>Code postal</th><th>Minimum de commande</th><th>Livraison offerte</th><th>Abonnement</th></tr>';
		foreach ($procheFields as $key => $value) {
			$val = 0;
			$html.= '<tr><td><input type="text" name="cp[]" value="'.$value['cp'].'"></td><td><input type="text" name="minimum[]" value="'.$value['minimum'].'"></td><td><input type="text" name="free_shipping[]" value="'.$value['free_shipping'].'"></td><td><input class="cheat" type="checkbox" value="1" name="abonnement_by_cp_cheat[]"';
			if ($value['abonnement_by_cp']) {
				$html.= ' checked ';
				$val = 1;
			}
			$html.= '><input class="cheat_2" type="hidden" name="abonnement_by_cp[]" value="'.$val.'" /></td></tr>';
		}
		$html.= '<tr><td><input type="text" name="cp[]" value=""></td><td><input type="text" name="minimum[]" value=""></td><td><input type="text" name="free_shipping[]" value=""></td><td><input type="checkbox" name="abonnement_by_cp_cheat[]" class="cheat" value="1"><input class="cheat_2" type="hidden" name="abonnement_by_cp[]" value="0" /></td></tr>';
		$html .= '</table>
		<script>
			$("#cp-zone-list .cheat").on("click",function(){
				$(this).siblings(".cheat_2").val(this.checked | 0);
				console.log($(this).siblings(".cheat_2").val());
			});
		</script>
		</div>';
		$str .= $html;
	}

	public function addColumnZoneGrande(&$str) {
		$grandeFields = ZoneCustom::getListGrande();
		$html = '<div id="cp-zone-list">
		<label>Code postaux</label>
		<table>
			<tr><th>Code postal</th></tr>';
		foreach ($grandeFields as $key => $value) {
			$html.= '<tr><td><input type="text" name="cp[]" value="'.$value['cp'].'"></td></tr>';
		}
		$html.= '<tr><td><input type="text" name="cp[]" value=""></td></tr>';
		$html .= '</table></div>';
		$str .= $html;
	}

	public function setMedia()
	{
		$this->addCSS(_PS_CSS_DIR_.'admin.css', 'all');
		$this->addCSS(_THEME_CSS_DIR_.'zones.css', 'all');
		$admin_webpath = str_ireplace(_PS_ROOT_DIR_, '', _PS_ADMIN_DIR_);
		$admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath);
		$this->addCSS(__PS_BASE_URI__.$admin_webpath.'/themes/'.$this->bo_theme.'/css/admin.css', 'all');
		if ($this->context->language->is_rtl)
			$this->addCSS(_THEME_CSS_DIR_.'rtl.css');

		$this->addJquery();
		$this->addjQueryPlugin(array('cluetip', 'hoverIntent', 'scrollTo', 'alerts', 'chosen'));

		$this->addJS(array(
			_PS_JS_DIR_.'admin.js',
			_PS_JS_DIR_.'toggle.js',
			_PS_JS_DIR_.'tools.js',
			_PS_JS_DIR_.'ajax.js',
			_PS_JS_DIR_.'toolbar.js',
			_THEME_JS_DIR_.'zones.js'
		));

		if (!Tools::getValue('submitFormAjax'))
		{
			$this->addJs(_PS_JS_DIR_.'notifications.js');
			if (Configuration::get('PS_HELPBOX'))
				$this->addJS(_PS_JS_DIR_.'helpAccess.js');
		}

		// Execute Hook AdminController SetMedia
		Hook::exec('actionAdminControllerSetMedia', array());
	}

	public function addCustomBddProche() {
		$cps = Tools::getValue('cp');
		$minim = Tools::getValue('minimum');
		$freeShip = Tools::getValue('free_shipping');
		$abo = Tools::getValue('abonnement_by_cp');
		// var_dump($abo);die();
		$sql = 'INSERT INTO `'._DB_PREFIX_.'zone_proche` (`cp`, `minimum`, `free_shipping`, `abonnement_by_cp`) VALUES';
		// var_dump($cps);
		foreach ($cps as $key => $value) {
			if (!empty($value)) {
				$abo_ = 0;
				// var_dump($abo);
				if ($abo[$key]) {
					$abo_ = 1;
				}
				$sql .= '('.$cps[$key].', '.$minim[$key].', '.$freeShip[$key].', '.$abo_.')';
				if (isset($cps[$key+1]) and $cps[$key+1]) {
					$sql .= ', ';
				}
			}
		}
		// die();
		Db::getInstance()->execute('TRUNCATE `'._DB_PREFIX_.'zone_proche`');
		Db::getInstance()->execute($sql);
	}

	public function addCustomBddGrande() {
		$cps = Tools::getValue('cp');
		$minim = Tools::getValue('minimum');
		$freeShip = Tools::getValue('free_shipping');
		$sql = 'INSERT INTO `'._DB_PREFIX_.'zone_grande` (`cp`) VALUES';
		// var_dump($cps);
		foreach ($cps as $key => $value) {
			if (!empty($value)) {
				$sql .= '('.$cps[$key].')';
				if (isset($cps[$key+1]) and $cps[$key+1]) {
					$sql .= ', ';
				}
			}
		}
		Db::getInstance()->execute('TRUNCATE `'._DB_PREFIX_.'zone_grande`');
		Db::getInstance()->execute($sql);
	}

	public function processUpdate()
	{
		/* Checking fields validity */
		$this->validateRules();
                $pattern = '/^\d{4}[,]{1}\d{2}[,]{1}\d{2}+$/';
if (!preg_match($pattern, Tools::getValue('holiday')))
		{
			$this->errors[] = Tools::displayError('Format date yyyy,mm,dd example: 2014,06,03)');
		}elseif(sizeof(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'calendar_publicholiday`
			WHERE `holiday` ="'.Tools::getValue('holiday').'"
		'))>0){
                    $this->errors[] = Tools::displayError('Already saved this day');
                }elseif(date('Y,m,d') > Tools::getValue('holiday')){
                    $this->errors[] = Tools::displayError('day exceeded');
                }
                
                
				
		
		if (empty($this->errors))
		{
			$id = (int)Tools::getValue($this->identifier);

			/* Custom Insert Bdd */
			if ($id == 9) {
				$this->addCustomBddProche();
			}
			if ($id == 10) {
				$this->addCustomBddGrande();
			}
			/* /Custom Insert Bdd */

			/* Object update */
			if (isset($id) && !empty($id))
			{
				$object = new $this->className($id);
				if (Validate::isLoadedObject($object))
				{
					/* Specific to objects which must not be deleted */
					if ($this->deleted && $this->beforeDelete($object))
					{
						// Create new one with old objet values
						$object_new = $object->duplicateObject();
						if (Validate::isLoadedObject($object_new))
						{
							// Update old object to deleted
							$object->deleted = 1;
							$object->update();

							// Update new object with post values
							$this->copyFromPost($object_new, $this->table);
							$result = $object_new->update();
							if (Validate::isLoadedObject($object_new))
								$this->afterDelete($object_new, $object->id);
						}
					}
					else
					{
						$this->copyFromPost($object, $this->table);
						$result = $object->update();
						$this->afterUpdate($object);
					}

					if ($object->id)
						$this->updateAssoShop($object->id);

					if (!$result)
					{
						$this->errors[] = Tools::displayError('An error occurred while updating object.').
							' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
					}
					elseif ($this->postImage($object->id) && !count($this->errors) && $this->_redirect)
					{
						$parent_id = (int)Tools::getValue('id_parent', 1);
						// Specific back redirect
						if ($back = Tools::getValue('back'))
							$this->redirect_after = urldecode($back).'&conf=4';
						// Specific scene feature
						// @todo change stay_here submit name (not clear for redirect to scene ... )
						if (Tools::getValue('stay_here') == 'on' || Tools::getValue('stay_here') == 'true' || Tools::getValue('stay_here') == '1')
							$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&updatescene&token='.$this->token;
						// Save and stay on same form
						// @todo on the to following if, we may prefer to avoid override redirect_after previous value
						if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
							$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&update'.$this->table.'&token='.$this->token;
						// Save and back to parent
						if (Tools::isSubmit('submitAdd'.$this->table.'AndBackToParent'))
							$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'='.$parent_id.'&conf=4&token='.$this->token;

						// Default behavior (save and back)
						if (empty($this->redirect_after))
							$this->redirect_after = self::$currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=4&token='.$this->token;
					}
				}
				else
					$this->errors[] = Tools::displayError('An error occurred while updating object.').
						' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
		}
		$this->errors = array_unique($this->errors);
		if (!empty($this->errors))
		{
			// if we have errors, we stay on the form instead of going back to the list
			$this->display = 'edit';
			return false;
		}

		if (isset($object))
			return $object;
		return;
	}
}

