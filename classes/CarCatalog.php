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
 * Provide methods to get all events of a certain period from the database.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class CarCatalog extends \Module
{

    /**
     * Current URL
     * @var string
     */
    protected $strUrl;


    /**
     * Current events
     * @var array
     */
    protected $arrCars = array();


    /**
     * Sort out protected archives
     *
     * @param array $arrCalendars
     *
     * @return array
     */
    protected function sortOutProtected($arrCalendars)
    {
        if (BE_USER_LOGGED_IN || !is_array($arrCalendars) || empty($arrCalendars))
        {
            return $arrCalendars;
        }

        $this->import('FrontendUser', 'User');
        $objCalendar = \CarCatalogModel::findMultipleByIds($arrCalendars);
        $arrCalendars = array();

        if ($objCalendar !== null)
        {
            while ($objCalendar->next())
            {
                if ($objCalendar->protected)
                {
                    if (!FE_USER_LOGGED_IN)
                    {
                        continue;
                    }

                    $groups = deserialize($objCalendar->groups);

                    if (!is_array($groups) || empty($groups) || count(array_intersect($groups, $this->User->groups)) < 1)
                    {
                        continue;
                    }
                }

                $arrCalendars[] = $objCalendar->id;
            }
        }

        return $arrCalendars;
    }

    /**
     * Generate a URL and return it as string
     *
     * @param \CalendarEventsModel $objEvent
     * @param string $strUrl
     *
     * @return string
     */
    protected function generateCarUrl($objCar, $strUrl)
    {
        switch ($objCar->source)
        {
            // Link to an external page
            case 'external':
                if (substr($objCar->url, 0, 7) == 'mailto:')
                {
                    return \StringUtil::encodeEmail($objCar->url);
                }
                else
                {
                    return ampersand($objCar->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objCar->getRelated('jumpTo')) !== null)
                {
                    /** @var \PageModel $objTarget */
                    return ampersand($objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = \ArticleModel::findByPk($objCar->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
                {
                    /** @var \PageModel $objPid */
                    return ampersand($objPid->getFrontendUrl('/items/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        $targetPage = \PageModel::findByPk($objCar->getRelated('pid')->jumpTo);
        if ($targetPage !== null)
        {
            $strUrl = $targetPage->getFrontendUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s');
            $strUrl = ampersand(sprintf($strUrl, ((!\Config::get('disableAlias') && $objCar->alias != '') ? $objCar->alias : $objCar->id)));
        }

        return $strUrl;
    }


    /**
     * Get all events of a certain period
     *
     * @param array $arrCalendars
     * @param integer $intStart
     * @param integer $intEnd
     *
     * @return array
     */
    protected function getAllCars($arrCatalogs)
    {
        if (!is_array($arrCatalogs))
        {
            return array();
        }

        $this->arrCars = array();

        foreach ($arrCatalogs as $id)
        {
            $strUrl = $this->strUrl;
            $objCatalog = \CarCatalogModel::findByPk($id);

            // Get the current "jumpTo" page
            if ($objCatalog !== null && $objCalendar->jumpTo && ($objTarget = $objCalendar->getRelated('jumpTo')) !== null)
            {
                /** @var \PageModel $objTarget */
                $strUrl = $objTarget->getFrontendUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s');
            }

            // Get the events of the current period
            $objEvents = \CarModel::findByPid($id);

            if ($objEvents === null)
            {
                continue;
            }

            while ($objEvents->next())
            {
                $this->addEvent($objEvents, $objEvents->startTime, $objEvents->endTime, $strUrl, $intStart, $intEnd, $id);

                // Recurring events
                if ($objEvents->recurring)
                {
                    $arrRepeat = deserialize($objEvents->repeatEach);

                    if ($arrRepeat['value'] < 1)
                    {
                        continue;
                    }

                    $count = 0;
                    $intStartTime = $objEvents->startTime;
                    $intEndTime = $objEvents->endTime;
                    $strtotime = '+ ' . $arrRepeat['value'] . ' ' . $arrRepeat['unit'];

                    while ($intEndTime < $intEnd)
                    {
                        if ($objEvents->recurrences > 0 && $count++ >= $objEvents->recurrences)
                        {
                            break;
                        }

                        $intStartTime = strtotime($strtotime, $intStartTime);
                        $intEndTime = strtotime($strtotime, $intEndTime);

                        // Skip events outside the scope
                        if ($intEndTime < $intStart || $intStartTime > $intEnd)
                        {
                            continue;
                        }

                        $this->addEvent($objEvents, $intStartTime, $intEndTime, $strUrl, $intStart, $intEnd, $id);
                    }
                }
            }
        }

        // Sort the array
        foreach (array_keys($this->arrEvents) as $key)
        {
            ksort($this->arrEvents[$key]);
        }

        // HOOK: modify the result set
        if (isset($GLOBALS['TL_HOOKS']['getAllEvents']) && is_array($GLOBALS['TL_HOOKS']['getAllEvents']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getAllEvents'] as $callback)
            {
                $this->import($callback[0]);
                $this->arrEvents = $this->{$callback[0]}->{$callback[1]}($this->arrEvents, $arrCalendars, $intStart, $intEnd, $this);
            }
        }

        return $this->arrEvents;
    }


    /**
     * Generate the content element
     */
    public function addGallery($objCar)
    {

        /** @var \PageModel $objPage */
        global $objPage;
        $objCar->multiSRC = deserialize($objCar->multiSRC);

        // Return if there are no files
        if (!is_array($objCar->multiSRC) || empty($objCar->multiSRC))
        {
            return '';
        }


        // Get the file entries from the database
        $objFiles = \FilesModel::findMultipleByUuids($objCar->multiSRC);

        if ($objFiles === null)
        {
            if (!\Validator::isUuid($objCar->multiSRC[0]))
            {
                return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
            }

            return '';
        }

        $images = array();
        $auxDate = array();

        // Get all images
        while ($objFiles->next())
        {
            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
            {
                continue;
            }


            // Single files
            if ($objFiles->type == 'file')
            {
                $objFile = new \File($objFiles->path, true);

                if (!$objFile->isImage)
                {
                    continue;
                }

                $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

                if (empty($arrMeta))
                {
                    if ($objCar->metaIgnore)
                    {
                        continue;
                    }
                    elseif ($objPage->rootFallbackLanguage !== null)
                    {
                        $arrMeta = $this->getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
                    }
                }

                // Use the file name as title if none is given
                if ($arrMeta['title'] == '')
                {
                    $arrMeta['title'] = specialchars($objFile->basename);
                }

                // Add the image
                $images[$objFiles->path] = array(
                    'id'        => $objFiles->id,
                    'uuid'      => $objFiles->uuid,
                    'name'      => $objFile->basename,
                    'singleSRC' => $objFiles->path,
                    'alt'       => $arrMeta['title'],
                    'imageUrl'  => $arrMeta['link'],
                    'caption'   => $arrMeta['caption'],
                );

                $auxDate[] = $objFile->mtime;
            }

            // Folders
            else
            {
                $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

                if ($objSubfiles === null)
                {
                    continue;
                }

                while ($objSubfiles->next())
                {
                    // Skip subfolders
                    if ($objSubfiles->type == 'folder')
                    {
                        continue;
                    }

                    $objFile = new \File($objSubfiles->path, true);

                    if (!$objFile->isImage)
                    {
                        continue;
                    }

                    $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

                    if (empty($arrMeta))
                    {
                        if ($objCar->metaIgnore)
                        {
                            continue;
                        }
                        elseif ($objPage->rootFallbackLanguage !== null)
                        {
                            $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->rootFallbackLanguage);
                        }
                    }

                    // Use the file name as title if none is given
                    if ($arrMeta['title'] == '')
                    {
                        $arrMeta['title'] = specialchars($objFile->basename);
                    }

                    // Add the image
                    $images[$objSubfiles->path] = array(
                        'id'        => $objSubfiles->id,
                        'uuid'      => $objSubfiles->uuid,
                        'name'      => $objFile->basename,
                        'singleSRC' => $objSubfiles->path,
                        'alt'       => $arrMeta['title'],
                        'imageUrl'  => $arrMeta['link'],
                        'caption'   => $arrMeta['caption'],
                    );

                    $auxDate[] = $objFile->mtime;
                }
            }
        }

        // Sort array
        switch ($objCar->sortBy)
        {
            default:
            case 'name_asc':
                uksort($images, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($images, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
                break;

            case 'meta': // Backwards compatibility
            case 'custom':
                if ($objCar->orderSRC != '')
                {
                    $tmp = deserialize($objCar->orderSRC);

                    if (!empty($tmp) && is_array($tmp))
                    {
                        // Remove all values
                        $arrOrder = array_map(function ()
                        {
                        }, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k => $v)
                        {
                            if (array_key_exists($v['uuid'], $arrOrder))
                            {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images))
                        {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }
                }
                break;

            case 'random':
                shuffle($images);
                break;
        }

        $images = array_values($images);

        // Limit the total number of items (see #2652)
        if ($objCar->numberOfItems > 0)
        {
            $images = array_slice($images, 0, $objCar->numberOfItems);
        }
        $strPagination = '';
        $offset = 0;
        $total = count($images);
        $limit = $total;

        // Paginate the result of not randomly sorted (see #8033)
        if ($objCar->perPage > 0 && $objCar->sortBy != 'random')
        {
            // Get the current page
            $id = 'page_g' . $objCar->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $objCar->perPage), 1))
            {
                /** @var \PageError404 $objHandler */
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            // Set limit and offset
            $offset = ($page - 1) * $objCar->perPage;
            $limit = min($objCar->perPage + $offset, $total);

            $objPagination = new \Pagination($total, $objCar->perPage, \Config::get('maxPaginationLinks'), $id);
            $strPagination = $objPagination->generate("\n  ");
        }

        $rowcount = 0;
        $colwidth = floor(100 / $objCar->perRow);
        $intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $objCar->perRow)) : floor((\Config::get('maxImageWidth') / $objCar->perRow));
        $strLightboxId = 'lightbox[lb' . $objCar->id . ']';
        $body = array();

        // Rows
        for ($i = $offset; $i < $limit; $i = ($i + $objCar->perRow))
        {
            $class_tr = '';

            if ($rowcount == 0)
            {
                $class_tr .= ' row_first';
            }

            if (($i + $objCar->perRow) >= $limit)
            {
                $class_tr .= ' row_last';
            }

            $class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

            // Columns
            for ($j = 0; $j < $objCar->perRow; $j++)
            {
                $class_td = '';

                if ($j == 0)
                {
                    $class_td .= ' col_first';
                }

                if ($j == ($objCar->perRow - 1))
                {
                    $class_td .= ' col_last';
                }

                $objCell = new \stdClass();
                $key = 'row_' . $rowcount . $class_tr . $class_eo;

                // Empty cell
                if (!is_array($images[($i + $j)]) || ($j + $i) >= $limit)
                {
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_' . $j . $class_td;
                }
                else
                {
                    // Add size and margin
                    $images[($i + $j)]['size'] = $objCar->size;
                    $images[($i + $j)]['imagemargin'] = $objCar->imagemargin;
                    $images[($i + $j)]['fullsize'] = $objCar->fullsize;

                    $this->addImageToTemplate($objCell, $images[($i + $j)], $intMaxWidth, $strLightboxId);

                    // Add column width and class
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_' . $j . $class_td;
                }

                $body[$key][$j] = $objCell;
            }

            ++$rowcount;
        }

        $strTemplate = 'car_gallery_default';

        // Use a custom template
        if (TL_MODE == 'FE' && $objCar->galleryTpl != '')
        {
            $strTemplate = $objCar->galleryTpl;
        }

        /** @var \FrontendTemplate|object $objTemplate */
        $objTemplate = new \FrontendTemplate($strTemplate);
        $objTemplate->perRow = $objCar->perRow;
        $objTemplate->body = $body;
        $objTemplate->pagination = $strPagination;

        return $objTemplate->parse();
    }


}



