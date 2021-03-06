<?php
/**
 * @version: $Id: filter.php 1458 2011-06-04 11:18:29Z Radek Suski $
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
 * $Date: 2011-06-04 13:18:29 +0200 (Sat, 04 Jun 2011) $
 * $Revision: 1458 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/ctrl/adm/filter.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Aug-2010 15:38:15
 */
class SPFilter extends SPConfigAdmCtrl
{
	/**
	 * @var string
	 */
	protected $_defTask = 'list';

	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'list':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'save':
				$this->save();
				break;
			default:
				/* case plugin didn't registered this task, it was an error */
				if( !parent::execute() ) {
					Sobi::Error( 'filter_ctrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
				}
				break;
		}
	}

	protected function delete()
	{
		$filters = $this->getFilters();
		$id = SPRequest::cmd( 'filter_id' );
		$name = SPRequest::string( 'filter_name' );
		$msg = SPRequest::string( 'filter_message' );
		$regex = base64_encode( SPRequest::string( 'filter_regex' ) );
		if( isset( $filters[ $id ] ) && ( strlen( $filters[ $id ][ 'options' ] ) ) ) {
			unset( $filters[ $id ] );
		}
		SPFactory::registry()->saveDBSection( $filters, 'fields_filter' );
		SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'FLR.MSG_FILTER_DELETED'  ), 'msgtype' => SPC::ERROR_MSG ) );
		echo '<script>parent.location.reload();</script>';
	}

	protected function save()
	{
		if( !( SPFactory::mainframe()->checkToken() ) ) {
			Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
		}
		$filters = $this->getFilters();
		$id = SPRequest::cmd( 'filter_id' );
		$name = SPRequest::string( 'filter_name' );
		$msg = str_replace( array( "\n", "\t", "\r" ), null, SPLang::clean( SPRequest::string( 'filter_message' ) ) );
		$regex = SPLang::clean( SPRequest::raw( 'filter_regex' ) );
		$regex = str_replace( '[:apostrophes:]', '\"'."\'", $regex );
		$regex = base64_encode( str_replace( array( "\n", "\t", "\r" ), null, $regex ) );
		$custom = 'custom';
		if( isset( $filters[ $id ] ) && !( strlen( $filters[ $id ][ 'options' ] ) ) ) {
			$regex = $filters[ $id ][ 'params' ];
			$custom = null;
		}
		$filters[ $id ] = array(
			'params' => $regex,
			'key' => $id,
			'value' => $name,
			'description' => $msg,
			'options' => $custom
		);
		SPFactory::registry()->saveDBSection( $filters, 'fields_filter' );
		SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'FLR.MSG_FILTER_SAVED'  ), 'msgtype' => SPC::ERROR_MSG ) );
		echo '<script>parent.location.reload();</script>';
	}

	private function getFilters()
	{
		$registry =& SPFactory::registry();
		$registry->loadDBSection( 'fields_filter' );
		$filters = $registry->get( 'fields_filter' );
		$f = array();
		foreach ( $filters as $fid => $filter ) {
			$f[ $fid ] = array(
				'params' => $filter[ 'params' ] ,
				'key' => $fid,
				'value' => $filter[ 'value' ],
				'description' => $filter[ 'description' ],
				'options' => $filter[ 'options' ]
			);
		}
		ksort( $f );
		return $f;
	}

	private function edit()
	{
		$id = SPRequest::cmd( 'fid' );
		$Filters = array();
		$filters = $this->getFilters();
		if( count( $filters ) && isset( $filters[ $id ] ) ) {
			$Filter = array(
				'id' => $id,
				'regex' => str_replace( '\"'."\'", '[:apostrophes:]', base64_decode( $filters[ $id ][ 'params' ] ) ),
				'name' => $filters[ $id ][ 'value' ],
				'message' => $filters[ $id ][ 'description' ],
				'editable' =>  strlen( $filters[ $id ][ 'options' ] )
			);
		}
		else {
			$Filter = array( 'id' => '', 'regex' => '', 'name' => '', 'message' => '', 'editable' =>  true );
		}
		$raw = Sobi::Url( array( 'out' => 'raw' ), true );
		$raw = explode( '&', $raw );
		$view =& SPFactory::View( 'view', true );
		$view->assign( $this->_task, 'task' );
		$view->loadConfig( 'field.filter' );
		$view->assign( $Filter, 'filter' );
		if( count( $raw ) ) {
			foreach ( $raw as $line ) {
				if( !( strstr( $line, '?' ) ) ) {
					$line = explode( '=', $line );
					$view->addHidden( $line[ 1 ], $line[ 0 ] );
				}
			}
		}
		$view->setTemplate( 'field.filter' );
		$view->display();
	}

	private function screen()
	{
		$filters = $this->getFilters();
		$Filters = array();
		if( count( $filters ) ) {
			foreach ( $filters as $name => $filter ) {
				$Filters[] = array(
					'id' => $name,
					'regex' => str_replace( '\"'."\'", '[:apostrophes:]', base64_decode( $filter[ 'params' ] ) ),
					'name' => $filter[ 'value' ],
					'message' => $filter[ 'description' ],
					'editable' =>  strlen( $filter[ 'options' ] )
				);
			}
		}
//		$head =& SPFactory::header();
//		$head->addJsFile( 'windoo' );
//		$head->addCssFile( 'windoo.windoo');
//		$head->addCssFile( 'windoo.aero');
//		$head->addCssFile( 'windoo.alphacube' );
		$view =& SPFactory::View( 'view', true );
		$view->assign( $this->_task, 'task' );
		$view->loadConfig( 'field.filters' );
		$view->assign( $this->createMenu(), 'menu' );
		$view->assign( Sobi::Url( array( 'task' => 'filter.edit', 'out' => 'html' ), true ), 'edit_url' );
		$view->assign( $Filters, 'filters' );
		$view->setTemplate( 'field.filters' );
		$view->display();
	}
}
?>