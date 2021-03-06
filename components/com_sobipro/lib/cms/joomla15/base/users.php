<?php
/**
 * @version: $Id: users.php 551 2011-01-11 14:34:26Z Radek Suski $
 * @package: SobiPro Bridge
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-01-11 15:34:26 +0100 (Tue, 11 Jan 2011) $
 * $Revision: 551 $
 * $Author: Radek Suski $
 * File location: components/com_sobipro/lib/cms/joomla15/base/users.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 03-Feb-2009 17:14:11
 */
class SPUsers
{
	public static function getGroupsField()
	{
		$acl =& JFactory::getACL();
		$g = $acl->get_group_children_tree( null, 'USERS', false );
		$gids = array();
		foreach ( $g as $k => $v ) {
			$gids[] = get_object_vars( $v );
		}
		array_unshift( $gids, array( 'value' => '0', 'text' => Sobi::Txt( 'ACL.REG_VISITOR' ) , 'disable' => null ) );
		return $gids;
	}
}
?>