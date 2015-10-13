<?php

class AdminZonesController extends AdminZonesControllerCore
{

	public function renderForm()
	{
		$minimum_order_type = 'text';
		if (Tools::getValue('id_zone') == 9) {
			$minimum_order_type = 'hidden';
		}
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Zones'),
				'image' => '../img/admin/world.gif'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name'),
					'name' => 'name',
					'size' => 33,
					'required' => true,
					'desc' => $this->l('Zone name (e.g. Africa, West Coast, Neighboring Countries)'),
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Active:'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Allow or disallow shipping to this zone')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Horaires:'),
					'name' => 'horaire',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'horaire_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'horaire_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Activer ou non les horaires de livraison pour cette zone')
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Abonnement:'),
					'name' => 'abonnement',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'abonnement_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'abonnement_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'desc' => $this->l('Activer ou non l\'abonnement pour cette zone')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Début'),
					'name' => 'h_start',
					'size' => 33,
					'required' => false,
					'desc' => $this->l('Heure de début de tournée (format hh:mm)'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Fin'),
					'name' => 'h_end',
					'size' => 33,
					'required' => false,
					'desc' => $this->l('Heure de fin de tournée (format hh:mm)'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Tranche'),
					'name' => 'tranche',
					'size' => 33,
					'required' => false,
					'desc' => $this->l('Tranche horaire (en minutes)'),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Créneau'),
					'name' => 'creneau',
					'size' => 33,
					'required' => false,
					'desc' => $this->l('Créneau horaire minimum (en minutes)'),
				),
                array(
                    'type' => 'text',
                    'label' => $this->l('Auto fermeture'),
                    'name' => 'h_auto_close',
                    'size' => 33,
                    'max'=>24,
                    'min'=>0,
                    'required' => true,
                    'desc' => $this->l('Heure à laquelle les commandes sont fermées pour la zone (nombre entre 0 à 24)'),
                ),
				array(
					'type' => $minimum_order_type,
					'label' => $this->l('Minimum Commande'),
					'name' => 'minimum_order',
					'size' => 33,
					'required' => false,
					'desc' => $this->l('Minimum requis pour passer une commande'),
				),
				array(
					'type' => 'hidden',
					'name' => 'calendar',
					'values' => array(
						array(
							'id' => 'calendar_val',
							'value' => 1
						)
					)
				),
				array(
					'type' => 'hidden',
					'name' => 'replacement_cheat',
					'values' => array(
						array(
							'id' => 'replacement_cheat',
							'value' => 1
						)
					)
				)
			)
		);

		if (Shop::isFeatureActive())
		{
			$this->fields_form['input'][] = array(
				'type' => 'shop',
				'label' => $this->l('Group shop association:'),
				'name' => 'checkBoxShopAsso',
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('Save   '),
			'class' => 'button'
		);

		// ORIGINAL FORM
		if (!$this->default_form_language)
			$this->getLanguages();

		if (Tools::getValue('submitFormAjax'))
			$this->content .= $this->context->smarty->fetch('form_submit_ajax.tpl');
		
		if ($this->fields_form && is_array($this->fields_form))
		{
			if (!$this->multiple_fieldsets)
				$this->fields_form = array(array('form' => $this->fields_form));

			// For add a fields via an override of $fields_form, use $fields_form_override
			if (is_array($this->fields_form_override) && !empty($this->fields_form_override))
				$this->fields_form[0]['form']['input'][] = $this->fields_form_override;

			$helper = new HelperForm($this);
			$this->setHelperDisplay($helper);
			$helper->submit_action = 'submitAdd'.$this->table.'AndStay';
			$helper->fields_value = $this->getFieldsValue($this->object);
			$helper->tpl_vars = $this->tpl_form_vars;
			!is_null($this->base_tpl_form) ? $helper->base_tpl = $this->base_tpl_form : '';
			if ($this->tabAccess['view'])
			{
				if (Tools::getValue('back'))
					$helper->tpl_vars['back'] = Tools::safeOutput(Tools::getValue('back'));
				else
					$helper->tpl_vars['back'] = Tools::safeOutput(Tools::getValue(self::$currentIndex.'&token='.$this->token));
			}
			$form = $helper->generateForm($this->fields_form);

			// Calendar

				// Vars
			$currentZone =  Tools::getValue('id_zone');
			$month =  Tools::getValue('month');
			$year =  Tools::getValue('year');
			// Si month et year définis
			if ($month && $year) {
				$d = new DateTime($year.'-'.$month);
			} else {
				$d = new DateTime();
			}
			// Creation des variables next month et previous month et des liens qui vont avec
			$d->modify( 'next month' );
			$nMonth =  $d->format( 'm' );
			$nYear =  $d->format( 'Y' );
			$d->modify( 'previous month' );
			$d->modify( 'previous month' );
			$pMonth =  $d->format( 'm' );
			$pYear =  $d->format( 'Y' );
			$link = $this->toolbar_btn["back"]["href"]."&updatezone&id_zone=".$currentZone;
			$linkP = $link."&month=".$pMonth."&year=".$pYear;
			$linkN = $link."&month=".$nMonth."&year=".$nYear;

			// genering html
			// var_dump($linkP,$linkN);
			$cal = '
			<div id="calendar_admin" class="margin-form" data-array="'.htmlspecialchars($this->fields_value['calendar']).'">
				<div class="nav">
					<a class="prev" href="'.$linkP.'">Précédent</a>
					<a class="next" href="'.$linkN.'">Suivant</a>
				</div>
				';

			// {"2013":{"04":[1,4,11,19,25]}}
			// {"04":[1,4,7,11,19,25]}
			// {"04":{"1":"-2","4":"-2","11":"-2","19":"-2","25":"-2"}}
			$opens = json_decode($this->fields_value['calendar']);
			if (!$month && !$year) {
				$month = date('m');
				$year = date('Y');
			}
			$op = (isset($opens->$year->$month)) ? $opens->$year->$month : array();
			$cal .= AdminZonesController::Calendrier($month, $year, $op);

			$cal .= '</div>';
            /*
			if ($currentZone == 9) {
				$this->addColumnZoneProche($cal);
			}
			if ($currentZone == 10) {
				$this->addColumnZoneGrande($cal);
			}
            */

			$cheat = str_replace('<input type="hidden" name="replacement_cheat" id="replacement_cheat" value="" />', $cal, $form);
			return $cheat;
		}
		// return AdminController::renderForm();
	}

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
		$html.= '<tr>';
        foreach ($DayNames as $d)
            $html.= '<th>'.$d.'</th>'; $html.= "</tr>\n";

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
		for ( ; $daycode<$firstday; $daycode++)
            $html.= '<td>&nbsp;</td>';

		// boucle sur tous les jours du mois :
		for ($numday = 1; $numday <= $days_in_month; $numday++, $daycode++) {
            //get date state
            $day_state = 0;
            if (isset($links->$numday))
                $day_state = $links->$numday;
            $day_class = '';
            if ($day_state == 1)
                 $day_class  = "open";
            if ($day_state == 2)
                $day_class  = "close";

            // si on en est au lundi (sauf le 1er),
			// on ferme la ligne précédente et on en ouvre une nouvelle 
			if ($daycode%7 == 1 && $numday != 1) $html.= "</tr>\n".'<tr>';
			// on ouvre la case (avec un style particulier s'il s'agit d'aujourd'hui)
			$class = ($numday == $today) ? 'today ' : '';
			$class .= $day_class;
			// $html.= '<td';
			// $html.= ($numday == $today ? ' class="today">' : '>');
			// $html.= (in_array($numday, $links) ? ' class="open">' : '>');

			$html.= '<td'.(!empty($class) ? ' class="'.$class.'"': '').' data-day="'.$numday.'" data-val="'.(isset($links->$numday) ? $links->$numday : 2).'">';


			// on affiche le numéro du jour
			$html.= '<div class="desc">
				<strong>'.$numday.' '.$title.'</strong> <br>
				<label for="day_state_auto_'.$numday.'">
				    <input type="radio" name="day_state_'.$numday.'" id="day_state_auto_'.$numday.'" value="0" '.($day_state == 0 ? 'checked="checked"':'').'>
				    Gestion automatique (default)
				</label>


				<label for="day_state_open_'.$numday.'">
				    <input type="radio" name="day_state_'.$numday.'" id="day_state_open_'.$numday.'" value="1" '.($day_state == 1 ? 'checked="checked"':'').'>
				     Ouvert
				</label>

				<label for="day_state_close_'.$numday.'">
				    <input type="radio" name="day_state_'.$numday.'" id="day_state_close_'.$numday.'" value="2" '.($day_state == 2 ? 'checked="checked"':'').'>
			         Fermé
			    </label>
			    <span class="okk">Ok</span>
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

		if (empty($this->errors))
		{
			$id = (int)Tools::getValue($this->identifier);

			/* Custom Insert Bdd */
			/*if ($id == 9) {
				$this->addCustomBddProche();
			}
			if ($id == 10) {
				$this->addCustomBddGrande();
			}*/
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

