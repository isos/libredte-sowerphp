<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_ventas" title="Volver a IEV">
            Volver a IEV
        </a>
    </li>
</ul>

<h1>Libro de ventas período <?=$Libro->periodo?></h1>
<p>Esta es la página del libro de ventas del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<?php $n_ventas = count($detalle); ?>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
        <li role="presentation"><a href="#resumen" aria-controls="resumen" role="tab" data-toggle="tab">Resumen</a></li>
<?php if ($n_ventas) : ?>
        <li role="presentation"><a href="#detalle" aria-controls="detalle" role="tab" data-toggle="tab">Detalle</a></li>
        <li role="presentation"><a href="#estadisticas" aria-controls="estadisticas" role="tab" data-toggle="tab">Estadísticas</a></li>
<?php endif; ?>
        <li role="presentation"><a href="#revision" aria-controls="revision" role="tab" data-toggle="tab">Subir revisión</a></li>
    </ul>
    <div class="tab-content">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos">
    <div class="row">
        <div class="col-md-9">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Período', 'Emitidos', 'Envíados'],
    [$Libro->periodo, num($n_ventas), num($Libro->documentos)],
]);
?>
        <div class="row">
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$n_ventas?' disabled':''?>" href="<?=$_base?>/dte/dte_ventas/csv/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-excel-o" style="font-size:24px"></span>
                    Descargar CSV
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_ventas/pdf/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-pdf-o" style="font-size:24px"></span>
                    Descargar PDF
                </a>
            </div>
            <div class="col-md-4">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_ventas/xml/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-code-o" style="font-size:24px"></span>
                    Descargar XML
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3 center bg-info">
        <span class="lead">Track ID SII: <?=$Libro->track_id?></span>
        <p><strong><?=$Libro->revision_estado?></strong></p>
        <p><?=str_replace("\n", '<br/>', $Libro->revision_detalle)?></p>
<?php if ($Libro->track_id and $Libro->getEstado()!='LRH') : ?>
        <p>
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_ventas/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_ventas/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a>
                <br/>
                <a href="<?=$_base?>/dte/dte_ventas/enviar_rectificacion/<?=$Libro->periodo?>" title="Enviar rectificación del libro al SII">enviar rectificación</a>
            </span>
        </p>
<?php else: ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_ventas/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.checkSend('¿Confirmar el envio del libro al SII?')">Enviar libro al SII</a></p>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO RESUMEN -->
<div role="tabpanel" class="tab-pane" id="resumen">
    <div class="panel panel-default">
        <div class="panel-heading">
            Documentos con detalle registrado
        </div>
        <div class="panel-body">
<?php
foreach ($resumen as &$r) {
    foreach ($r as &$v) {
        if ($v)
            $v = num($v);
    }
}
$titulos = ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo'];
array_unshift($resumen, $titulos);
new \sowerphp\general\View_Helper_Table($resumen);
?>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            Enviar libro agregando resúmenes manuales
        </div>
        <div class="panel-body">
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin([
    'id'=>'enviar_sii',
    'action'=>$_base.'/dte/dte_ventas/enviar_sii/'.$Libro->periodo,
    'onsubmit'=>'Form.check(\'enviar_sii\') && Form.checkSend()'
]);
echo $f->input([
    'type' => 'js',
    'id' => 'resumenes',
    'titles' => $titulos,
    'inputs' => [
        ['type'=>'select', 'name'=>'TpoDoc', 'options'=>[35=>'Boleta', 38=>'Boleta exenta', 48=>'Pago electrónico'], 'attr'=>'style="width:10em"'],
        ['name'=>'TotDoc', 'check'=>'notempty integer'],
        ['name'=>'TotAnulado', 'check'=>'integer'],
        ['name'=>'TotOpExe', 'check'=>'integer'],
        ['name'=>'TotMntExe', 'check'=>'integer'],
        ['name'=>'TotMntNeto', 'check'=>'integer'],
        ['name'=>'TotMntIVA', 'check'=>'integer'],
        ['name'=>'TotIVAPropio', 'check'=>'integer'],
        ['name'=>'TotIVATerceros', 'check'=>'integer'],
        ['name'=>'TotLey18211', 'check'=>'integer'],
        ['name'=>'TotMntTotal', 'check'=>'notempty integer'],
        ['name'=>'TotMntNoFact', 'check'=>'integer'],
        ['name'=>'TotMntPeriodo', 'check'=>'integer'],
    ],
]);
$f->setStyle('horizontal');
echo $f->input([
    'name' => 'CodAutRec',
    'label'=>'Autorización rectificación',
    'help' => 'Código de autorización de rectificación obtenido desde el SII (sólo si es rectificación)',
]);
?>
            <div class="row">
                <div class="form-group col-md-offset-3 col-md-6">
                    <button type="submit" name="submit" class="btn btn-info" style="width:100%">
                        Enviar libro al SII incorporando los resúmenes manuales
                    </button>
                </div>
            </div>
<?php
echo $f->end(false);
?>
        </div>
    </div>
</div>
<!-- FIN RESUMEN -->

<?php if ($n_ventas) : ?>

<!-- INICIO DETALLES -->
<div role="tabpanel" class="tab-pane" id="detalle">
<?php
array_unshift($detalle, $libro_cols);
new \sowerphp\general\View_Helper_Table($detalle);
?>
</div>
<!-- FIN DETALLES -->

<!-- INICIO ESTADÍSTICAS -->
<div role="tabpanel" class="tab-pane" id="estadisticas">
    <img src="<?=$_base.'/dte/dte_ventas/grafico_documentos_diarios/'.$Libro->periodo?>" alt="Gráfico ventas diarias del período" class="img-responsive thumbnail center" />
    <br/>
    <img src="<?=$_base.'/dte/dte_ventas/grafico_tipos/'.$Libro->periodo?>" alt="Gráfico con tipos de ventas del período" class="img-responsive thumbnail center" />
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

<!-- INICIO REVISIÓN -->
<div role="tabpanel" class="tab-pane" id="revision">
<p>Aquí puede subir el XML con el resultado de la revisión del libro de ventas envíado al SII.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_ventas/subir_revision/'.$Libro->periodo, 'onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML revisión',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Subir XML de revisión');
?>
</div>
<!-- FIN REVISIÓN -->

    </div>
</div>
