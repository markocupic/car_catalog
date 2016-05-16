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
 * Front end module "car list".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleCarList extends \CarCatalog
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_car_list';


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

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['carlist'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->car_catalog = $this->sortOutProtected(deserialize($this->car_catalog, true));

        // Return if there are no catalogs
        if (!is_array($this->car_catalog) || empty($this->car_catalog))
        {
            return '';
        }

        // Show the car reader if an item has been selected
        if ($this->car_readerModule > 0 && (isset($_GET['items']) || (\Config::get('useAutoItem') && isset($_GET['auto_item']))))
        {
            return $this->getFrontendModule($this->car_readerModule, $this->strColumn);
        }

        $this->strUrl = preg_replace('/\?.*$/', '', \Environment::get('request'));


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
        $arrLang = $GLOBALS['TL_LANG']['tl_cars'];
        $arrCars = array();


        // add rows to $arrData
        $aSearch = array('UPDATE','DELETE','ALTER','TRUNCATE','REPLACE','OPTIMIZE');
        $aReplace = array('','','','','','');
        $this->filterExpression = str_ireplace($aSearch, $aReplace, $this->filterExpression);
        $arrFilter = json_decode($this->filterExpression);
        $filter = '';
        if (is_array($arrFilter) && !empty($arrFilter))
        {
            $expr = $arrFilter[0][0];
            $expr = str_replace('?', '%s', $expr);
            $arrArgs = $arrFilter[1];
            $arrArgs = array_map(function ($e)
            {
                return (!is_numeric($e)) ? sprintf('"%s"', $e) : $e;
            }, $arrArgs);

            $filter = ' AND ' . vsprintf($expr, $arrArgs);
        }


        $objCar = $this->Database->prepare('SELECT * FROM tl_cars WHERE pid IN(0,' . implode(",", $this->car_catalog) . ')' . $filter)->execute(1);
        while ($dataRecord = $objCar->fetchAssoc())
        {
            $dataRecord['parent'] = $dataRecord['pid'];
            $dataRecord['href'] = $this->generateCarUrl(\CarsModel::findByPk($dataRecord['id']), $this->strUrl);
            //die($dataRecord['href']);
            $dataRecord['abstract'] = $arrLang[$dataRecord['transmission']] . ', ' . $arrLang[$dataRecord['fuel']] . ', ' . $dataRecord['power'] . 'PS';
            $arrCars[] = $dataRecord;

        }


        $total = count($arrCars);
        $limit = $total;
        $offset = 0;

        // Overall limit
        if ($this->car_limit > 0)
        {
            $total = min($this->car_limit, $total);
            $limit = $total;
        }

        // Pagination
        if ($this->perPage > 0)
        {
            $id = 'page_c' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1))
            {
                /** @var \PageError404 $objHandler */
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            $offset = ($page - 1) * $this->perPage;
            $limit = min($this->perPage + $offset, $total);

            $objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }


        $imgSize = false;

        // Override the default image size
        if ($this->imgSize != '')
        {
            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
            {
                $imgSize = $this->imgSize;
            }
        }

        // Parse cars
        for ($i = $offset; $i < $limit; $i++)
        {
            $car = $arrCars[$i];
            $blnIsLastCar = false;

            // Last car of the list
            if (($i + 1) == $limit)
            {
                $blnIsLastCar = true;
            }

            /** @var \FrontendTemplate|object $objTemplate */
            $objTemplate = new \FrontendTemplate($this->car_template);
            $objTemplate->setData($car);


            // Add the template variables
            $objTemplate->classList = $car['class'] . ((($headerCount % 2) == 0) ? ' even' : ' odd') . (($headerCount == 0) ? ' first' : '') . ($blnIsLastCar ? ' last' : '') . ' catalog_' . $car['parent'];
            $objTemplate->readMore = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $car['name']));
            $objTemplate->more = $GLOBALS['TL_LANG']['MSC']['more'];
            $objTemplate->locationLabel = $GLOBALS['TL_LANG']['MSC']['location'];


            $objTemplate->addImage = false;

            // Add an image
            if ($car['addImage'] && $car['singleSRC'] != '')
            {
                $objModel = \FilesModel::findByUuid($car['singleSRC']);

                if ($objModel === null)
                {
                    if (!\Validator::isUuid($car['singleSRC']))
                    {
                        $objTemplate->text = '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
                    }
                }
                elseif (is_file(TL_ROOT . '/' . $objModel->path))
                {
                    if ($imgSize)
                    {
                        $car['imagesize'] = $imgSize;
                    }

                    $car['singleSRC'] = $objModel->path;
                    $this->addImageToTemplate($objTemplate, $car);
                }
            }


            $strCars .= $objTemplate->parse();

            ++$carCount;
            ++$headerCount;
        }

        // No cars found
        if ($strCars == '')
        {
            $strCars = "\n" . '<div class="empty">' . $strEmpty . '</div>' . "\n";
        }

        // See #3672
        $this->Template->headline = $this->headline;
        $this->Template->cars = $strCars;
    }
}
