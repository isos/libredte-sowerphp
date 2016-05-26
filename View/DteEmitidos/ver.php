<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Volver a los documentos emitidos">
            Volver a documentos emitidos
        </a>
    </li>
</ul>

<h1>Documento T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?></h1>
<p>Esta es la página del documento <?=$DteEmitido->getTipo()->tipo?> (<?=$DteEmitido->dte?>) folio número <?=$DteEmitido->folio?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>).</p>

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
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Enviar por email</a></li>
        <li role="presentation"><a href="#intercambio" aria-controls="intercambio" role="tab" data-toggle="tab">Resultado intercambio</a></li>
        <li role="presentation"><a href="#referencias" aria-controls="referencias" role="tab" data-toggle="tab">Referencias</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-<?=$enviar_sii?9:12?>">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Fecha', 'Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getTipo()->tipo, $DteEmitido->folio, \sowerphp\general\Utility_Date::format($DteEmitido->fecha), $Receptor->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>/<?=$Emisor->config_pdf_dte_cedible?>" role="button">
                        <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                        Descargar documento en PDF
                    </a>
                </div>
                <div class="col-md-6">
                    <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                        <span class="fa fa-file-code-o" style="font-size:24px"></span>
                        Descargar documento en XML
                    </a>
                </div>
            </div>
        </div>
<?php if ($enviar_sii) : ?>
        <div class="col-md-3 center bg-info">
            <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span>
            <p><strong><?=$DteEmitido->revision_estado?></strong></p>
            <p><?=$DteEmitido->revision_detalle?></p>
<?php if ($DteEmitido->track_id) : ?>
            <p>
                <a class="btn btn-info" href="<?=$_base?>/dte/dte_emitidos/actualizar_estado/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Actualizar estado</a><br/>
                <span style="font-size:0.8em">
                    <a href="<?=$_base?>/dte/dte_emitidos/solicitar_revision/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Solicitar nueva revisión del documento al SII">solicitar nueva revisión</a>
<?php if ($DteEmitido->getEstado()=='R') : ?>
                    <br/>
                    <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.checkSend('¿Confirmar la eliminación del DTE?')">eliminar documento</a>
<?php endif; ?>
                </span>
            </p>
<?php else: ?>
            <p>
                <a class="btn btn-info" href="<?=$_base?>/dte/dte_emitidos/enviar_sii/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Enviar documento al SII</a>
                <br/>
                <span style="font-size:0.8em">
                    <a href="<?=$_base?>/dte/dte_emitidos/eliminar/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Eliminar documento" onclick="return Form.checkSend('¿Confirmar la eliminación del DTE?')">eliminar documento</a>
                </span>
            </p>
<?php endif; ?>
        </div>
<?php endif; ?>
    </div>
</div>
<!-- INICIO DATOS BÁSICOS -->

<!-- INICIO ENVIAR POR EMAIL -->
<div role="tabpanel" class="tab-pane" id="email">
<?php
if ($emails) {
    $asunto = 'EnvioDTE: '.num($Emisor->rut).'-'.$Emisor->dv.' - '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio;
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio.' del día '.\sowerphp\general\Utility_Date::format($DteEmitido->fecha).' por un monto total de $'.num($DteEmitido->total).'.-'."\n\n";
    $mensaje .= 'Saluda atentamente,'."\n\n";
    $mensaje .= '-- '."\n".$Emisor->razon_social."\n";
    $mensaje .= $Emisor->giro."\n";
    $contacto = [];
    if (!empty($Emisor->telefono))
        $contacto[] = $Emisor->telefono;
    if (!empty($Emisor->email))
        $contacto[] = $Emisor->email;
    if ($Emisor->config_extra_web)
        $contacto[] = $Emisor->config_extra_web;
    if ($contacto)
        $mensaje .= implode(' - ', $contacto)."\n";
    $mensaje .= $Emisor->direccion.', '.$Emisor->getComuna()->comuna."\n";
    $table = [];
    $checked = [];
    foreach ($emails as $k => $e) {
        $table[] = [$e, $k];
        if ($k=='Email intercambio')
            $checked = [$e];
    }
    $f = new \sowerphp\general\View_Helper_Form();
    echo $f->begin(['action'=>$_base.'/dte/dte_emitidos/enviar_email/'.$DteEmitido->dte.'/'.$DteEmitido->folio]);
    echo $f->input([
        'type' => 'tablecheck',
        'name' => 'emails',
        'label' => 'Para',
        'titles' => ['Email', 'Origen'],
        'table' => $table,
        'checked' => $checked,
        'help' => 'Seleccionar emails a los que se enviará el documento',
    ]);
    echo $f->input(['name'=>'asunto', 'label'=>'Asunto', 'value'=>$asunto, 'check'=>'notempty']);
    echo $f->input(['type'=>'textarea', 'name'=>'mensaje', 'label'=>'Mensaje', 'value'=>$mensaje, 'rows'=>10, 'check'=>'notempty']);
    echo $f->input(['type'=>'checkbox', 'name'=>'cedible', 'label'=>'¿Copia cedible?', 'checked'=>$Emisor->config_pdf_dte_cedible]);
    echo $f->end('Enviar PDF y XML por email');
} else {
    echo '<p>No hay emails registrados para el receptor ni el documento.</p>',"\n";
}
?>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<!-- INICIO INTERCAMBIO -->
<div role="tabpanel" class="tab-pane" id="intercambio">
<?php
// recibo
echo '<h2>Recibo</h2>',"\n";
$Recibo = $DteEmitido->getIntercambioRecibo();
if ($Recibo) {
    $Sobre = $Recibo->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Recinto', 'Firma', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Recibo->recinto,
            $Recibo->firma,
            $Recibo->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_recibos/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe recibo para el documento.</p>';
}
// recepcion
echo '<h2>Recepción</h2>',"\n";
$Recepcion = $DteEmitido->getIntercambioRecepcion();
if ($Recepcion) {
    $Sobre = $Recepcion->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Estado general', 'Estado documento', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Sobre->estado.': '.$Sobre->glosa,
            $Recepcion->estado.': '.$Recepcion->glosa,
            $Sobre->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_recepciones/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe recepción para el documento.</p>';
}
// resultado
echo '<h2>Resultado</h2>',"\n";
$Resultado = $DteEmitido->getIntercambioResultado();
if ($Resultado) {
    $Sobre = $Resultado->getSobre();
    new \sowerphp\general\View_Helper_Table([
        ['Contacto', 'Teléfono', 'Email', 'Estado', 'Fecha y hora', 'XML'],
        [
            $Sobre->contacto,
            $Sobre->telefono,
            $Sobre->email,
            $Resultado->estado.': '.$Resultado->glosa,
            $Sobre->fecha_hora,
            '<a href="'.$_base.'/dte/dte_intercambio_resultados/xml/'.$Sobre->responde.'/'.$Sobre->codigo.'" role="button"><span class="fa fa-file-code-o btn btn-default"></span></a>',
        ],
    ]);
} else {
    echo '<p>No existe resultado para el documento.</p>';
}
?>
</div>
<!-- FIN INTERCAMBIO -->

<!-- INICIO REFERENCIAS -->
<div role="tabpanel" class="tab-pane" id="referencias">
<?php
if ($referencias) {
    echo '<p>Los siguientes son documentos que hacen referencia a este.</p>',"\n";
    foreach ($referencias as &$r) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$r['dte'].'/'.$r['folio'].'" title="Ver documento"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$r['dte'].'/'.$r['folio'].'" title="Descargar PDF del documento"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
        $r[] = $acciones;
        unset($r['dte']);
    }
    array_unshift($referencias, ['Documento', 'Folio', 'Fecha', 'Referencia', 'Razón', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($referencias);
} else {
    echo '<p>No hay documentos que referencien a este.</p>',"\n";
}
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/documentos/emitir/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
    Crear referencia a este documento
</a>
</div>
<!-- FIN REFERENCIAS -->

    </div>
</div>
