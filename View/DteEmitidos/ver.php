<h1>DTE T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?></h1>
<p>Esta es la página del DTE <?=$DteEmitido->getTipo()->tipo?> (<?=$DteEmitido->dte?>) folio número <?=$DteEmitido->folio?> de la empresa <?=$Emisor->razon_social?>.</p>

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
        <li role="presentation"><a href="#email" aria-controls="email" role="tab" data-toggle="tab">Enviar DTE por email</a></li>
        <li role="presentation"><a href="#referencias" aria-controls="referencias" role="tab" data-toggle="tab">Referencias</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-9">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Folio', 'Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    [$DteEmitido->getTipo()->tipo, $DteEmitido->folio, $Receptor->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
]);
?>
        <div class="row">
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                    <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                    Descargar PDF del DTE
                </a>
            </div>
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">
                    <span class="fa fa-file-code-o" style="font-size:24px"></span>
                    Descargar XML del DTE
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 center bg-info">
        <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span>
        <p><strong><?=$DteEmitido->revision_estado?></strong></p>
        <p><?=$DteEmitido->revision_detalle?></p>
<?php if ($DteEmitido->track_id) : ?>
        <p>
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_emitidos/actualizar_estado/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em"><a href="<?=$_base?>/dte/dte_emitidos/solicitar_revision/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" title="Solicitar nueva revisión del DTE al SII">solicitar nueva revisión</a></span>
        </p>
<?php else: ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_emitidos/enviar_sii/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>" role="button">Enviar DTE al SII</a></p>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- INICIO DATOS BÁSICOS -->

<!-- INICIO ENVIAR POR EMAIL -->
<div role="tabpanel" class="tab-pane" id="email">
<?php
if ($emails) {
    $asunto = 'EnvioDTE: '.num($Emisor->rut).'-'.$Emisor->dv.' - '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio;
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta '.$DteEmitido->getTipo()->tipo.' N° '.$DteEmitido->folio.' del día '.$DteEmitido->fecha.' por un monto total de $'.num($DteEmitido->total).'.-'."\n\n";
    $mensaje .= 'Saluda atentamente,'."\n\n";
    $mensaje .= '-- '."\n".$Emisor->razon_social."\n";
    $mensaje .= $Emisor->giro."\n";
    $contacto = [];
    foreach (['telefono', 'email', 'web'] as $c) {
        if (!empty($Emisor->$c))
            $contacto[] = $Emisor->$c;
    }
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
        'help' => 'Seleccionar emails a los que se enviará el DTE',
    ]);
    echo $f->input(['name'=>'asunto', 'label'=>'Asunto', 'value'=>$asunto, 'check'=>'notempty']);
    echo $f->input(['type'=>'textarea', 'name'=>'mensaje', 'label'=>'Mensaje', 'value'=>$mensaje, 'rows'=>10, 'check'=>'notempty']);
    echo $f->input(['type'=>'checkbox', 'name'=>'cedible', 'label'=>'¿Copia cedible?']);
    echo $f->end('Enviar PDF y XML por email');
} else {
    echo '<p>No hay emails registrados para el receptor ni el DTE.</p>',"\n";
}
?>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<!-- INICIO REFERENCIAS -->
<div role="tabpanel" class="tab-pane" id="referencias">
<?php
if ($referencias) {
    echo '<p>Los siguientes son documentos que hacen referencia a este.</p>',"\n";
    foreach ($referencias as &$r) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$r['dte'].'/'.$r['folio'].'" title="Ver DTE"><span class="fa fa-search btn btn-default"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$r['dte'].'/'.$r['folio'].'" title="Descargar PDF del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
        $r[] = $acciones;
        unset($r['dte']);
    }
    array_unshift($referencias, ['Documento', 'Folio', 'Fecha', 'Referencia', 'Razón', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($referencias);
} else {
    echo '<p>No hay documentos que referencien a este.</p>',"\n";
}
?>
</div>
<!-- FIN REFERENCIAS -->

    </div>
</div>
