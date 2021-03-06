<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 1, array
(
	'carcatalog' => array
	(
		'tables'      => array('tl_car_catalog', 'tl_cars'),
		'icon'        => 'system/modules/calendar/assets/icon.gif',
		'table'       => array('TableWizard', 'importTable'),
		'list'        => array('ListWizard', 'importList')
	)
));


/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array
(
	'carcatalog' => array
	(
		'carlist'    => 'MCupic\ModuleCarList',
		'carreader' => 'MCupic\ModuleCarReader',
		//'eventlist'   => 'ModuleEventlist',
		//'eventmenu'   => 'ModuleEventMenu'
	)
));


/**
 * Cron jobs
 */
//$GLOBALS['TL_CRON']['daily'][] = array('Calendar', 'generateFeeds');


/**
 * Register hook to add news items to the indexer
 */
//$GLOBALS['TL_HOOKS']['removeOldFeeds'][] = array('Calendar', 'purgeOldFeeds');
//$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('Calendar', 'getSearchablePages');
//$GLOBALS['TL_HOOKS']['generateXmlFiles'][] = array('Calendar', 'generateFeeds');


/**
 * Add permissions
 */
//$GLOBALS['TL_PERMISSIONS'][] = 'calendars';
//$GLOBALS['TL_PERMISSIONS'][] = 'calendarp';
//$GLOBALS['TL_PERMISSIONS'][] = 'calendarfeeds';
//$GLOBALS['TL_PERMISSIONS'][] = 'calendarfeedp';
