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
define('_JEXEC', 1);

define('JPATH_BASE', dirname(__FILE__));

define('DS', DIRECTORY_SEPARATOR);
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false)
{
    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));

    $_REQUEST = array_merge($_REQUEST, (array) json_decode(trim(file_get_contents('php://input')), true));
}

require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

JDEBUG ? $_PROFILER->mark('afterLoad') : null;

/**
 * CREATE THE APPLICATION
 *
 * NOTE :
 */
$mainframe = JFactory::getApplication('site');


$db = JFactory::getDbo();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$db->setQuery('select * from #__cars_mapitems where parsed=0 and id!=1');
//$linkObject = $db->loadObject();



$linkObject = new stdClass();


if (empty($linkObject))
{
    die('all parsed');
}


include_once './simple_html_dom.php';

//$linkObject->link = 'http://chrysler.ilcats.ru/subgroup/model/075/year/07/group/5/clid/1';
$linkObject->link = 'http://bpn.ge/ttmp/23852.html';


$html = file_get_html($linkObject->link);



$rootTable = $html->find('.mainview');
if (empty($rootTable))
{
    die('root table not found');
}


if (count($rootTable) != 1)
{
    die('some problem with mainview');
}

$rootTable = $rootTable[0];

$tabs = $rootTable->find('.scrl');
if (count($tabs) !== 2)
{
    die('no tabs found');
}

$leftCplumn = $tabs[0];
$rightColumn = $tabs[1];




$images = $leftCplumn->find('img');

$map = $leftCplumn->find('map');
$maphtml = '';
 $divsArray = array();

if (!empty($map[0]->innertext))
{
    $maphtml = $map[0]->innertext;
    $maphtml = explode('</MAP>', $maphtml);
    //finish map html
    $maphtml = $maphtml[0];
    $divs = $leftCplumn->find('.imageContainer div');

   
    if (!empty($divs))
    {
        foreach ($divs as $ddv)
        {
            $divsArray[] = $ddv->getAllAttributes();
        }
    }
}
if (empty($images))
{
    die('image not fount');
}
$image = $images[0]->src;


$imageInfo = getimagesize($image);



if (empty($imageInfo))
{
    die('cannot get image info');
}

$ext = '.png';
if ($imageInfo == 1)
{
    $ext = '.gif';
}
if ($imageInfo == 2)
{
    $ext = '.jpg';
}
if ($imageInfo == 3)
{
    $ext = '.png';
}

$imageFile = file_get_contents($image);


$imageName = md5($image) . $ext;

$t = file_put_contents('./maps/' . $imageName, $imageFile);
//$t = file_put_contents('.txt', $imageFile);


//update __cars_mapitems 
//set image = $imageName
//    mapHtml=$maphtml
//    divs=serialize($divsArray)





//find parts
$notes = $rightColumn->find('tr');
$insertNotes = array();
$insertItems = array();


if (!empty($notes))
{
    $isNote = '';
    foreach ($notes as $note)
    {
        $tdss = $note->find('td');
        if (count($tdss) == 0)
        {
            continue;
        }

        if (count($tdss) == 1)
        {
            if ($isNote == 'td')
            {
                $insertNotes = array();
            }

            $insertNotes[] = $tdss[0]->innertext;
            $isNote = 'tn';
        }


        // item
        if (count($tdss) == 4)
        {
            $inNote = serialize($insertNotes);


            $kk = new stdClass();
            $kk->part_image_code = $tdss[0]->innertext;
            $kk->part_number = $tdss[1]->innertext;
            $kk->description = $tdss[2]->innertext;
            $kk->count = $tdss[3]->innertext;
            $kk->notes = $insertNotes;
            !empty($insertNotes) ? serialize($insertNotes) : '';
            $insertItems[] = $kk;
            $isNote = 'td';
        }
    }
}

if (!empty($insertItems))
{
    foreach ($insertItems as $one)
    {
        // insert part intoo table
    }
}

if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'Debug')
{
    echo '<pre>' . __FILE__ . ' -->>| <b> Line </b>' . __LINE__ . '</pre><pre>';
    print_r($insertItems);
    die;
}






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
