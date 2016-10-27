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


$db->setQuery('select * from #__cars_groups where parsed=0 and downloaded=1 and id!=1');
$linkObject = $db->loadObject();

if (file_exists('./downloadedCarsSubgroup/' . $linkObject->id . '.html'))
{

    $linkObject->link = './downloadedCarsSubgroup/' . $linkObject->id . '.html';
}



if (file_exists($filename))
    if (empty($linkObject))
    {
        die('all parsed');
    }


include_once 'simple_html_dom.php';

//$linkObject->link = 'http://chrysler.ilcats.ru/subgroup/model/075/year/07/group/5/clid/1';
 if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'Debug')
 {
     echo '<pre>'.__FILE__.' -->>| <b> Line </b>'.__LINE__.'</pre><pre>';
     print_r($linkObject);
     die;
     
 }
$html = file_get_html($linkObject->link);

$rootTable = $html->find('#ban1');
if (empty($rootTable))
{
    die('root table not found');
}
$finedTable = $rootTable[0]->find('table');


echo '<pre>' . __FILE__ . ' -->>| <b> Line </b>' . __LINE__ . '</pre><pre>';
print_r($linkObject
);



if (empty($rootTable))
{
    die('fined table not found');
}

$trs = $finedTable[0]->find('.tl');




foreach ($trs as $one)
{
    $insert = array();
    $subgroupTitle = '';
    $subgroupId = '';


    $p = $one->find('p');


    if (!empty($p))
    {
        preg_match("'<b>(.*?)</b>'si", $p[0]->innertext, $match);

        if (!empty($match) && is_array($match))
        {
            $subgroupTitle = strip_tags($match[0]);
        }
    }
    // insert into subgroup table
    // 
    // end insert into subgroup table

    $innertrs = $one->find('tr');


    if (!empty($innertrs))
    {
        foreach ($innertrs as $two)
        {
            $links = $two->find('a');
            $tmp = new stdClass();
            if (!empty($links) and count($links) == 2)
            {
                $leftLink = $links[0];
                $rightLink = $links[1];
            } else
            {
                die('some links problem');
            }

            $a = $rightLink;




            $tmp->local_parent_id = $linkObject->id;
            $tmp->link = $a->href;
            $tmp->model = getExploded('model', $a->href);
            $tmp->group = getExploded('group', $a->href);
            $tmp->subgroup = getExploded('subgroup', $a->href);
            $tmp->year = getExploded('year', $a->href);
            $tmp->image = getExploded('image', $a->href);
            $tmp->title = $a->innertext;
            $tmp->additional_title = $leftLink->innertext;
            $subgroupId = $leftLink->innertext;
            $insert[] = $tmp;
        }
    } else
    {
      //  continue;
    }

    $subgroup = new stdClass();



    $subgroup->title = $subgroupTitle;
    $subgroup->local_parent_id = $linkObject->id;
    $subgroup->group = $linkObject->group;


    $db->setQuery(''
            . ''
            . 'INSERT INTO #__cars_subgroups (`title`,`local_parent_id`,`group`)'
            . ' VALUES '
            . '('
            . '' . $db->quote($subgroup->title)
            . ',' . $db->quote($subgroup->local_parent_id)
            . ',' . $db->quote($subgroup->group)
            . ''
            . ''
            . ')'
            . ''
            . '');

    $db->execute();
    $insertID = $db->insertid();


    if (!empty($insert))
    {
        foreach ($insert as $value)
        {
            $value->local_parent_id = $insertID;
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
                    . 'INSERT INTO #__cars_mapitems (`local_parent_id`,`link`,`model`,`group`,`subgroup`,`year`,`image`,`title`,`additional_title`)'
                    . ' VALUES '
                    . '('
                    . '' . $db->quote($value->local_parent_id)
                    . ',' . $db->quote($value->link)
                    . ',' . $db->quote($value->model)
                    . ',' . $db->quote($value->group)
                    . ',' . $db->quote($value->subgroup)
                    . ',' . $db->quote($value->year)
                    . ',' . $db->quote($value->image)
                    . ',' . $db->quote($value->title)
                    . ',' . $db->quote($value->additional_title)
                    . ''
                    . ''
                    . ')'
                    . ''
                    . '');
            $db->execute();
        }
    }
}





$db->setQuery('update #__cars_groups set `parsed`=1 where id=' . $linkObject->id);
$db->execute();








$id = (int) isset($_GET['id']) ? $_GET['id'] : 0;
$id+=1;
//header('Location: /step3.php?id='.$id);

echo '<pre>' . __FILE__ . ' -->>| <b> Line </b>' . __LINE__ . '</pre><pre>';

print_r($linkObject);
?>
<script type="text/javascript">
    window.location.href = '/step3.php'
</script>

<?php

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
