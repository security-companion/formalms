<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


/**
 * Class DashboardBlockCoursesLms
 */
class DashboardBlockCoursesLms extends DashboardBlockLms
{
	const MAX_COURSES = 3;
	const COURSE_TYPE_LIMIT = 3;

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_MEDIUM);
	}

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();

		$data['courses'] = $this->getCourses();
		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath(): string
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(): string
	{
		return $this->viewFile;
	}

	public function getLink(): string
	{
		return '#';
	}

	public function getRegisteredActions(): array
	{
		return [];
	}

	private function getCourses()
	{

		$conditions = [
			'cu.iduser = :id_user'
		];

		$params = [
			':id_user' => (int) Docebo::user()->getId()
		];

		// course status : all status, new, completed, in progress
		$conditions[] = '(c.status <> 3)';

		$elearningConditions = $conditions;
		$elearningConditions[] = "c.course_type = ':course_type'";
		$elearningConditions[] = 'c.date_begin != 0000-00-00';
		$elearningConditions[] = 'c.date_end != 0000-00-00';


		$elearningParams = $params;
		$elearningParams[':course_type'] = 'elearning';

		$courselist = $this->findAll($elearningConditions, $elearningParams, self::COURSE_TYPE_LIMIT);

		if (count($courselist) < self::COURSE_TYPE_LIMIT || count($courselist) < self::MAX_COURSES) {

			$classRoomConditions = $conditions;
			$classRoomConditions[] = "c.course_type = ':course_type'";

			$classRoomParams = $params;
			$classRoomParams[':course_type'] = 'classroom';

			$classRoomCourseList = $this->findAll($classRoomConditions, $classRoomParams, self::MAX_COURSES - count($courselist));

			foreach ($classRoomCourseList as $id => $course) {
				$courselist[$id] = $course;
			}
		}

		return $courselist;
	}

	private function findAll($conditions, $params, $limit = 0, $offset = 0)
	{
		$db = DbConn::getInstance();

		// exclude course belonging to pathcourse in which the user is enrolled as a student
		$learning_path_enroll = $this->getUserCoursePathCourses($params[':id_user']);
		$exclude_pathcourse = '';
		if (count($learning_path_enroll) > 1 && Get::sett('on_path_in_mycourses') == 'off') {
			$exclude_path_course = "select idCourse from learning_courseuser where idUser=" . $params[':id_user'] . " and level <= 3 and idCourse in (" . implode(',', $learning_path_enroll) . ")";
			$rs = $db->query($exclude_path_course);
			while ($d = $db->fetch_assoc($rs)) {
				$excl[] = $d['idCourse'];
			}
			$exclude_pathcourse = " and c.idCourse not in (" . implode(',', $excl) . " )";
		}

		$query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, c.img_course AS course_img_course '
			. ' ,cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting '
			. ' FROM %lms_course AS c '
			. ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
			. ' WHERE ' . $this->compileWhere($conditions, $params)
			. $exclude_pathcourse
			. ' ORDER BY c.idCourse';

		if ($limit > 0) {
			$query .= " LIMIT $limit";
		}
		if ($offset > 0) {
			$query .= " OFFSET $offset";
		}

		$rs = $db->query($query);

		$result = [];
		while ($course = $db->fetch_assoc($rs)) {

			$courseData = $this->getDataFromCourse($course);

			if ($courseData['type'] === 'classroom') {

				$dates = $this->getDatesForCourse($course);

				$courseData['dates'] = $dates;
			}

			$result[$course['course_id']] = $courseData;
		}

		return $result;
	}

	private function getUserCoursePathCourses($id_user)
	{
		require_once(_lms_ . '/lib/lib.coursepath.php');
		$cp_man = new Coursepath_Manager();
		$output = array();
		$cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
		if (!empty($cp_list)) {
			$cp_list = array_keys($cp_list);
			$output = $cp_man->getAllCourses($cp_list);
		}
		return $output;
	}

	private function compileWhere($conditions, $params)
	{

		if (!is_array($conditions)) return "1";

		$where = array();
		$find = array_keys($params);
		foreach ($conditions as $key => $value) {

			$where[] = str_replace($find, $params, $value);
		}
		return implode(" AND ", $where);
	}

	private function getDatesForCourse($course)
	{
		$db = DbConn::getInstance();

		$query = 'SELECT cd.id_date AS date_id ,cd.code AS date_code ,cd.name AS date_name ,cd.description AS date_description ,cd.status AS date_status ,cd.sub_start_date AS date_start_date ,cd.sub_end_date AS date_end_date'
			. ' FROM %lms_course_date AS cd '
			. ' WHERE cd.id_course = ' . $course['course_id']
			. ' AND cd.status <>3 '
            . ' AND cd.sub_end_date <> \'0000-00-00 00:00:00\' '
            . ' AND cd.sub_start_date <> \'0000-00-00 00:00:00\' '
			. ' ORDER BY cd.id_date';

		$rs = $db->query($query);

		$dates = [];
		while ($date = $db->fetch_assoc($rs)) {

			if ($date['date_start_date'] !== '0000-00-00 00:00:00') {
				$startDate = new DateTime($date['date_start_date']);
				$startDateString = $startDate->format('d/m/Y');
			} else {
				$startDateString = '';
			}

			if ($date['date_end_date'] !== '0000-00-00 00:00:00') {
				$endDate = new DateTime($date['date_end_date']);
				$endDateString = $endDate->format('d/m/Y');
			} else {
				$endDateString = '';
			}

			$dates[] = [
				'id' => $date['date_id'],
				'code' => $date['date_code'],
				'name' => $date['date_name'],
				'description' => $date['date_description'],
				'status' => $date['date_status'],
				'startDate' => $date['date_start_date'],
				'endDate' => $date['date_end_date'],
				'startDateString' => $startDateString,
				'endDateString' => $endDateString,
			];
		}
		return $dates;
	}
}
