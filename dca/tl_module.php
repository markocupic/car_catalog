<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Add palettes to tl_module
 */
//$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar']    = '{title_legend},name,headline,type;{config_legend},cal_calendar,cal_noSpan,cal_startDay;{redirect_legend},jumpTo;{template_legend:hide},cal_ctemplate,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['carlist'] = '{title_legend},name,headline,type;{config_legend},car_catalog,sortBy,sorting_direction,filterExpression,car_readerModule,car_limit,perPage;{template_legend:hide},car_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['carreader'] = '{title_legend},name,headline,type;{config_legend},car_catalog;{template_legend:hide},car_template,customTpl;{image_legend},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
//$GLOBALS['TL_DCA']['tl_module']['palettes']['eventmenu']   = '{title_legend},name,headline,type;{config_legend},cal_calendar,cal_noSpan,cal_showQuantity,cal_format,cal_startDay,cal_order;{redirect_legend},jumpTo;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['car_catalog'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['car_catalog'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options_callback' => array('tl_module_car_catalog', 'getCarCatalogs'),
    'eval' => array('mandatory' => true, 'multiple' => true),
    'sql' => "blob NULL"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['sorting_direction'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sorting_direction'],
    'default' => 'ascending',
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('ascending', 'descending'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['filterExpression'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['filterExpression'],
    'inputType' => 'text',
    //'save_callback' => array(array('tl_module_car_catalog', 'cleanFilterExpression')),
    'eval' => array(
        'mandatory' => false, 'preserveTags' => false, 'allowHtml' => true, 'decodeEntities' => false
    ),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['sortBy'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['sortBy'],
    'default' => 'ascending',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_car_catalog', 'listCarFields'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['car_readerModule'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['car_readerModule'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_car_catalog', 'getReaderModules'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => array('includeBlankOption' => true, 'tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['car_limit'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['car_limit'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('rgxp' => 'natural', 'tl_class' => 'w50'),
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['car_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['car_template'],
    'default' => 'event_full',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_car_catalog', 'getCarTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);




/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_module_car_catalog extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Get all car catalogs and return them as array
     *
     * @return array
     */
    public function getCarCatalogs()
    {
        $arrCarCatalogs = array();
        $objCarCatalog = $this->Database->execute("SELECT id, title FROM tl_car_catalog ORDER BY title");

        while ($objCarCatalog->next()) {
            $arrCarCatalogs[$objCarCatalog->id] = $objCarCatalog->title;
        }

        return $arrCarCatalogs;
    }


    /**
     * Get all event reader modules and return them as array
     *
     * @return array
     */
    public function getReaderModules()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='carreader' ORDER BY t.name, m.name");

        while ($objModules->next()) {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
        }

        return $arrModules;
    }


    /**
     * Return all event templates as array
     *
     * @return array
     */
    public function getCarTemplates()
    {
        return $this->getTemplateGroup('car_');
    }

    /**
     * @return array
     */
    public function listCarFields()
    {
        $arrFields = array();
        $arrNotAllowed = array('PRIMARY', 'assImage', 'singleSRC', 'multiSRC');
        $arrFieldList = $this->Database->listFields('tl_cars');
        foreach ($arrFieldList as $arrField) {
            if (!in_array($arrField['name'], $arrNotAllowed)) {
                $arrFields[] = $arrField['name'];
            }
        }
        return $arrFields;

    }


}
