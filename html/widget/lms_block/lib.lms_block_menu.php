<?php

defined ( "IN_FORMA" ) or die ( 'Direct access is forbidden.' );

/*
 * ======================================================================== \ | FORMA - The E-Learning Suite | | | | Copyright (c) 2013 (Forma) | | http://www.formalms.org | | License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt | | | | from docebo 4.0.5 CE 2008-2012 (c) docebo | | License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt | \ ========================================================================
 */
class Lms_BlockWidget_menu extends Widget {
	public $block_list = false;
	
	/**
	 * Constructor
	 * 
	 * @param <string> $config
	 *        	the properties of the table
	 */
	public function __construct() {
		parent::__construct ();
		$this->_widget = 'lms_block';
	}
	public function run() {
		require_once (_lms_ . '/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea ();
		$this->block_list = array ();
		if ($ma->currentCanAccessObj ( 'user_details_short' ))
			$this->block_list ['user_details_short'] = true;
		if ($ma->currentCanAccessObj ( 'user_details_full' ))
			$this->block_list ['user_details_full'] = true;
		if ($ma->currentCanAccessObj ( 'credits' ))
			$this->block_list ['credits'] = true;
		if ($ma->currentCanAccessObj ( 'news' ))
			$this->block_list ['news'] = true;
		if ($ma->currentCanAccessObj ( 'career' ))
			$this->block_list ['career'] = true;
		if ($ma->currentCanAccessObj ( 'course' ))
			$this->block_list ['course'] = true;
		
		if (isset ( $this->block_list ['user_details_full'] )) {
			
			/*
			 * echo '<div class="inline_block">' .'<div class="content">';
			 */
			$this->user_details_full ( $this->link );
			/*
			 * echo '</div>' .'</div>';
			 */
		}
		if (isset ( $this->block_list ['labels'] )) {
			echo '<div class="inline_block">';
			$this->label ( $this->link );
			echo '</div>';
		}
		if (isset ( $this->block_list ['credits'] )) {
			
			echo '<div class="inline_block">';
			$this->credits ( $this->link );
			echo '</div>';
		}
		if (isset ( $this->block_list ['news'] )) {
			
			echo '<div class="inline_block">';
			$this->news ( $this->link );
			echo '</div>';
		}
		// modifica box carriera
		if (isset ( $this->block_list ['career'] )) {
			
			echo '<div class="inline_block">';
			$this->career ();
			echo '</div>';
		}
		// modifica box iscrizione corso
		if (isset ( $this->block_list ['course'] )) {
			
			echo '<div class="inline_block">';
			$this->subscribe_course ();
			echo '</div>';
		}
		// END
	}
	
	public function career() {

	}
	
	// box iscrizione corso
	public function subscribe_course() {
		
        
        require_once (_base_ . '/lib/lib.form.php');
		

            $ma = new Man_MiddleArea ();
            if ($ma->currentCanAccessObj ( 'course' )){
            $html .=  '<div class="inline_block">';
                            $str_code = Lang::t('_TIT_SUBSCRIPTION_BY_CODE', 'catalogue');
		                    $form = new Form ();
		                    $op = $form->openForm ( 'course_autoregistration', 'index.php?modname=course_autoregistration&amp;op=subscribe' );
                            
                            $it = $form->getInputTextfield ( Lang::t('_LBL_CODE', 'standard'), 'course_autoregistration_code', 'course_autoregistration_code', '','',30, ' size=30 placeholder="'.$str_code.'"' );
		                    //$it = $form->getTextfield ( Lang::t('_LBL_CODE', 'standard'), 'course_autoregistration_code', 'course_autoregistration_code', '100');
                           // echo Form::getInputTextfield("search_t", $id."_filter_text", "filter_text", $filter_text, '', 255, 'placeholder='.$str_search );
		                    
                            $sb = $form->getButton ( 'subscribe_info', 'subscribe_info', Lang::t('_LBL_SEND', 'standard') );
		                    $cf = $form->closeForm ();
		                    
		                    $html .= '
                                             <div class="content">
                                    <div>
                                            <div class="form_line_l">
                                            <p><div >'. $op . $it . '' . $sb . $cf . '</div></p>      
                                ';
                                
                                
                                $html .= '</div>
                                
                                                   </div>
                                    </div>
                            </div>
                                ';
            }         
            
		//echo $html;
        return $html;
	}
	// END
	
	public function news($link) {
       
       $html = "";
            $ma = new Man_MiddleArea ();
            if ($ma->currentCanAccessObj ( 'news' )){
            
                    $html .= '<div class="inline_block">' ;        
                
		        $html  .= '<h2 class="heading">' . Lang::t ( '_NEWS', 'catalogue' ) . '</h2>' . '<div class="content">';
		        
		        $user_assigned = Docebo::user ()->getArrSt ();
		        
		        $query_news = "
		        SELECT idNews, publish_date, title, short_desc, important, viewer
		        FROM %lms_news_internal
		        WHERE language = '" . getLanguage () . "'
		        OR language = 'all'
		        ORDER BY important DESC, publish_date DESC ";
		        $re_news = sql_query ( $query_news );
		        
		        while ( list ( $id_news, $publish_date, $title, $short_desc, $impo, $viewer ) = sql_fetch_row ( $re_news ) ) {
			        
			        $viewer = (is_string ( $viewer ) && $viewer != false ? unserialize ( $viewer ) : array ());
			        $intersect = array_intersect ( $user_assigned, $viewer );
			        if (! empty ( $intersect ) || empty ( $viewer )) {
				        
				        $html .= '<h3>' . $title . '</h3>' . '<div class="news_textof">' . '<span class="news_data">' . Format::date ( $publish_date, 'date' ) . ' - </span>' . $short_desc . '</div>';
			        }
		        } // end news display
		        if (! sql_num_rows ( $re_news )) {
			        $html .= Lang::t ( '_NO_CONTENT', 'catalogue' );
		        }
		        $html .= '</div>';
        
        }
        
        
        
        return $html;
	}
	public function label() {
		require_once (_lms_ . '/admin/models/LabelAlms.php');
		$label_model = new LabelAlms ();
		
		echo '<h2 class="heading">' . Lang::t ( '_LABEL', 'catalogue' ) . '</h2>' . '<div class="content">' . Form::openForm ( 'label_form', 'index.php?r=elearning/show' ) . Form::getDropdown ( Lang::t ( '_LABELS', 'catalogue' ), 'id_common_label_dd', 'id_common_label', $label_model->getDropdownLabelForUser ( Docebo::user ()->getId () ), ($_SESSION ['id_common_label'] == - 1 ? - 2 : $_SESSION ['id_common_label']) ) . Form::closeForm () . '<script type="text/javascript">' . 'var dd = YAHOO.util.Dom.get(\'id_common_label_dd\');' . 'YAHOO.util.Event.onDOMReady(YAHOO.util.Event.addListener(dd, "change", function(e){var form = YAHOO.util.Dom.get(\'label_form\');form.submit();}));' . '</script>' . '</div>';
	}
	public function credits() {
		$str = '<h2 class="heading">' . Lang::t ( '_CREDITS', 'catalogue' ) . '</h2>' . '<div class="content">';
		$period_start = '';
		$period_end = '';
		
		// extract checking period
		$year = date ( "Y" );
		$p_list = array ();
		$p_selected = Get::req ( 'credits_period', DOTY_INT, 0 );
		$p_res = sql_query ( "SELECT * FROM " . $GLOBALS ['prefix_lms'] . "_time_period ORDER BY end_date DESC, start_date DESC" );
		if (sql_num_rows ( $p_res ) > 0) {
			while ( $obj = sql_fetch_object ( $p_res ) ) {
				if ($p_selected == 0)
					$p_selected = $obj->id_period;
				$p_list [$obj->id_period] = Format::date ( $obj->start_date, 'date' ) . ' - ' . Format::date ( $obj->end_date, 'date' );
				if ($p_selected == $obj->id_period) {
					$period_start = $obj->start_date;
					$period_end = $obj->end_date;
				}
			}
		}
		
		if (count ( $p_list ) <= 0)
			$p_list ['0'] = Lang::t ( '_NO_PERIODS', 'catalogue' );
		if (! array_key_exists ( $p_selected, $p_list ))
			$p_selected = 0;
		if ($p_selected == 0)
			$p_selected = false;
			
			// extract courses which have been completed in the considered period and the credits associated
		$course_type_trans = getCourseTypes ();
		$query = "SELECT c.idCourse, c.name, c.course_type, c.credits, cu.status " . " FROM " . $GLOBALS ['prefix_lms'] . "_course as c " . " JOIN " . $GLOBALS ['prefix_lms'] . "_courseuser as cu " . " ON (cu.idCourse = c.idCourse) WHERE cu.idUser=" . ( int ) getLogUserId () . " AND c.course_type IN ('" . implode ( "', '", array_keys ( $course_type_trans ) ) . "') " . " AND cu.status = '" . _CUS_END . "' " . ($period_start != '' ? " AND cu.date_complete > '" . $period_start . "' " : "") . ($period_end != '' ? " AND cu.date_complete < '" . $period_end . "' " : "") . " ORDER BY c.name";
		$res = sql_query ( $query );
		
		$course_data = array ();
		while ( $obj = sql_fetch_object ( $res ) ) {
			switch ($obj->course_type) {
				case 'elearning' :
					$course_data ['elearning'] [$obj->idCourse] = $obj;
					break;
				case 'classroom' :
				case 'blended' :
					$course_data ['classroom'] [$obj->idCourse] = $obj;
					break;
			}
		}
		
		// date dropdown
		$onchange = ' onchange="javascript:this.form.submit();"';
		$form = new Form ();
		
		$str =  $str. $form->openForm ( 'credits_period_form', 'index.php?r=' . $this->link, false, 'GET' ) . $form->getDropdown ( Lang::t ( '_TIME_PERIODS', 'menu' ), 'credits_period', 'credits_period', $p_list, $p_selected, '', '', $onchange ) . $form->closeForm ();
		
		// draw tables
		$no_cdata = true;
		foreach ( $course_data as $ctype => $cdata ) {
			
			if (count ( $cdata ) > 0) {
				
				$no_cdata = false;
				$str = $str.'<div class="yui-dt">' . '<table class="table-view" summary="' . Lang::t ( '_CREDITS', 'catalogue' ) . '">' . '<caption>' . $course_type_trans [$ctype] . '</caption>' . '<thead><tr clas="yui-dt-odd">' . '<th class="course_name"><div class="yui-dt-liner"><span class="yui-dt-label">' . Lang::t ( '_COURSE', 'catalogue' ) . '</span></div></th>' . '<th class="image"><div class="yui-dt-liner"><span class="yui-dt-label">' . Lang::t ( '_CREDITS', 'catalogue' ) . '</span></div></th>' . '</tr></thead>' . '<tbody>';
				
				$total = 0;
				$i = 0;
				foreach ( $cdata as $id_course => $data ) {
					
					if ($data->credits > 0) {
						$str = $str.'<tr class="yui-dt-' . ($i % 2 ? 'odd' : 'even') . '">' . '<td class="course_name"><div class="yui-dt-liner">' . $data->name . '</div></td>' . '<td class="image"><div class="yui-dt-liner">' . $data->credits . '</div></td>' . '</tr>';
						$total += $data->credits;
						$i ++;
					}
				}
				
				$str = $str.'</tbody>' . '<tfoot><tr class="yui-dt-' . ($i % 2 ? 'odd' : 'even') . '">' . '<td><div class="yui-dt-liner"><b>' . Lang::t ( '_TOTAL', 'catalogue' ) . '<b></div></td>' . '<td class="image"><div class="yui-dt-liner"><span class="yui-dt-label"><b>' . $total . '</b></div></td>' . '</tr></tfoot></table>' . '</div>';
			}
		} // end foreach
		
		if ($no_cdata) {
			$str = $str.'<p>' . Lang::t ( '_NO_CONTENT', 'catalogue' ) . '</p>';
		}
		
		$str = $str.'</div>';    
        
        return $str;
        
	}
	public function user_details_full($link) {
		require_once (_lib_ . '/lib.user_profile.php');
		$profile = new UserProfile ( getLogUserId () );
		$profile->init ( 'profile', 'framework', 'index.php?r=' . $link, 'ap' );
		echo $profile->homeUserProfile ( 'normal', false, false );
	}
	public function user_details_short($link) {
		require_once (_lib_ . '/lib.user_profile.php');
		$profile = new UserProfile ( getLogUserId () );
		$profile->init ( 'profile', 'framework', 'index.php?r=' . $link, 'ap' );
		echo $profile->userIdMailProfile ( 'normal', false, false );
	}
}