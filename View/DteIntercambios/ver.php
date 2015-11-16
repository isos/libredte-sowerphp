<h1>Intercambio N° <?=$DteIntercambio->codigo?></h1>
<p>Esta es la página del intercambio N° <?=$DteIntercambio->codigo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Email recibido</a></li>
        <li role="presentation"><a href="#documentos" aria-controls="documentos" role="tab" data-toggle="tab">Documentos</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="email">

<?php
$de = $DteIntercambio->de;
if ($DteIntercambio->de!=$DteIntercambio->responder_a)
    $de .= '<br/><span>'.$DteIntercambio->responder_a.'</span>';
new \sowerphp\general\View_Helper_Table([
    ['Recibido', 'De', 'Emisor', 'Firma', 'DTEs', 'Estado', 'Usuario'],
    [$DteIntercambio->fecha_hora_email, $de, $DteIntercambio->getEmisor()->razon_social, $DteIntercambio->fecha_hora_firma, num($DteIntercambio->documentos), $DteIntercambio->getEstado()->estado, $DteIntercambio->getUsuario()->usuario],
]);
?>

<p><strong>Asunto</strong>: <?=$DteIntercambio->asunto?></p>
<p><?=str_replace("\n", '</p><p>', strip_tags(base64_decode($DteIntercambio->mensaje)))?></p>
<?php if ($DteIntercambio->mensaje_html) : ?>
<a class="btn btn-default btn-lg btn-block" href="javascript:__.popup('<?=$_base?>/dte/dte_intercambios/html/<?=$DteIntercambio->codigo?>', 800, 600)" role="button">
    <span class="fa fa-html5" style="font-size:24px"></span>
    Ver mensaje del correo electrónico del intercambio
</a>
<br/>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/pdf/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
            Descargar PDF del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-code-o" style="font-size:24px"></span>
            Descargar XML del intercambio
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/resultados_xml/<?=$DteIntercambio->codigo?>" role="button">
            <span class="fa fa-file-code-o" style="font-size:24px"></span>
            Descargar XML de resultados
        </a>
    </div>
</div>

</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO DOCUMENTOS -->
<div role="tabpanel" class="tab-pane" id="documentos">
<p>Aquí podrá generar y enviar la respuesta para los documentos que <?=$DteIntercambio->getEmisor()->razon_social?> envió a <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_intercambios/responder/'.$DteIntercambio->codigo, 'onsubmit'=>'Form.check() && Form.checkSend()']);
echo $f->input([
    'name' => 'NmbContacto',
    'label' => 'Contacto',
    'value' => $_Auth->User->nombre,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'MailContacto',
    'label' => 'Email contacto',
    'value' => $_Auth->User->email,
    'check' => 'notempty email',
]);
echo $f->input([
    'name' => 'Recinto',
    'label' => 'Recinto',
    'value' => $Emisor->direccion.', '.$Emisor->getComuna()->comuna,
    'check' => 'notempty',
    'help' => 'Lugar donde se recibieron los productos o prestaron los servicios',
]);
echo $f->input([
    'name' => 'responder_a',
    'label' => 'Responder a',
    'value' => $DteIntercambio->de,
    'check' => 'notempty email',
]);
$estado = $EnvioDte->getEstadoValidacion(['RutReceptor'=>$Emisor->rut.'-'.$Emisor->dv]);
echo $f->input([
    'type' => 'select',
    'name' => 'EstadoRecepEnv',
    'label' => 'Estado envío',
    'options' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'],
    'value' => $estado,
    'check' => 'notempty',
    'attr' => 'onchange="document.getElementById(\'RecepEnvGlosaField\').value=this.selectedOptions[0].textContent"'
]);
echo $f->input([
    'name' => 'RecepEnvGlosa',
    'label' => 'Glosa estado envío',
    'value' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$estado],
    'check' => 'notempty',
]);

// Recepción de envío
$RecepcionDTE = [];
foreach ($Documentos as $Dte) {
    $estado_sii = !isset($DteIntercambio->estado) ? $Dte->getEstado($Firma) : ['GLOSA'=>''];
    $estado = $Dte->getEstadoValidacion([
        'RUTEmisor' => $DteIntercambio->getEmisor()->rut.'-'.$DteIntercambio->getEmisor()->dv,
        'RUTRecep'=>$Emisor->rut.'-'.$Emisor->dv
    ]);
    $RecepcionDTE[] = [
        'TipoDTE' => $Dte->getTipo(),
        'Folio' => $Dte->getFolio(),
        'FchEmis' => $Dte->getFechaEmision(),
        'RUTEmisor' => $Dte->getEmisor(),
        'RUTRecep' => $Dte->getReceptor(),
        'MntTotal' => $Dte->getMontoTotal(),
        'estado_sii' => isset($estado_sii['GLOSA']) ? $estado_sii['GLOSA'] : (isset($estado_sii['GLOSA_ERR']) ? $estado_sii['GLOSA_ERR'] : 'No determinado'),
        'EstadoRecepDTE' => $estado,
        'RecepDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['documento'][$estado],
        'acuse' => (int)(bool)!$estado,
    ];
}
echo $f->input([
    'type' => 'js',
    'id' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['DTE', 'Folio', 'Total', 'Estado SII', 'Estado', 'Glosa', 'Acuse'],
    'inputs' => [
        ['name'=>'TipoDTE', 'attr'=>'readonly="readonly" size="3"'],
        ['name'=>'Folio', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'FchEmis', 'type'=>'hidden'],
        ['name'=>'RUTEmisor', 'type'=>'hidden'],
        ['name'=>'RUTRecep', 'type'=>'hidden'],
        ['name'=>'MntTotal', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'estado_sii'],
        ['name'=>'EstadoRecepDTE', 'type'=>'select', 'options'=>\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['documento'], 'attr'=>'style="width:12em" onchange="this.parentNode.parentNode.parentNode.childNodes[8].firstChild.firstChild.value=this.selectedOptions[0].textContent"'],
        ['name'=>'RecepDTEGlosa'],
        ['name'=>'acuse', 'type'=>'select', 'options'=>[1=>'Si', 0=>'No'], 'attr'=>'style="width:5em"'],
    ],
    'values' => $RecepcionDTE,
    'help' => 'Si el estado es diferente a "DTE Recibido OK" entonces el DTE será rechazado (no hay aceptado con reparos). Aquellos DTE con acuse de recibo serán agregados a los DTEs recibidos de '.$Emisor->razon_social
]);

echo $f->end('Generar y enviar respuesta del intercambio');
?>
</div>
<!-- FIN DOCUMENTOS -->

    </div>
</div>
