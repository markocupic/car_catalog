<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace MCupic;


/**
 * Front end module "event reader".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleCarReader extends \CarCatalog
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_car';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['carreader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['events']) && \Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        // Do not index or cache the page if no event has been specified
        if (!\Input::get('items'))
        {
            /** @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        $this->car_catalog = $this->sortOutProtected(deserialize($this->car_catalog));

        // Do not index or cache the page if there are no calendars
        if (!is_array($this->car_catalog) || empty($this->car_catalog))
        {
            /** @var \PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var \PageModel $objPage */
        global $objPage;
        $this->loadLanguageFile('tl_cars');

        $this->Template->car = '';
        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        // Get the current event
        $objCar = \CarsModel::findPublishedByParentAndIdOrAlias(\Input::get('items'), $this->car_catalog);

        if (null === $objCar)
        {
            /** @var \PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }

        // Overwrite the page title (see #2853 and #4955)
        if ($objCar->title != '')
        {
            $objPage->pageTitle = strip_tags(strip_insert_tags($objCar->name));
        }

        // Overwrite the page description
        if ($objCar->name != '')
        {
            $objPage->description = $this->prepareMetaDescription($objCar->name);
        }


        $objTemplate = new \FrontendTemplate($this->car_template);
        $objTemplate->setData($objCar->row());

        $objTemplate->class = ($objCar->cssClass != '') ? ' ' . $objCar->cssClass : '';
        $objTemplate->locationLabel = $GLOBALS['TL_LANG']['MSC']['location'];
        $objTemplate->details = '';
        $objTemplate->hasDetails = false;

        // Clean the RTE output
        $arrTextareas = array('history', 'state', 'configuration');
        foreach ($arrTextareas as $field)
        {
            if($field > 0 || $field != '')
            $has = 'has' . ucfirst($field);
            $objTemplate->{$has} = true;

            if ($objPage->outputFormat == 'xhtml')
            {
                $objTemplate->{$field} = \StringUtil::toXhtml($objCar->{$field});
            }
            else
            {
                $objTemplate->{$field} = \StringUtil::toHtml5($objCar->{$field});
            }

        }
        // Add Field-Legends
        $arrLegends = array();
        foreach($GLOBALS['TL_LANG']['tl_cars'] as $fieldname => $arrLang)
        {
            $arrLegends[$fieldname] = is_array($arrLang) ? $GLOBALS['TL_LANG']['tl_cars'][$fieldname][0] : $GLOBALS['TL_LANG']['tl_cars'][$fieldname];
        }
        $objTemplate->arrLegends = $arrLegends;


        /**
         * // Display the "read more" button for external/article links
         * if ($objCar->source != 'default')
         * {
         * $objTemplate->details = true;
         * $objTemplate->hasDetails = true;
         * }
         *
         * // Compile the event text
         * else
         * {
         * $id = $objCar->id;
         *
         * $objTemplate->details = function () use ($id)
         * {
         * $strDetails = '';
         * $objElement = \ContentModel::findPublishedByPidAndTable($id, 'tl_cars');
         *
         * if ($objElement !== null)
         * {
         * while ($objElement->next())
         * {
         * $strDetails .= $this->getContentElement($objElement->current());
         * }
         * }
         *
         * return $strDetails;
         * };
         *
         * $objTemplate->hasDetails = (\ContentModel::countPublishedByPidAndTable($id, 'tl_cars') > 0);
         * }
         **/

        // Add gallery to template
        if ($objCar->addGallery)
        {
            $strGallery = $this->addGallery($objCar);
            if ($strGallery != '')
            {
                $objTemplate->addGallery = true;
                $objTemplate->gallery = $strGallery;
            }
        }


        $this->Template->car = $objTemplate->parse();


    }
}
