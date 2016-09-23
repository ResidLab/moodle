<link type="text/css" rel="stylesheet"
	href="../scripts/calendar/dhtmlgoodies_calendar.css" media="screen" />
<link
	type="text/css" rel="stylesheet" href="../scripts/form/form.css"
	media="screen" />
<link rel="stylesheet"
	href="../scripts/tooltip/form-field-tooltip.css" media="screen"
	type="text/css">

<script
	type="text/javascript"
	src="../scripts/calendar/dhtmlgoodies_calendar.js"></script>
<script
	type="text/javascript" src="../scripts/form/form.js"></script>
<script
	type="text/javascript" src="../scripts/tooltip/rounded-corners.js"></script>
<script
	type="text/javascript" src="../scripts/tooltip/form-field-tooltip.js"></script>

<?php
global $CFG;
include_once('../scripts/functions.php');
include_once('../../../config.php');
require_once("ClienteWim.php");
$id_curso	= $_GET['course'];

$info_turma = pegar_info_classe_wims($id_curso);

$referencia = current($info_turma)->ref_classe_wims;

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (!empty($referencia)) {
	error(' Invalid Access! ');
}
else {
	$navlinks[0] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);

	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';
	if( !isset( $_POST['session'] ) ) {
		echo "<form name='form1' target=\"_self\" method='POST' action='#' onsubmit=\"if(!isFormValid()){alert('Verifique os campos marcados de vermelho !');return false;}\">";
		$session_name = 'wims'.$USER->id;
		$email = $_SESSION["$session_name"]["emailprofessor"];

		echo "<br/><br/><input name='session' type='hidden' value=".$_GET['session'].">";
		$input8	= "<input name='codigo_acesso' type='text' maxlength='50' required=1 style='width:400px' tooltipText='C&oacute;digo de acesso a turma no ambiente WIMS, enviado para o email $email.' />";
		$table->data[] = array (get_string( 'configinput8','block_wim' ),$input8);
		print_table($table);
		echo "<br/><br/><input name='gravar' type='submit' value='Gravar'>";
		echo "<input name='voltar' type='button' value='Voltar' onclick='history.back();'>";
		echo "</form>";

		print_footer( $course );

	} else {

		$client = new ClienteWim();
		$resMessage = $client->confirmaCadastroTurma($_POST);

		if ($resMessage)
		{
			$simplexml = new SimpleXMLElement($resMessage->str);
			if($simplexml->turmaconfirmada == 'true'){

				$session_name = 'wims'.$USER->id;

				$sqlnumerodeentradas	= "SELECT COUNT(*) AS nent FROM {$CFG->prefix}config_wim WHERE id_curso=$id_curso";
				$linhanumerodeentradas	= get_records_sql($sqlnumerodeentradas);
				$total_entradas			= current($linhanumerodeentradas)->nent;

				if(empty($total_entradas)){
					$config	= new Object();
					$config->id_curso = $id_curso;
					$config->ref_classe_wims = $_POST['codigo_acesso'];
					$config->senha_professor = $_SESSION["$session_name"]["senhaprofessor"];
					$config->senha_classe = $_SESSION["$session_name"]["senhaclasse"];

					insert_record("config_wim", $config);
				}
				redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
				print_footer( $course );
			}else{
				redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
			}
		}
		else{
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
		}
	}
}
?>

<script type="text/javascript">
    var tooltipObj = new DHTMLgoodies_formTooltip();
    tooltipObj.setTooltipPosition('right');
    tooltipObj.setPageBgColor('#EEEEEE');
    tooltipObj.setTooltipCornerSize(15);
    tooltipObj.initFormFieldTooltip();
</script>
