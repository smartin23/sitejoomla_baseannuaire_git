<?php
/**
 * @version: $Id: mainframe.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/base/mainframe.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 10-Jan-2009 17:09:58
 */
interface SPMainfrmaInterface
{
	public function __construct();

	public function path( $path );

	/**
	 * Gets basic data from the CMS (e.g Joomla); and stores in the #SPConfig instance
	 */
	public function getBasicCfg();

	/**
	 * @return SPMainFrame
	 */
	public static function & getInstance();

	/**
	 * @static
	 * @param	string	$code	The application-internal error code for this error
	 * @param	string	$msg	The error message, which may also be shown the user if need be.
	 * @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN);.
	 * @return	object	$error	The configured JError object
	 */
	public static function runAway( $msg, $code = 500, $info = null, $translate = false );

	/**
	 * @return string
	 */
	public static function getBack();

	/**
	 * @static
	 * @param	string	$msg	The message, which may also be shown the user if need be.
	 */
	public static function setRedirect( $add, $msg = null, $msgtype = 'message', $now = false );

	/**
	 * @static
	 * @param string $msg The message, which may also be shown the user if need be.
	 */
	public static function msg( $msg, $type = null );

	/**
	 * @static
	 */
	public static function redirect();

	/**
	 * @param SPDBObject $obj
	 * @return void
	 */
	public function addObjToPathway( $obj );

	/**
	 * @param array $head
	 */
	public function addHead( $head );

	/**
	 * Creating array of additional variables depend on the CMS
	 * @param array $var
	 * @return string
	 */
	public static function form();

	/**
	 * Creating URL from a array for the current CMS
	 * @param array $var
	 * @return string
	 */
	public static function url( $var = null, $js = false );

	public static function endOut();

	/**
	 * @param id
	 */
	public function & getUser( $id = 0 );

	/**
	 * Switchin error reporting and displaying of errors compl. off
	 * For e.g JavaScript, or XML output where the document structure is very sensible
	 *
	 */
	public function cleanBuffer();

	/**
	 * @param string $title
	 */
	public function setTitle( $title );
}
?>