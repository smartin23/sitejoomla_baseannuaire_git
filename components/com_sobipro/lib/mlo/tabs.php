<?php
/**
 * @version: $Id: tabs.php 677 2011-01-31 18:32:27Z Radek Suski $
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
 * $Date: 2011-01-31 19:32:27 +0100 (Mon, 31 Jan 2011) $
 * $Revision: 677 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/mlo/tabs.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Jan-2009 9:35:15 AM
 */
final class SPHtml_Tabs
{
	/**
	 * @var bool
	 */
	var $useCookies = true;
	/**
	 * @var string
	 */
	var $prefix = null;
	/**
	 * @var string
	 */
	var $class = null;

	/**
	 * Set/add CSS class
	 * @param string $class
	 */
	public function setClass ( $class )
	{
		$this->class = ' '.$class;
	}

	/**
	 * Set the ID prefix
	 * @param string $prefix
	 */
	public function setPrefix ( $prefix )
	{
		$this->prefix = $prefix;
	}

	/**
	 * @param bool $useCookies
	 * @param string $cssFile - separate CSS file
	 * @param string $prefix
	 * @return sobiTabs
	 */
	public function __construct( $useCookies = true, $cssFile = 'tabs', $prefix = null )
	{
		$this->useCookies = $useCookies ? 1 : 0;
		$this->prefix = $prefix;
		if( $cssFile ) {
			SPFactory::header()->addCssFile( $cssFile );
		}
		SPFactory::header()->addJsFile( 'tabs' );
	}

	/**
	 * creates a tab pane and creates JS obj
	 * @param string The Tab Pane Name
	 */
	public function startPane( $id, $return = false )
	{
		$r = null;
		$r .= "<div class=\"tab-page{$this->prefix}{$this->class}\" id=\"{$id}\">";
		$r .= "<script type=\"text/javascript\">\n";
		$r .= "	var SobiTabPane{$this->prefix} = new WebFXTabPane( document.getElementById( \"{$id}\" ), {$this->useCookies} )\n";
		$r .= "</script>\n";
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ), array( &$r ) );
		$this->out( $r, $return );
	}

	/**
	 * Ends Tab Pane
	 */
	public function endPane( $return = false )
	{
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ) );
		$this->out( '</div>', $return );
	}

	/**
	 * Creates a tab with title text and starts that tabs page
	 * @param tabText - This is what is displayed on the tab
	 * @param paneid - This is the parent pane to build this tab on
	 */
	public function startTab( $tabText, $paneid, $return = false )
	{
		$r = null;
		$r .= "<div class=\"tab-page{$this->prefix}{$this->class}\" id=\"{$paneid}\">";
		$r .= "<h2 class=\"tab{$this->prefix}{$this->class}\">".$tabText."</h2>";
		$r .= "<script type=\"text/javascript\">\n";
		$r .= "  SobiTabPane{$this->prefix}.addTabPage( document.getElementById( \"{$paneid}\" ) );";
		$r .= "</script>";
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ), array( &$r ) );
		$this->out( $r, $return );
	}

	/*
	 * Ends a tab page
	 */
	public function endTab( $return = false )
	{
		Sobi::Trigger( 'Tabs', ucfirst( __FUNCTION__ ) );
		$this->endPane( $return );
	}

	/**
	 * @param bool $return
	 * @return string
	 */
	private function out( $r, $return )
	{
		if( $return ) {
			return $r;
		}
		else {
			echo $r;
		}
	}
}
?>