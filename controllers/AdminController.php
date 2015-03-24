<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class AdminController extends ModuleAdminController
{
	public function actionViewPostOpDrugs()
	{
		Audit::add('admin','list',null,null,array('module'=>'OphTrOperationnote','model'=>'OphTrOperationnote_PostopDrug'));

		$this->render('postopdrugs');
	}

	public function actionViewIncisionLengthDefaults()
	{
		$this->render('incisionlengthdefaults');	
	}

	public function actionIncisionLengthDefaultAddForm()
	{
	        $default = new OphTrOperationnote_CataractIncisionLengthDefault;

		$sites = Site::model()->findAll();
		$siteList = array();

		foreach ($sites as $site)
		{
			$id = $site->id;
			$siteList[$id] = $site->name;
		}		

                if (!empty($_POST)) {
                        $default->attributes = $_POST['OphTrOperationnote_CataractIncisionLengthDefault'];

                        if (!$default->validate()) {
                                $errors = $default->getErrors();
                        } else {
                                if (!$default->save()) {
                                        throw new Exception("Unable to save drug: ".print_r($default->getErrors(),true));
                                }
				else
				{
					Audit::add('admin-OphTrOperationnote_PostopDrug','add',$default->id);
					$this->redirect('/OphTrOperationnote/admin/viewIncisionLengthDefaults');
				
				}
                        }
                }

                $this->render('/admin/incisionlengthdefaultaddform',array(
                        'default' => $default,
                        'errors' => @$errors,
			'siteList' => $siteList
                ));
	}

	public function actionAddIncisionLengthDefault()
	{
		$incisionLength = new OphTrOperationnote_IncisionLengthDefault;
		$incisionLength->value = $_POST['OphTrOperationnote_IncisionLengthDefaultValue'];

		if (!$incisionLength->save())
		{
			throw new Exception("Unable to save default incision length: ".print_r($incisionLength->getErrors(),true));
		}
		
		Audit::add('admin-OphTrOperationnote_IncisionLengthDefault','incisionLength',$incisionLength->id);
		$this->redirect('/OphTrOperationnote/admin/viewIncisionLengthDefault');
	}

	public function actionDeleteIncisionLengthDefault()
        {
                $result = 1;
                foreach (OphTrOperationnote_IncisionLengthDefault::model()->findAllByPk(@$_POST['defaultIncisionLength']) as $incisionLength) {
                        if (!$incisionLength->delete()) {
                                $result = 0;
                        } else {
                                Audit::add('admin','delete',$drug->id,null,array('module'=>'OphTrOperationnote','model'=>'OphTrOperationnote_IncisionLengthDefault'));
                        }
                }
                echo $result;
        }


	public function actionAddPostOpDrug()
	{
		$drug = new OphTrOperationnote_PostopDrug;

		if (!empty($_POST)) {
			$drug->attributes = $_POST['OphTrOperationnote_PostopDrug'];

			if (!$drug->validate()) {
				$errors = $drug->getErrors();
			} else {
				if (!$drug->save()) {
					throw new Exception("Unable to save drug: ".print_r($drug->getErrors(),true));
				}
				Audit::add('admin-OphTrOperationnote_PostopDrug','add',$drug->id);
				$this->redirect('/OphTrOperationnote/admin/viewPostOpDrugs');
			}
		}

		$this->render('/admin/addpostopdrug',array(
			'drug' => $drug,
			'errors' => @$errors,
		));
	}

	public function actionEditPostOpDrug($id)
	{
		if (!$drug = OphTrOperationnote_PostopDrug::model()->findByPk($id)) {
			throw new Exception("Drug not found: $id");
		}

		if (!empty($_POST)) {
			$drug->attributes = $_POST['OphTrOperationnote_PostopDrug'];

			if (!$drug->validate()) {
				$errors = $drug->getErrors();
			} else {
				if (!$drug->save()) {
					throw new Exception("Unable to save drug: ".print_r($drug->getErrors(),true));
				}

				Audit::add('admin-OphTrOperationnote_PostopDrug','edit',$id);

				$this->redirect('/OphTrOperationnote/admin/viewPostOpDrugs');
			}
		} else {
			Audit::add('admin-OphTrOperationnote_PostopDrug','view',$id);
		}

    $this->render('/admin/editpostopdrug',array(
      'drug' => $drug,
      'errors' => @$errors,
    ));
  }

	public function actionDeletePostOpDrugs()
	{
		$result = 1;
		foreach (OphTrOperationnote_PostopDrug::model()->findAllByPk(@$_POST['drugs']) as $drug) {
			if (!$drug->delete()) {
				$result = 0;
			} else {
				Audit::add('admin','delete',$drug->id,null,array('module'=>'OphTrOperationnote','model'=>'OphTrOperationnote_PostopDrug'));
			}
		}
		echo $result;
	}

	public function actionSortPostOpDrugs()
	{
		if (!empty($_POST['order'])) {
			foreach ($_POST['order'] as $i => $id) {
				if ($drug = OphTrOperationnote_PostopDrug::model()->findByPk($id)) {
					$drug->display_order = $i+1;
					if (!$drug->save()) {
						throw new Exception("Unable to save drug: ".print_r($drug->getErrors(),true));
					}
				}
			}
		}
	}
}
