<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MCupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'MCupic\CarCatalog'      => 'system/modules/car_catalog/classes/CarCatalog.php',

	// Models
	'Contao\CarCatalogModel' => 'system/modules/car_catalog/models/CarCatalogModel.php',
	'Contao\CarsModel'       => 'system/modules/car_catalog/models/CarsModel.php',

	// Modules
	'MCupic\ModuleCarReader' => 'system/modules/car_catalog/modules/ModuleCarReader.php',
	'MCupic\ModuleCarList'   => 'system/modules/car_catalog/modules/ModuleCarList.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'car_gallery_default'          => 'system/modules/car_catalog/templates/car_gallery',
	'car_gallery_default_carousel' => 'system/modules/car_catalog/templates/car_gallery',
	'car_list_default'             => 'system/modules/car_catalog/templates/car_list',
	'mod_car'                      => 'system/modules/car_catalog/templates/modules',
	'mod_car_list'                 => 'system/modules/car_catalog/templates/modules',
	'car_detail'                   => 'system/modules/car_catalog/templates/car_detail',
));
