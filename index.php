<?php

$app = require(realpath('Shunt/Shunt.php'));

// reference our other dependency namespaces
//$app->load('Appstuff', 'Appstuff');

//echo '<pre>' . print_r($app, 1) . '</pre>';

//$app->setNamespace('plugins', 'jfhfjhfh');
//$app->setPath('plugins', 'xxx'); 

$app->using('middleware')->define('test', function($x) use ($app) {
    echo 'You are ' . $x . ' calling from IP ' . $app->getIp();
});

$app->using('middleware')->test('Barney');

$test = array('name' => 'test');
$app->load('Spyc', 'External/Spyc/Spyc.php');

$app->using('middleware')->define('writeYaml', function($input) use ($app) {
    $app->using('debug')->pre(Spyc::YAMLDump($input));
});
$app->using('middleware')->writeYaml($test);

// set up a loader for Mustache
$app->load('Mustache', 'External/Mustache/src/Mustache');
$m = new Mustache_Engine;
echo $m->render('Hello {{planet}}', array('planet' => 'World!'));



//$app->util('macro')->define('test', function($x) use ($app) {
//    echo 'You are ' . $x . ' calling from IP ' . $app->env('ip');
//});
//
//$app->util('macro')->call('test', 'Fred Flintstone');
echo '<hr/>';
$app->route('test')
        ->url('/test/$arg(/$wtf*)')
        ->defaults(array('wtf' => 'mine'))
        //->skip();
        ->methods('post');

$app->route('css')
        ->url('/$file*.css');

$app->run();
exit;

echo '<hr/>';
echo 'Domain: ' . $app->getDomain() . '<br/>';
echo 'Method: ' . $app->getMethod() . '<br/>';
echo 'Doc Root: ' . $app->getDocRoot() . '<br/>';
echo 'Accept Encoding: ' . $app->getAcceptEncoding() . '<br/>';
echo 'Browser: ' . $app->getBrowser() . '<br/>';
echo "<hr/>Browser is Chrome?<br/>\n";
var_dump($app->getBrowser()->is('chrome'));
echo "<hr/>\n";
echo 'Content Type: ' . $app->getContentType() . '<br/>';
//echo 'Content Type: ' . $app->setContentType('text/xml') . '<br/>';
echo 'Url: ' . $app->getUrl() . '<br/>';
echo 'Query String: ' . $app->getQueryString() . '<br/>';
echo 'APC on?: ' . $app->getApc() . '<br/>';
echo 'IP: ' . $app->getIp() . '<br/>';
$app->using('debug')->pre($app->getQueryStringData());
echo "<hr/>page variable available in query string?<br/>\n";
var_dump($app->queryUrl('page'));
echo $app->queryUrl('page');
echo "<hr/>\n";

$app->using('debug')->dump($app->buildUrl('test', array('arg' => 'test', 'me' => 'zippy', 'wtf' => 'hello/this')));
$app->using('debug')->dump($app->buildUrl('test', array('arg' => 'test', 'me' => 'zippy', 'wtf' => array('hello', 'this', 'me'))));
$app->using('debug')->dump($app->buildUrl('test', array('arg' => 'test')));
$app->using('debug')->dump($app->buildUrl('test'));
$app->using('debug')->dump($app->buildUrl('css', array('file' => array('path', 'to', 'css'))));

$app->set('my test', '12345t');
echo $app->get('my test');
$app->myVar = 'hello';

//$app->setPluginNamespace('wee');
//$app->setPluginPath('wee');
$app->using('debug')->console($app, 'App', 0);
$app->using('debug')->console($app->getRoutes(), 'Routes');
$app->using('db')->setup(function($test) {
    echo $test;
});
$app->using('db')->call('hello');

echo '<hr/>';
$app->using('debug')->pre($app->find($app->getUrl()));



if($app->getUrl() == '/test-redirect') {
    //$app->redirect('/redirected');
    $app->respond(404);
}
?>
