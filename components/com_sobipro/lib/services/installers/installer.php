<?php
/**
 * @version: $Id: installer.php 551 2011-01-11 14:34:26Z Radek Suski $
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
 * File location: components/com_sobipro/lib/services/installers/installer.php $
 */
defined( 'SOBIPRO' ) || ( trigger_error( 'Restricted access ' . __FILE__, E_USER_ERROR ) && exit( 'Restricted access' ) );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 17-Jun-2010 12:35:23
 */

class SPInstaller extends SPObject
{
	/**
	 * @var string
	 */
	protected $type = null;
	/**
	 * @var string
	 */
	protected $xmlFile = null;
	/**
	 * @var string
	 */
	protected $root = null;
	/**
	 * @var DOMDocument
	 */
	protected $definition = null;
	/**
	 * @var DOMXPath
	 */
	protected $xdef = null;

	/**
	 * @param string $definition
	 * @param string $type
	 * @return SPInstaller
	 */
	public function __construct( $definition, $type = null )
	{
		$this->type = $type;
		$this->xmlFile = $definition;
		$this->definition = DOMDocument::load( $this->xmlFile );
		$this->xdef = new DOMXPath( $this->definition );
		$this->root = dirname( $this->xmlFile );
	}

	protected function xGetString( $key )
	{
		$node = $this->xGetChilds( $key )->item( 0 );
		return isset(  $node ) ? $node->nodeValue : null;
	}

	/**
	 * @param $key
	 * @return DOMNodeList
	 */
	protected function xGetChilds( $key )
	{
		return $this->xdef->query( "/{$this->type}/{$key}" );
	}

	public function validate()
	{
		$type = ( $this->type ==  'SobiProApp' ) ? 'application' : $this->type;
		$schemaDef = SPLoader::path( 'lib.services.installers.schemas.'.$type, 'front', false, 'xsd' );
		if( !( SPFs::exists( $schemaDef  ) ) || ( time() - filemtime( $schemaDef  ) > ( 60 * 60 * 24 * 7 ) ) ) {
			$connection = SPFactory::Instance( 'services.remote' );
			$def = "https://xml.sigsiu.net/SobiPro/{$type}.xsd";
			$connection->setOptions(
				array(
					'url' => $def,
					'connecttimeout' => 10,
					'header' => false,
					'returntransfer' => true,
					'ssl_verifypeer' => false,
					'ssl_verifyhost' => 2,
				)
			);
			$schema =& SPFactory::Instance( 'base.fs.file', SPLoader::path( 'lib.services.installers.schemas.'.$type, 'front', false, 'xsd' ) );
			$file = $connection->exec();
			if( !( strlen( $file ) ) ) {
				throw new SPException(  SPLang::e( 'CANNOT_ACCESS_SCHEMA_DEF', $def ) );
			}
			$schema->content( $file );
			$schema->save();
			$schemaDef = $schema->filename();
		}
		if( !( $this->definition->schemaValidate( $schemaDef ) ) ) {
			throw new SPException(  SPLang::e( 'CANNOT_VALIDATE_SCHEMA_DEF_AT', str_replace( SOBI_ROOT.DS, null, $this->xmlFile ), $def ) );
		}
	}
}
?>