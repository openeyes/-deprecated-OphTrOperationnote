<?php

class ReportController extends BaseController
{
	// disables the need to pass an argument to the constructor
	function __construct($blah=null)
	{
		parent::__construct($blah);
	}

	/*
		generates a cataract outcomes report

		inputs (all optional):

			firm_id
			surgeon_id
			assistant_id
			supervising_surgeon_id
			date_from
			date_to

		outputs:

			- number of cataracts (number)
			- age of patients (mean and range)
			- eyes (numbers and percentage for left/right)
			- final visual acuity (mean and range)
			- pc ruptures (number and percentage)
			- complications (number and percentage)
	*/
	public function reportCataractOperations($params=array())
	{
		$data = array();

		$where = '';

		@$params['firm_id'] and $where .= " and f.id = {$params['firm_id']}";

		$surgeon_where = '';
		foreach (array('surgeon_id','assistant_id','supervising_surgeon_id') as $field) {
			if (@$params[$field]) {
				if ($surgeon_where) $surgeon_where .= ' or ';
				$surgeon_where .= "s.$field = {$params[$field]}";
			}
		}

		$surgeon_where and $where .= " and ($surgeon_where)";

		if (preg_match('/^[0-9]+[\s\-][a-zA-Z]{3}[\s\-][0-9]{4}$/',@$params['date_from'])) {
			$params['date_from'] = Helper::convertNHS2MySQL($params['date_from']);
		}
		if (preg_match('/^[0-9]+[\s\-][a-zA-Z]{3}[\s\-][0-9]{4}$/',@$params['date_to'])) {
			$params['date_to'] = Helper::convertNHS2MySQL($params['date_to']);
		}
		@$params['date_from'] and $where .= " and e.datetime >= '{$params['date_from']}'";
		@$params['date_to'] and $where .= " and e.datetime <= '{$params['date_to']}'";

		$data['cataracts'] = 0;
		$data['eyes'] = array(
			'left' => array(
				'number' => 0,
			),
			'right' => array(
				'number' => 0,
			),
		);
		$data['age']['from'] = 200; // wonder if this will ever need to be changed..
		$data['age']['to'] = 0;
		$data['final_visual_acuity'] = array(
			'from' => 0,
			'to' => 0,
			'mean' => 0,
		);
		$data['pc_ruptures']['number'] = 0;
		$data['complications']['number'] = 0;

		$ages = array();

		if (!($db = Yii::app()->params['report_db'])) {
			$db = 'db';
		}

		foreach (Yii::app()->$db->createCommand()
			->select("pl.eye_id, p.dob, p.date_of_death, comp.id as comp_id, pc.id as pc_id")
			->from("et_ophtroperationnote_procedurelist pl")
			->join("et_ophtroperationnote_cataract c","pl.event_id = c.event_id")
			->join("event e","c.event_id = e.id")
			->join("et_ophtroperationnote_surgeon s","s.event_id = e.id")
			->join("episode ep","e.episode_id = ep.id")
			->join("firm f","ep.firm_id = f.id")
			->join("patient p","ep.patient_id = p.id")
			->leftJoin("et_ophtroperationnote_cataract_complication comp","comp.cataract_id = c.id")
			->leftJoin("et_ophtroperationnote_cataract_complication pc","pc.cataract_id = c.id and pc.complication_id = 11")
			->where("e.deleted = 0 and ep.deleted = 0 $where")
			->queryAll() as $row) {

			$data['cataracts']++;
			($row['eye_id'] == 1) ? $data['eyes']['left']['number']++ : $data['eyes']['right']['number']++;

			$age = Helper::getAge($row['dob'], $row['date_of_death']);
			$ages[] = $age; //this is taking ages

			if ($age < $data['age']['from']) {
				$data['age']['from'] = $age;
			}

			if ($age > $data['age']['to']) {
				$data['age']['to'] = $age;
			}

			$row['pc_id'] and $data['pc_ruptures']['number']++;
			$row['comp_id'] and $data['complications']['number']++;
		}

		if (count($ages) == 0) {
			$data['age']['from'] = 0;
		}

		$data['eyes']['left']['percentage'] = ($data['cataracts'] >0) ? number_format($data['eyes']['left']['number']/($data['cataracts']/100),2) : 0;
		$data['eyes']['right']['percentage'] = ($data['cataracts'] >0) ? number_format($data['eyes']['right']['number']/($data['cataracts']/100),2) : 0;
		$data['age']['mean'] = (count($ages) >0) ? number_format(array_sum($ages)/count($ages),2) : 0;
		$data['pc_ruptures']['percentage'] = ($data['cataracts'] >0) ? number_format($data['pc_ruptures']['number']/($data['cataracts']/100),2) : 0;
		$data['complications']['percentage'] = ($data['cataracts'] >0) ? number_format($data['complications']['number']/($data['cataracts']/100),2) : 0;
		$data['pc_rupture_average']['number'] = 0;
		$data['complication_average']['number'] = 0;

		if (!($db = Yii::app()->params['report_db'])) {
			$db = 'db';
		}

		foreach (Yii::app()->$db->createCommand()
			->select("pl.eye_id, p.dob, p.date_of_death, comp.id as comp_id, pc.id as pc_id")
			->from("et_ophtroperationnote_procedurelist pl")
			->join("et_ophtroperationnote_cataract c","pl.event_id = c.event_id")
			->join("event e","c.event_id = e.id")
			->join("et_ophtroperationnote_surgeon s","s.event_id = e.id")
			->join("episode ep","e.episode_id = ep.id")
			->join("firm f","ep.firm_id = f.id")
			->join("patient p","ep.patient_id = p.id")
			->leftJoin("et_ophtroperationnote_cataract_complication comp","comp.cataract_id = c.id")
			->leftJoin("et_ophtroperationnote_cataract_complication pc","pc.cataract_id = c.id and pc.complication_id = 11")
			->where("e.deleted = 0 and ep.deleted = 0")
			->queryAll() as $i => $row) {

			$row['pc_id'] and $data['pc_rupture_average']['number']++;
			$row['comp_id'] and $data['complication_average']['number']++;
		}

		$i++;

		$data['pc_rupture_average']['percentage'] = number_format($data['pc_rupture_average']['number']/($i/100),2);
		$data['complication_average']['percentage'] = number_format($data['complication_average']['number']/($i/100),2);

		return $data;
	}

	public function reportOperations($params=array())
	{
		$where = '';

		if (strtotime($params['date_from'])) {
			$where .= " and e.datetime >= '".date('Y-m-d',strtotime($params['date_from']))." 00:00:00'";
		}
		if (strtotime($params['date_to'])) {
			$where .= " and e.datetime <= '".date('Y-m-d',strtotime($params['date_to']))." 23:59:59'";
		}

		if ($user = User::model()->findByPk($params['surgeon_id'])) {
			$clause = '';
			if (@$params['match_surgeon']) {
				$clause .= "s.surgeon_id = $user->id";
			}
			if (@$params['match_assistant_surgeon']) {
				if ($clause) $clause .= ' or ';
				$clause .= "s.assistant_id = $user->id";
			}
			if (@$params['match_supervising_surgeon']) {
				if ($clause) $clause .= ' or ';
				$clause .= "s.supervising_surgeon_id = $user->id";
			}
			$where .= " and ($clause)";
		}

		if (!($db = Yii::app()->params['report_db'])) {
			$db = 'db';
		}

		foreach (Yii::app()->$db->createCommand()
			->select("p.hos_num, c.first_name, c.last_name, e.datetime, s.surgeon_id, s.assistant_id, s.supervising_surgeon_id, pl.id as pl_id, e.id as event_id, cat.id as cat_id, eye.name as eye")
			->from('patient p')
			->join('contact c',"c.parent_class = 'Patient' and c.parent_id = p.id")
			->join('episode ep','ep.patient_id = p.id')
			->join('event e','e.episode_id = ep.id')
			->join('et_ophtroperationnote_procedurelist pl','pl.event_id = e.id')
			->join('eye','pl.eye_id = eye.id')
			->join('et_ophtroperationnote_surgeon s','s.event_id = e.id')
			->leftJoin('et_ophtroperationnote_cataract cat','cat.event_id = e.id')
			->where("e.deleted = 0 and ep.deleted = 0 $where")
			->order('e.datetime asc')
			->queryAll() as $row) {

			$operations[] = array(
				'date' => date('j M Y',strtotime($row['datetime'])),
				'hos_num' => $row['hos_num'],
				'first_name' => $row['first_name'],
				'last_name' => $row['last_name'],
				'procedures' => array(),
				'complications' => array(),
				'role' => ($row['surgeon_id'] == $user->id ? 'Surgeon' : ($row['assistant_id'] == $user->id ? 'Assistant surgeon' : 'Supervising surgeon')),
			);

			foreach (ProcedureListProcedureAssignment::model()->findAll('procedurelist_id=?',array($row['pl_id'])) as $i => $pa) {
				$operations[count($operations)-1]['procedures'][] = array(
					'eye' => $row['eye'],
					'procedure' => $pa->procedure->term,
				);
			}

			if ($row['cat_id']) {
				foreach (CataractComplication::model()->findAll('cataract_id=?',array($row['cat_id'])) as $complication) {
					$operations[count($operations)-1]['complications'][] = array('complication'=>$complication->complication->name);
				}
			}
		}

		return array('operations'=>$operations);
	}
}
