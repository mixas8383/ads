<?php

/**
 * @package    Joomla.Site
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
 */
define('JOOMLA_MINIMUM_PHP', '5.3.10');

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<'))
{
    die('Your host needs to use PHP ' . JOOMLA_MINIMUM_PHP . ' or higher to run this version of Joomla!');
}

// Saves the start time and memory usage.
$startTime = microtime(1);
$startMem = memory_get_usage();

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used in the installation folder rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

if (file_exists(__DIR__ . '/defines.php'))
{
    include_once __DIR__ . '/defines.php';
}

if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

// Set profiler start time and memory usage and mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->setStart($startTime, $startMem)->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('site');


include_once 'simple_html_dom.php';


$html = file_get_html('http://chrysler.ilcats.ru/');

// get tables with id prefix ddtr
$tables = $html->find('tr[id^=ddtr]');
$modelGroup = 'chrysler';

if (!empty($tables))
{
    $insert = array();
    foreach ($tables as $one)
    {

        //$title = new simple_html_dom($one->find('p[id^=ddp]')) ;
        $p = $one->find('p[id^=ddp]');


        //get model title
        $titleTag = $p[0]->innertext;
        $MODEL = '';

        preg_match("'<b>(.*?)</b>'si", $titleTag, $match);

        if (!empty($match) && is_array($match))
        {
            $MODEL = strip_tags($match[0]);
        }
        //end get model title
        //get model codes
        $innerTable = $one->find('table');
        $tableTags = $innerTable[0]->find('tr');
        if (!empty($tableTags))
        {
            foreach ($tableTags as $tableTag)
            {
                $tmp = new stdClass();
                $tmp->model = $MODEL;
                $tmp->group = $modelGroup;
                $tds = $tableTag->find('td');

                if (count($tds) == 2)
                {
                    $leftCplumn = $tds[0];
                    $rightColumn = $tds[1];

                    $linkTag = $leftCplumn->find('a');
                    if (!empty($linkTag[0]))
                    {
                        $link = $linkTag[0]->href;
                        $tmp->link = $link;
                        $modelCode = getExploded('model', $link);
                        $modelYear = getExploded('year', $link);
                        $tmp->model_code = $modelCode;
                        $tmp->year = $modelYear;
                    }

                    $imageTag = $rightColumn->find('img');
                    if (!empty($imageTag[0]))
                    {
                        $imageSrc = $imageTag[0]->src;
                        $tmp->images = $imageSrc;
                    }

                    $insert[] = $tmp;
                }
            }
        }

        //end get model codes
    }
}



$db = JFactory::getDbo();

if (!empty($insert))
{

    foreach ($insert as $value)
    {

        $yer = (int) $value->year;

        if ($yer < 16)
        {
            if ($yer < 10)
            {
                $value->year = '200' . $yer . '-00-00';
            } else
            {
                 $value->year = '20' . $yer . '-00-00';
            }
        } else
        {
            $value->year = '19' . $yer . '-00-00';
        }

        $value->images = 'http://chrysler.ilcats.ru' . $value->images;

       

        $db->setQuery(''
                . ''
                . 'INSERT INTO #__cars_crisler (`title`,`group`,`model`,`model_code`,`year`,`link`,`images`) VALUES '
                . '('
                . '' . $db->quote($value->model)
                . ',' . $db->quote($value->group)
                . ',' . $db->quote($value->model)
                . ',' . $db->quote($value->model_code)
                . ',' . $db->quote($value->year)
                . ',' . $db->quote($value->link)
                . ',' . $db->quote($value->images)
                . ''
                . ''
                . ''
                . ')'
                . ''
                . '');
       // $db->execute();
        
       
             
         
        
    }
}
 
     echo '<pre>'.__FILE__.' -->>| <b> Line </b>'.__LINE__.'</pre><pre>';
     print_r($db);
     die;


function getExploded($name, $string, $delimeter = '/')
{


    if (empty($string))
    {
        return false;
    }

    $string = explode($delimeter, $string);





    foreach ($string as $key => $one)
    {
        if ($one == $name and isset($string[$key + 1]))
        {
            return $string[$key + 1];
        }
    }
    return false;
}
