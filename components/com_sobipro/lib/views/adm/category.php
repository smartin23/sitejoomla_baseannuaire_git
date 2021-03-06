<?php
/**
 * @version: $Id: category.php 2317 2012-03-27 10:19:39Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-03-27 12:19:39 +0200 (Tue, 27 Mar 2012) $
 * $Revision: 2317 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/views/adm/category.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadView( 'section', true );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 4:42:13 PM
 */
class SPCategoryAdmView extends SPSectionAdmView
{

	/**
	 * @param string $title
	 */
	public function setTitle( $title )
	{
		$titles = array();
		if( strstr( $title, '|' ) ) {
			$titleArr = explode( '|', $title );
			foreach ( $titleArr as $t ) {
				$t = explode( '=', $t );
				$titles[ trim( $t[ 0 ] ) ] = $t[ 1 ];
			}
			$title = $titles[ $this->get( 'task' ) ];
		}
		$name = $this->get( 'category.name' );
		Sobi::Trigger( 'setTitle', $this->name(), array( &$title ) );
		$title = Sobi::Txt( $title, array( 'category' => $name ) );
		SPFactory::header()->setTitle( $title );
		$this->set( $title, 'site_title');
	}

	/**
	 *
	 */
	public function display()
	{
		SPLoader::loadClass( 'html.tooltip' );
		switch ( $this->get( 'task' ) ) {
			case 'list':
				$this->listSection();
				break;
			case 'edit':
			case 'add':
				$this->edit();
				break;
			case 'chooser':
				$this->chooser();
				break;
		}
		parent::display();
	}

	/**
	 */
	private function edit()
	{
		$pid = $this->get( 'category.parent' );
		$path = null;
		if( !$pid ) {
			$pid = SPRequest::int( 'pid' );
		}
		$this->assign( $pid, 'parent' );
		$id = $this->get( 'category.id' );
		if( $id ) {
			$this->addHidden( $id, 'category.id' );
		}
		$head =& SPFactory::header();
//		$head->addJsFile( 'windoo' );
//		$head->addCssFile( 'windoo.windoo');
//		$head->addCssFile( 'windoo.aero');
//		$head->addCssFile( 'windoo.aqua');
//		$head->addCssFile( 'windoo.alphacube' );
		if( $this->get( 'category.icon' ) && SPFs::exists( Sobi::Cfg( 'images.category_icons' ).DS.$this->get( 'category.icon' ) ) ) {
			$i = Sobi::Cfg( 'images.category_icons_live' ).$this->get( 'category.icon' );
			$this->assign( $i, 'category_icon' );
		}
		/* if edititng - get the full path. Otherwise get the path of the parent element */
		$id = $id ? $id : $pid;
		if( $id ) {
			$path = $this->parentPath( $id );
		}
		$this->assign( $path, 'parent_path' );
		if( SPRequest::sid() ) {
			$this->assign( Sobi::Url( array( 'task' => 'category.chooser', 'sid' => SPRequest::sid(), 'out' => 'html' ), true ), 'cat_chooser_url' );
		}
		elseif ( SPRequest::int( 'pid' ) ) {
			$this->assign( Sobi::Url( array( 'task' => 'category.chooser', 'pid' => SPRequest::int( 'pid' ), 'out' => 'html' ), true ), 'cat_chooser_url' );
		}
		$this->assign( Sobi::Url( array( 'task' => 'category.icon', 'out' => 'html' ), true ), 'icon_chooser_url' );
	}

	private function chooser()
	{
		$pid = $this->get( 'category.parent' );
		$path = null;
		if( !$pid ) {
			$pid = SPRequest::sid();
		}
		$this->assign( $pid, 'parent' );
		$id = $this->get( 'category.id' );
		$id = $id ? $id : $pid;
		if( $id ) {
			$path = $this->parentPath( $id );
		}
		$this->assign( $path, 'parent_path' );
		$this->assign( Sobi::Url( array( 'task' => 'category.parents', 'out' => 'json', 'format' => 'raw' ), true ), 'parent_ajax_url' );
	}
}
