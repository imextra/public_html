<?php
require_once 'HTTP/Request2.php';

$request = new HTTP_Request2('http://kinoafisha.info/', HTTP_Request2::METHOD_GET);

$response = $request->send();


// echo '<pre>',print_r($response->getStatus()),'</pre>';
// echo '<pre>',print_r($response->getHeaders()),'</pre>';
// echo '<pre>',print_r($response->getCookieJar()),'</pre>';
// echo '<pre>',print_r($response->getLastEvent()),'</pre>';
echo '<pre>',print_r($response->getBody()),'</pre>';
		// echo $response->getStatus();
	// echo $response->getBody();

// echo '<pre>',print_r($arParams),'</pre>';
?>