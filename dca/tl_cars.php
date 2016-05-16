<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Load tl_content language file
 */
System::loadLanguageFile('tl_content');


/**
 * Table tl_calendar_events
 */
$GLOBALS['TL_DCA']['tl_cars'] = array(

    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_car_catalog',
        //'ctable'           => array('tl_content'),
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'onload_callback'  => array(
            array('tl_cars', 'checkPermission'),
        ),
        'sql'              => array(
            'keys' => array(
                'id'            => 'primary',
                'alias'         => 'index',
                'pid,published' => 'index',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'                  => 4,
            'flag'                  => 4,
            'fields'                => array('name DESC'),
            'headerFields'          => array('title', 'tstamp'),
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => array('tl_cars', 'listCars'),
            'child_record_class'    => 'no_padding',
        ),
        'global_operations' => array(
            'all' => array(
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ),
        ),
        'operations'        => array(
            /*
            'edit'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_cars']['edit'],
                'href'  => 'table=tl_content',
                'icon'  => 'edit.gif',
            ),
            */
            'editheader' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_cars']['editmeta'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ),
            'copy'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_cars']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.gif',
            ),
            'cut'        => array(
                'label' => &$GLOBALS['TL_LANG']['tl_cars']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif',
            ),
            'delete'     => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_cars']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'toggle'     => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_cars']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_cars', 'toggleIcon'),
            ),
            'show'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_cars']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        '__selector__' => array('addImage', 'addImage', 'addGallery', 'source'),
        'default'      => '{published_legend},published,archiv;{title_legend},name,alias,kilometer,initialRegistrationDate,fuel,engineCapacity,power,transmission,gears,price,colorOutside,colorInside,configuration,state,history;{image_legend},addImage;{gallery_legend},addGallery;{source_legend},source;',
    ),
    // Subpalettes
    'subpalettes' => array(
        'addImage'        => 'singleSRC,imagesize',
        'addGallery'      => 'multiSRC,galleryTpl,sortBy,metaIgnore,size,imagemargin,perRow,fullsize,perPage,numberOfItems',
        'source_internal' => 'jumpTo',
        'source_article'  => 'articleId',
        'source_external' => 'url,target',
    ),
    // Fields
    'fields'      => array(
        'id'                      => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid'                     => array(
            'foreignKey' => 'tl_car_catalog.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => array('type' => 'belongsTo', 'load' => 'lazy'),
        ),
        'tstamp'                  => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'archiv'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['archiv'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'doNotCopy' => true),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'name'                    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['name'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'maxlength' => 255),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'alias'                   => array(
            'label'         => &$GLOBALS['TL_LANG']['tl_cars']['alias'],
            'exclude'       => true,
            'search'        => true,
            'sorting'       => true,
            'inputType'     => 'text',
            'eval'          => array('rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'clr'),
            'save_callback' => array(
                array('tl_cars', 'generateAlias'),
            ),
            'sql'           => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
        ),
        'engineCapacity'          => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['engineCapacity'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => range('1.0', '8.0', '0.1'),
            'eval'      => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'power'                   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['power'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 255, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'gears'                   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['gears'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_cars'],
            'options'   => array_map(function ($e)
            {
                return $e . '-gear';
            }, range(1, 10, 1)),
            'eval'      => array('maxlength' => 255, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'transmission'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['transmission'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_cars'],
            'options'   => array('manual_gearbox', 'automatic_gearbox'),
            'eval'      => array('maxlength' => 255, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'fuel'                    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['fuel'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_cars'],
            'options'   => array('benzin', 'diesel', 'gas', 'bioethanol', 'elektro'),
            'eval'      => array('maxlength' => 255, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'colorInside'             => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['colorInside'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'colorOutside'            => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['colorOutside'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => array('doNotCopy' => false, 'chosen' => true, 'mandatory' => false, 'tl_class' => 'clr'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'configuration'           => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['configuration'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
            'sql'       => "text NULL",
        ),
        'state'                   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['state'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
            'sql'       => "text NULL",
        ),
        'history'                 => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['history'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
            'sql'       => "text NULL",
        ),
        'price'                   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['price'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 7, 'tl_class' => 'clr', 'rgxp' => 'digit'),
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ),
        'initialRegistrationDate' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['initialRegistrationDate'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => range('1900', Date::parse('Y'), 1),
            'eval'      => array('maxlength' => 255, 'tl_class' => 'clr', 'rgxp' => 'digit'),
            'sql'       => "int(4) unsigned NOT NULL default '0'",
        ),
        'kilometer'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['kilometer'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 7, 'tl_class' => 'clr', 'rgxp' => 'digit'),
            'sql'       => "int(7) unsigned NOT NULL default '0'",
        ),
        'addImage'                => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['addImage'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'singleSRC'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'       => "binary(16) NULL",
        ),
        'imagesize'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['imagesize'],
            'exclude'   => true,
            'inputType' => 'imageSize',
            'options'   => System::getImageSizes(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(64) NOT NULL default ''",
        ),
        'addGallery'              => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['addGallery'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'multiSRC'                => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['multiSRC'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => array('multiple' => true, 'extensions' => Config::get('validImageTypes'), 'fieldType' => 'checkbox', 'orderField' => 'orderSRC', 'files' => true, 'mandatory' => true, 'tl_class' => 'clr'),
            'sql'       => "blob NULL",
        ),
        'galleryTpl'              => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['galleryTpl'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('tl_cars', 'getGalleryTemplates'),
            'eval'             => array('tl_class' => 'w50'),
            'sql'              => "varchar(64) NOT NULL default ''",
        ),
        'orderSRC'                => array(
            'label' => &$GLOBALS['TL_LANG']['tl_content']['orderSRC'],
            'sql'   => "blob NULL",
        ),
        'fullsize'                => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50 m12'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'perRow'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['perRow'],
            'default'   => 4,
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "smallint(5) unsigned NOT NULL default '0'",
        ),
        'perPage'                 => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['perPage'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'natural', 'tl_class' => 'w50'),
            'sql'       => "smallint(5) unsigned NOT NULL default '0'",
        ),
        'numberOfItems'           => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['numberOfItems'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'natural', 'tl_class' => 'w50'),
            'sql'       => "smallint(5) unsigned NOT NULL default '0'",
        ),
        'sortBy'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['sortBy'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('custom', 'name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'),
            'reference' => &$GLOBALS['TL_LANG']['tl_content'],
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "varchar(32) NOT NULL default ''",
        ),
        'metaIgnore'              => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metaIgnore'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50 m12'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'customTpl'               => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['customTpl'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('tl_content', 'getElementTemplates'),
            'eval'             => array('includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'),
            'sql'              => "varchar(64) NOT NULL default ''",
        ),
        'size'                    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['size'],
            'exclude'   => true,
            'inputType' => 'imageSize',
            'options'   => System::getImageSizes(),
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
            'sql'       => "varchar(64) NOT NULL default ''",
        ),
        'source'                  => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_cars']['source'],
            'default'          => 'default',
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'radio',
            'options_callback' => array('tl_cars', 'getSourceOptions'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_calendar_events'],
            'eval'             => array('submitOnChange' => true, 'helpwizard' => true),
            'sql'              => "varchar(32) NOT NULL default ''",
        ),
        'jumpTo'                  => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_cars']['jumpTo'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => array('mandatory' => true, 'fieldType' => 'radio'),
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => array('type' => 'belongsTo', 'load' => 'lazy'),
        ),
        'articleId'               => array(
            'label'            => &$GLOBALS['TL_LANG']['tl_cars']['articleId'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('tl_cars', 'getArticleAlias'),
            'eval'             => array('chosen' => true, 'mandatory' => true),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ),
        'url'                     => array(
            'label'     => &$GLOBALS['TL_LANG']['MSC']['url'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'target'                  => array(
            'label'     => &$GLOBALS['TL_LANG']['MSC']['target'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50 m12'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'published'               => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_cars']['published'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'flag'      => 2,
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'doNotCopy' => true, 'tl_class' => 'clr'),
            'sql'       => "char(1) NOT NULL default ''",
        ),
    ),
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_cars extends Backend
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
     * Check permissions to edit table tl_cars
     */
    public function checkPermission()
    {

    }


    /**
     * Auto-generate the event alias if it has not been set yet
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->name);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_cars WHERE alias=?")->execute($varValue);

        // Check whether the alias exists
        if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias)
        {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }


    /**
     * Add the type of input field
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listCars($arrRow)
    {
        return '<div class="tl_content_left">' . $arrRow['name'] . '</div>';
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }


        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Return all gallery templates as array
     *
     * @return array
     */
    public function getGalleryTemplates()
    {
        return $this->getTemplateGroup('car_gallery_');
    }

    /**
     * Disable/enable a user group
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        $objVersions = new Versions('tl_cars', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cars']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cars']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_cars SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_cars.id=' . $intId . '" has been created' . $this->getParentEntries('tl_cars', $intId), __METHOD__, TL_GENERAL);

    }

    /**
     * Get all articles and return them as array
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getArticleAlias(DataContainer $dc)
    {
        $arrPids = array();
        $arrAlias = array();

        if (!$this->User->isAdmin)
        {
            foreach ($this->User->pagemounts as $id)
            {
                $arrPids[] = $id;
                $arrPids = array_merge($arrPids, $this->Database->getChildRecords($id, 'tl_page'));
            }

            if (empty($arrPids))
            {
                return $arrAlias;
            }

            $objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(" . implode(',', array_map('intval', array_unique($arrPids))) . ") ORDER BY parent, a.sorting")->execute($dc->id);
        }
        else
        {
            $objAlias = $this->Database->prepare("SELECT a.id, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid ORDER BY parent, a.sorting")->execute($dc->id);
        }

        if ($objAlias->numRows)
        {
            System::loadLanguageFile('tl_article');

            while ($objAlias->next())
            {
                $arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->title . ' (' . ($GLOBALS['TL_LANG']['COLS'][$objAlias->inColumn] ?: $objAlias->inColumn) . ', ID ' . $objAlias->id . ')';
            }
        }

        return $arrAlias;
    }

    /**
     * Add the source options depending on the allowed fields
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getSourceOptions(DataContainer $dc)
    {
        if ($this->User->isAdmin)
        {
            return array('default', 'internal', 'article', 'external');
        }

        $arrOptions = array('default', 'internal', 'article', 'external');

        // Add the option currently set
        if ($dc->activeRecord && $dc->activeRecord->source != '')
        {
            $arrOptions[] = $dc->activeRecord->source;
            $arrOptions = array_unique($arrOptions);
        }

        return $arrOptions;
    }
}
