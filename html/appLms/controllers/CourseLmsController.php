<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

require_once(_base_ . '/lib/lib.json.php');


class CourseLmsController extends LmsController
{
    public function init()
    {
        require_once(_adm_ . '/lib/lib.field.php');

        /** @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->_mvc_name = "course";
        $this->permissions = array(
            'view' => true,
            'mod' => true
        );

        if (!Docebo::user()->isAnonymous()) {

            define('_PATH_COURSE', '/appLms/' . Get::sett('pathcourse'));

            require_once($GLOBALS['where_lms'] . '/lib/lib.levels.php');

        } elseif (!isset($_SESSION['idCourse'])) {
            errorCommunication($lang->def('_FIRSTACOURSE'));

        } else echo "You can't access";
    }

    public function infocourse()
    {
        checkPerm('view_info');
        $acl_man = Docebo::user()->getAclManager();
        $lang =& DoceboLanguage::createInstance('course');
        // $course = $GLOBALS['course_descriptor']->getAllInfo();
        $course = $GLOBALS['course_descriptor']->getAllInfo();
        $levels = CourseLevel::getLevels();

        $status_lang = array(
            0 => $lang->def('_NOACTIVE'),
            1 => $lang->def('_ACTIVE'),
            2 => $lang->def('_CST_CONFIRMED'),
            3 => $lang->def('_CST_CONCLUDED'),
            4 => $lang->def('_CST_CANCELLED'));

        $difficult_lang = array(
            'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
            'easy' => $lang->def('_DIFFICULT_EASY'),
            'medium' => $lang->def('_DIFFICULT_MEDIUM'),
            'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
            'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'));

        $subs_lang = array(
            0 => $lang->def('_COURSE_S_GODADMIN'),
            1 => $lang->def('_COURSE_S_MODERATE'),
            2 => $lang->def('_COURSE_S_FREE'),
            3 => $lang->def('_COURSE_S_SECURITY_CODE'));

        $course['difficulty_translate'] = $difficult_lang[$course['difficult']];


        if ($_SESSION['levelCourse'] >= 4) {
            $course['show_quota'] = true;
            $quota = [];
            $max_quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $actual_space = $GLOBALS['course_descriptor']->getUsedSpace();

            $actual_space = number_format(($actual_space / (1024 * 1024)), '2');

            $percent = 0;
            if ($max_quota > 0) {
                $percent = ($actual_space != 0 ? number_format((($actual_space / $max_quota) * 100), '2') : '0');
            }

            $quota['percent'] = $percent;
            $quota['actual_space'] = $actual_space;
            $quota['max_quota'] = $max_quota;
            $quota['unlimited'] = $max_quota == USER_QUOTA_UNLIMIT;

            $course['quota'] = $quota;
        }

        foreach ($levels as $key => $level) {

            if ($course['level_show_user'] & (1 << $key)) {
                $course['show_users'] = true;
                $users =& $acl_man->getUsersMappedData(Man_Course::getIdUserOfLevel($_SESSION['idCourse'], $key, $_SESSION['idEdition']));

                $course[$level] = ['name' => $level, 'users' => $users];
            }
        }

        $data = [
            'templatePath' => getPathTemplate(),
            'course' => $course
        ];

        // var_dump($course);
        $this->render('infocourse/infocourse', $data);
    }
}

