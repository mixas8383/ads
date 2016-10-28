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


$db = JFactory::getDbo();


$db->setQuery('select * from #__cars_crisler where parsed=0 and id!=1');
$linkObject = $db->loadObject();

if (empty($linkObject))
{
    die('all parsed');
}


include_once 'simple_html_dom.php';


$html = file_get_html($linkObject->link);

$rootTable = $html->find('#ban1');
if (empty($rootTable))
{
    die('root table not found');
}
$finedTable = $rootTable[0]->find('table');
if (empty($rootTable))
{
    die('fined table not found');
}

$tds = $finedTable[0]->find('td');
if (empty($tds))
{
    die('fined td not found');
}
$insert = array();

foreach ($tds as $one)
{
    $tmp = new stdClass();


    $aTag = $one->find('a');

    if (empty($aTag))
    {
        die('atag not fount');
    }

    $a = $aTag[0];

    $tmp->local_parent_id = $linkObject->id;
    $tmp->link = $a->href;
    $tmp->model = getExploded('model', $a->href);
    $tmp->group = getExploded('group', $a->href);
    $tmp->year = getExploded('year', $a->href);
    $tmp->title = $a->innertext;
    $insert[] = $tmp;
}






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





        $db->setQuery(''
                . ''
                . 'INSERT INTO #__cars_groups (`title`,`group`,`year`,`link`,`local_parent_id`) VALUES '
                . '('
                . '' . $db->quote($value->title)
                . ',' . $db->quote($value->group)
                . ',' . $db->quote($value->year)
                . ',' . $db->quote($value->link)
                . ',' . $db->quote($value->local_parent_id)
                . ''
                . ''
                . ''
                . ')'
                . ''
                . '');

        if ($db->execute())
        {
            $db->setQuery('update #__cars_crisler set `parsed`=1 where id='.$linkObject->id);
            $db->execute();
        }
    }
}
header('Location: /step2.php');
echo '<pre>' . __FILE__ . ' -->>| <b> Line </b>' . __LINE__ . '</pre><pre>';
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
