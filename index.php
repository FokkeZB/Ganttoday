<?php 

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('Location: http://github.com/fokkezb/Ganttoday');
	exit;
}

$projectXML = simplexml_load_string($_POST["projectXML"]);

$sxe = new SimpleXMLElement($projectXML->asXML());
$sxe->registerXPathNamespace('n', 'http://schemas.microsoft.com/project');

$tasks = $sxe->xpath('/n:Project/n:Tasks/n:Task[n:Summary = 0 and n:Start[starts-with(text(), "' . date('Y-m-d') . '")]]');

header('Content-type: text/html');

/* TODO:

	- Instructie om project naar vandaag op te schuiven
	- Pad naar getoonde taken laten zien (via hiërarchie, niet via afhankelijkheden)
	- Percentage voltooid wijzigen (ook dmv checkbox op 0% of 100%)
	
	- Opmaak
	- Blog
	
	- Eventueel notities nog direct kunnen wijzigen

 */

?> 
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>Gantter.com - Today</title>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
		<meta name="viewport" content="width=device-width,height=device-height,maximum-scale=1.0">
		<link rel="stylesheet" href="https://app.gantter.com/css/styles.css?v=15242168" type="text/css" media="screen,projection,print">
		<link rel="stylesheet" href="https://app.gantter.com/css/default-theme.css?v=15242168" type="text/css" media="screen,projection,print">
		<link rel="icon" href="https://app.gantter.com/images/favicon.ico" type="image/x-icon">
		<link rel="shortcut icon" href="https://app.gantter.com/images/favicon.ico" type="image/x-icon">
		<link href="https://www.google.com/uds/api/picker/1.0/05c87704cd84b49307c16b1e4e9aee7c/default.css" type="text/css" rel="stylesheet">
	</head>
	<body>
	
		<h1>Today</h1>

		<ul>
			<? foreach ($tasks as $task): ?>
				<li><?= $task->Name ?></li>
			<? endforeach ?>
		</ul>
	
	</body>
</html>