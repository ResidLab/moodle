<?php
require_once("CalendarClient.php");
$client = new CalendarClient("Amal","aaa");
$resMessage=$client->cadastrarTurma();

if ($resMessage)
{
	$simplexml = new SimpleXMLElement($resMessage->str);
	@unlink('cadastroturma.html');
	$novoarquivo = fopen("cadastroturma.html", "a+");
	fwrite($novoarquivo, urldecode($simplexml));
	fclose($novoarquivo);
	echo 'lero lero <iframe name="<?php echo(COOK_PREF); ?>_top" src="cadastroturma.html" frameborder="0" style="border: 0px; width: 100%; height: 100%"></iframe>';

}
else{
	echo 'ERRO';
}
?>
