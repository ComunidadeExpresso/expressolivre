<?php
/*
 * Created on 20/08/2007 Por Bruno costa
 *
 *	Arquivo de configuração de catálogos externos
 *
 */

/*
 * This file is comprised of two arrays describing the external catalogs ($external_srcs) and their attribute mappings
 * ($external_mappings). The mapping between an external catalog and his attribute mappings are made with the array indices, i. e.
 * a catalog at external_srcs with indice = 1 use the mapping with indice = 1 at external_mapping.
 *
 * external_srcs' format:
 *
 * 	$external_srcs	=	array(
 * 					1	=>	array(
 * 						'name'		=>	'Catalog's name',
 * 						'host'		=>	'catalog's hostname',
 * 						'dn'		=>	'catalog's base dn',
 * 						'acc'		=>	'catalog's bind dn',
 * 						'pw'		=>	'bind dn's password',
 * 						'obj'		=>	'objectClass that will be used in searches',
 * 						'branch'	=>	strtolower('attribute used as branches in the catalog tree'),
 						'quicksearch	=>	'ExpressoMail's search',
						'max_results'	=>	'Ldap's search limit',
 * 					),
 * 					2 	=>	array(
 * 						...
 * 					),
 * 	);
 *
 * external_mappings' format:
 *
 * 	$external_mappings	=	array(
 *							1	=>	array(
 *								'contact.id_contact'				=>	array('dn'),
 *								'contact.photo'						=>	array('jpegPhoto'),
 *								'contact.prefixes.prefix'			=>	false, //used when you don't have an attribute or don't want to use one
 *								...
 *							),
 *							2	=>	array(
 *								...
 *							),
 *		);
 *
 */

?>
