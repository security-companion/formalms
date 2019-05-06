<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once _lib_ . '/TemplateController.php';
require_once _lms_ . '/lib/LMSTemplateModel.php';
require_once(_lms_.'/lib/lib.middlearea.php');

final class LMSTemplateController extends TemplateController {

    private $model;

    protected function __construct() {
        
        $this->model = new LMSTemplateModel();

        $this->setLayout($this->model->selectLayout());
        $this->templateFolder = _folder_lms_;

        parent::__construct();
    }

    public function show() {

        $this->showLogo();
        $this->showMenu();
        $this->showCart();
        $this->showProfile();
        $this->showHelpDesk(); // Temporary solution before helpdesk refactoring.
        
        parent::show();
    }

    private function showLogo() {
        
        $this->render('logo', 'logo', array(
            'user'          => $this->model->getUser()
          , 'logo'          => $this->model->getLogo()
          , 'currentPage'   => $this->model->getCurrentPage()
          , 'homePage'      => $this->model->getHomePage()
        ));
    }

    private function notGeneratedCertificates() {

      $sql_generated = 'SELECT DISTINCT(ca.id_certificate)
        FROM learning_certificate_assign AS ca 
        INNER JOIN learning_courseuser AS cu ON ca.id_user = cu.idUser AND ca.id_course = cu.idCourse 
        INNER JOIN learning_certificate_course AS cc ON ca.id_certificate = cc.id_certificate AND ca.id_course = cc.id_course
        WHERE cu.idUser = '.Docebo::user()->getIdSt();

      $generated = sql_query($sql_generated);
      
      $not_in = '';
      while($row = sql_fetch_array($generated)) {
        $not_in.= $row['id_certificate'].',';
      }
      $not_in = rtrim($not_in, ",");

      $sql_availables = 'SELECT COUNT(cc.id_certificate) AS count
        FROM learning_certificate_course AS cc
        INNER JOIN learning_courseuser AS cu ON cc.id_course = cu.idCourse
        WHERE cu.idUser = '.Docebo::user()->getIdSt().'
        AND cc.available_for_status = cu.status';

      if ($not_in) {
        $sql_availables.= ' AND cc.id_certificate NOT IN ('.$not_in.')';
      }

      $availables = sql_query($sql_availables);
      $availables = (int)sql_fetch_object($availables)->count;

      return $availables;
    }

    private function showMenu() {
        $ma = new Man_MiddleArea();

        $this->render('menu', 'main-menu', array(
            'user'          => $this->model->getUser()
          , 'menu'          => $this->model->getMenu()
          , 'currentPage'   => $this->model->getCurrentPage()
          , 'perm_certificate'   => $ma->currentCanAccessObj('mo_7')
          , 'notGeneratedCertificates'   => $this->notGeneratedCertificates()
        ));
    }

    private function showCart() {
        
        $this->render('cart', 'cart', array(
            'user'          => $this->model->getUser()
          , 'cart'          => $this->model->getCart()
          , 'currentPage'   => $this->model->getCurrentPage()
        ));
    }

    private function showProfile() {
        
        $this->render('profile', 'profile', array(
            'user'              => $this->model->getUser()
          , 'profile'           => $this->model->getProfile()
          , 'credits'           => $this->model->getCredits()
          , 'career'            => $this->model->getCareer()
          , 'subscribeCourse'   => $this->model->getSubscribeCourse()
          , 'news'              => $this->model->getNews()
          , 'languages'         => $this->model->getLanguages()
          , 'currentPage'       => $this->model->getCurrentPage()
        ));
    }

    private function showHelpDesk() {

        // Temporary solution before helpdesk refactoring.        
        $this->render('helpdesk_modal', 'helpdesk', array(
            'user'          => $this->model->getUser()
          , 'userDetails'   => $this->model->getUserDetails()
          , 'email'         => $this->model->getHelpDeskEmail()
          , 'currentPage'   => $this->model->getCurrentPage()
        ));
    }
}
