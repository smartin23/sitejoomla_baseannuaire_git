<?php
/**
 * @version: $Id: sobipro.php 2124 2012-01-02 10:54:28Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-01-02 11:54:28 +0100 (Mon, 02 Jan 2012) $
 * $Revision: 2124 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/ctrl/adm/sobipro.php $
 */
/**
 * @author Radek Suski
 * @version 1.2
 * @created 10-Jan-2009 4:39:59 PM
 */
final class SobiProAdmCtrl
{
    /**
     * @var SPMainFrame
     */
    private $_mainframe = null;
    /**
     * @var SPConfig
     */
    private $_config = null;
    /**
     * @var int
     */
    private $_mem = 0;
    /**
     * @var int
     */
    private $_time = 0;
    /**
     * @var int
     */
    private $_section = 0;
    /**
     * @var string
     */
    private $_task = 0;
    /**
     * @var int
     */
    private $_sid = 0;
    /**
     * @var SPUser
     */
    private $_user = null;
    /**
     * @var SPController - could be also array of
     */
    private $_ctrl = null;
    /**
     * @var mixed
     */
    private $_model = null;
    /**
     * @var string
     */
    private $_type = 'component';

    /**
     * @param string $task
     */
    function __construct( $task )
    {
        SPLoader::loadClass( 'base.exception' );
        set_error_handler( 'SPExceptionHandler' );
        $this->_err = ini_set( 'display_errors', 'on' );
        $this->_mem = memory_get_usage();
        $this->_time = microtime();
        $this->_task = $task;
        /* load all needed classes */
        SPLoader::loadClass( 'base.factory' );
        SPLoader::loadClass( 'base.object' );
        SPLoader::loadClass( 'base.const' );
        SPLoader::loadClass( 'base.filter' );
        SPLoader::loadClass( 'base.request' );
        SPLoader::loadClass( 'sobi' );
        SPLoader::loadClass( 'base.config' );
        /* authorise access */
        $this->checkAccess();
        /* initialise mainframe interface to CMS */
        $this->_mainframe = & SPFactory::mainframe();

        /* get sid if any */
        $this->_sid = SPRequest::sid();
        /* determine section */
        $this->getSection();
        /* initialise config */
        $this->createConfig();
        ini_set( 'display_errors', Sobi::Cfg( 'debug.display_errors', false ) );
        $this->_deb = error_reporting( Sobi::Cfg( 'debug.level', 0 ) );

        /* trigger plugin */
        Sobi::Trigger( 'AdminStart' );

        /* initialise translator and load language files */
        SPLoader::loadClass( 'cms.base.lang' );
        SPLang::setLang( Sobi::Lang() );
        try {
            SPLang::registerDomain( 'admin' );
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot register language domain: %s.', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }

        /* load admin html files */
        SPFactory::header()->addCssFile( 'sobipro', true );
        SPFactory::header()->addJsFile( 'sobipro' );
        SPFactory::header()->addJsFile( 'adm.sobipro' );

        /** @noinspection PhpParamsInspection */
        $sectionName = SPLang::translateObject( $this->_section, 'name', 'section' );
        SPFactory::registry()->set( 'current_section_name', $sectionName[ $this->_section ][ 'value' ] );

        /* check if it wasn't plugin custom task */
        if ( !Sobi::Trigger( 'custom', 'task', array( $this, SPRequest::task() ) ) ) {
            /* if not, start to route */
            try {
                $this->route();
            }
            catch ( SPException $x ) {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot route: %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        return true;
    }

    /**
     * Just few debug messages - if enabled
     * @return void
     */
    function __destruct()
    {
        $this->_mem = number_format( ( ( memory_get_usage() - $this->_mem ) / 1024 / 1024 ), 2 );
        $this->_time = number_format( ( ( microtime() - $this->_time ) ), 2 );
        //		$db = & SPFactory::db();
        //		SPConfig::debOut( "Number of Queries: " . $db->getCount() . " / Memory: {$this->_mem} MB / Time: {$this->_time} Seconds / Loaded files " . SPLoader::getCount(), true );
    }

    /**
     * Check access permissions
     * @return bool
     */
    private function checkAccess()
    {
        $this->_user = & SPFactory::user();
        /* authorise access permissions */
        if ( !$this->_user->can( 'panel' ) ) {
            Sobi::Error( 'CoreCtrl', /*SPLang::e*/
                ( 'UNAUTHORIZED_ACCESS' ), SPC::ERROR, 403, __LINE__, __FILE__ );
            exit();
        }
        /* authorise task permissions */
        if ( SPRequest::task() ) {
            if ( !$this->_user->can( SPRequest::task() ) ) {
                Sobi::Error( 'CoreCtrl', /*SPLang::e*/
                    ( 'UNAUTHORIZED_ACCESS' ), SPC::ERROR, 403, __LINE__, __FILE__ );
                //				Sobi::Error( 'CoreCtrl', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
                exit();
            }
        }
    }

    /**
     * initialise config object
     * @return void
     */
    private function createConfig()
    {
        $this->_config = & SPFactory::config();
        /* load basic configuration settings */
        $this->_config->addIniFile( 'etc.config', true );
        $this->_config->addIniFile( 'etc.adm.config', true );
        $this->_config->addTable( 'spdb_config', $this->_section );
        /* initialise interface config setting */
        $this->_mainframe->getBasicCfg();
        /* initialise config */
        $this->_config->init();
    }

    /**
     * get the right section
     * @return void
     */
    private function getSection()
    {
        $pid = SPRequest::int( 'pid' );
        $pid = $pid ? $pid : $this->_sid;
        if ( $pid ) {
            $this->_model = & SPFactory::object( $pid );
            /** @noinspection PhpParamsInspection
             * @var $this->_model stdClass
             */
            if ( $this->_model->oType == 'section' ) {
                $this->_section = $this->_model->id;
            }
            else {
                $db =& SPFactory::db();
                $path = array();
                $id = $pid;
                while ( $id > 0 ) {
                    try {
                        $db->select( 'pid', 'spdb_relations', array( 'id' => $id ) );
                        $id = $db->loadResult();
                        if ( $id ) {
                            $path[ ] = ( int )$id;
                        }
                    }
                    catch ( SPException $x ) {
                        Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                    }
                }
                $path = array_reverse( $path );
                $this->_section = $path[ 0 ];
            }
        }
        else {
            $this->_section = '0';
        }
        SPFactory::registry()->set( 'current_section', $this->_section );
    }

    /**
     * Try to find out what we have to do
     *     - If we have a task - parse task
     *  - If we don't have a task, but sid, we are going via default object task
     *  - Otherwise it could be only the frontpage
     * @return void
     */
    private function route()
    {
        /* if we have a task */
        if ( $this->_task && $this->_task != 'panel' ) {
            if ( !( $this->routeTask() ) ) {
                throw new SPException( SPLang::e( 'Cannot interpret task "%s"', $this->_task ) );
            }
        }
        /* if there is no task - execute default task for object */
        elseif ( $this->_sid ) {
            if ( !( $this->routeObj() ) ) {
                throw new SPException( SPLang::e( 'Cannot route object with id "%d"', $this->_sid ) );
            }
        }
        /* otherwise show the frontpage */
        else {
            $this->frontpage();
        }
    }

    /**
     * Route by task
     * @return bool
     */
    private function routeTask()
    {
        $r = true;
        if ( strstr( $this->_task, '.' ) ) {
            /* task consist of the real task and the object type */
            $task = explode( '.', $this->_task );
            $obj = trim( $task[ 0 ] );
            $task = trim( $task[ 1 ] );

            /* load the controller class definition and get the class name */
            $ctrl = SPLoader::loadController( $obj, true );

            /* route task for multiple objects - e.g removing or publishing elements from a list */
            if (
                /* if there was no model */
                /*!( $this->_model && ( $this->_model instanceof stdClass ) ) &&*/
                (
                    /* and there was some of multiple sids */
                        count( SPRequest::arr( 'sid' ) ) ||
                                count( SPRequest::arr( 'c_sid' ) ) ||
                                count( SPRequest::arr( 'e_sid' ) )
                )
            ) {
                $sid = key_exists( 'sid', $_REQUEST ) ? 'sid' : ( key_exists( 'c_sid', $_REQUEST ) ? 'c_sid' : 'e_sid' );
                if ( count( SPRequest::arr( $sid ) ) ) {
                    /* @var SPdb $db */
                    $db =& SPFactory::db();
                    $objects = null;
                    try {
                        $db->select( '*', 'spdb_object', array( 'id' => SPRequest::arr( $sid ) ) );
                        $objects = $db->loadObjectList();
                    }
                    catch ( SPException $x ) {
                        Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                        $r = false;
                    }
                    /** @noinspection PhpUndefinedVariableInspection */
                    if ( count( $objects ) ) {
                        $this->_ctrl = array();
                        foreach ( $objects as $object ) {
                            $o = $this->extendObj( $object, $obj, $ctrl, $task );
                            if ( $o ) {
                                $this->_ctrl[ ] = $o;
                            }
                        }
                        if ( !( count( $this->_ctrl ) ) ) {
                            Sobi::Error( 'CoreCtrl', SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::WARNING, 0, __LINE__, __FILE__ );
                            Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::ERROR_MSG );
                        }
                    }
                    else {
                        Sobi::Error( 'CoreCtrl', SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::WARNING, 0, __LINE__, __FILE__ );
                        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::ERROR_MSG );
                        $r = false;
                        //break;
                    }
                }
                else {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::WARNING, 0, __LINE__, __FILE__ );
                    Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), SPLang::e( 'IDENTIFIER_EXPECTED' ), SPC::ERROR_MSG );
                    $r = false;
                    //break;
                }
            }
            else {
                /* set controller and model */
                try {
                    $ctrl = new $ctrl();
                    $this->setController( $ctrl );
                    if ( $ctrl instanceof SPController ) {
                        $model = SPLoader::loadModel( $obj, false, false );
                        if ( $model ) {
                            $this->_ctrl->setModel( $model );
                        }
                    }
                }
                catch ( SPException $x ) {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
                }
                if ( $this->_sid ) {
                    $this->_model = & SPFactory::object( $this->_sid );
                }
                /* if the basic object we got from the #getSection method is the same one ... */
                if ( ( $this->_model instanceof stdClass ) && ( $this->_model->oType == $obj ) ) {
                    /*... extend the empty model of these data we've already got */
                    /** @noinspection PhpParamsInspection */
                    $this->_ctrl->extend( $this->_model );
                }
                /* ... and so on... */
                $this->_ctrl->setTask( $task );
            }
        }
        else {
            /** Special controllers not inherited from object and without model */
            $task = $this->_task;
            $ctrl = SPLoader::loadController( $task, true );
            try {
                $this->setController( new $ctrl() );
            }
            catch ( SPException $x ) {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot set controller. %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
            }
        }
        return $r;
    }

    /**
     * @return bool
     */
    private function routeObj()
    {
        try {
            $ctrl = SPLoader::loadController( $this->_model->oType, true );
            $ctrl = new $ctrl();
            if ( $ctrl instanceof SPController ) {
                $this->setController( $ctrl );
                $this->_ctrl->setModel( SPLoader::loadModel( $this->_model->oType ) );
                /** @noinspection PhpParamsInspection */
                $this->_ctrl->extend( $this->_model );
                $this->_ctrl->setTask( $this->_task );
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( 'Cannot route object: %s.', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
        }
        return true;
    }

    /**
     * @param stdClass $obj
     * @param string $ctrlClass
     * @param string $objType
     * @param string $task
     * @return SPControl
     */
    private function & extendObj( $obj, $objType, $ctrlClass, $task = null )
    {
        if ( $objType == $obj->oType ) {
            if ( $ctrlClass ) {
                /* create controler */
                $ctrl = new $ctrlClass();
                /* set model */
                /** @noinspection PhpUndefinedMethodInspection */
                $ctrl->setModel( SPLoader::loadModel( $objType ) );
                /* extend model of basic data */
                $ctrl->extend( $obj );
                /* set task */
                if ( strlen( $task ) ) {
                    $ctrl->setTask( $task );
                }
            }
            else {
                Sobi::Error( 'CoreCtrl', SPLang::e( 'SUCH_TASK_NOT_FOUND', SPRequest::task() ), SPC::NOTICE, 404, __LINE__, __FILE__ );
            }
        }
        return $ctrl;
    }

    /**
     * @return void
     */
    private function frontpage()
    {
        SPLoader::loadController( 'front', true );
        $this->setController( new SPAdminPanel() );
        Sobi::ReturnPoint();
        $this->_ctrl->setTask( SPRequest::task() );
    }

    /**
     * Executes the controller task
     * @return void
     */
    public function execute()
    {
        try {
            if ( is_array( $this->_ctrl ) ) {
                foreach ( $this->_ctrl as &$c ) {
                    $c->execute();
                }
            }
            else {
                if ( $this->_ctrl instanceof SPControl ) {
                    $this->_ctrl->execute();
                }
                else {
                    Sobi::Error( 'CoreCtrl', SPLang::e( 'No controller to execute' ), SPC::ERROR, 500, __LINE__, __FILE__ );
                }
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( 'CoreCtrl', SPLang::e( 'No controller to execute. %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
            Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), $x->getMessage(), SPC::ERROR_MSG );
        }
        /* send header data etc ...*/
        SPFactory::header()->send();
        SPMainFrame::endOut();
        /* redirect if any redyriect has been set */
        SPMainFrame::redirect();
        error_reporting( $this->_deb );
        restore_error_handler();
    }

    /**
     * @return void
     */
    public function setController( &$ctrl )
    {
        $this->_ctrl = & $ctrl;
    }
}
