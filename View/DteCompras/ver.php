<a href="<?=$_base?>/dte/dte_compras" title="Volver a IEC" class="pull-right"><span class="btn btn-default">Volver a IEC</span></a>

<h1>Libro de compras período <?=$Libro->periodo?></h1>
<p>Esta es la página del libro de compras del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
    }
});
</script>

<?php $n_compras = count($detalle); ?>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab">Datos básicos</a></li>
<?php if ($n_compras) : ?>
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
    ['Período', 'Recibidos', 'Envíados'],
    [$Libro->periodo, num($n_compras), num($Libro->documentos)],
]);
?>
        <div class="row">
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block<?=!$n_compras?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/csv/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-excel-o" style="font-size:24px"></span>
                    Descargar libro de compras en CSV
                </a>
            </div>
            <div class="col-md-6">
                <a class="btn btn-default btn-lg btn-block<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/xml/<?=$Libro->periodo?>" role="button">
                    <span class="fa fa-file-code-o" style="font-size:24px"></span>
                    Descargar libro de compras en XML
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
            <a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
            <span style="font-size:0.8em">
                <a href="<?=$_base?>/dte/dte_compras/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a>
                <br/>
                <a href="<?=$_base?>/dte/dte_compras/enviar_rectificacion/<?=$Libro->periodo?>" title="Enviar rectificación del libro al SII">enviar rectificación</a>
            </span>
        </p>
<?php else: ?>
        <p><a class="btn btn-info" href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.checkSend('¿Confirmar el envio del libro al SII?')">Enviar libro al SII</a></p>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<?php if ($n_compras) : ?>

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
    <img src="<?=$_base.'/dte/dte_compras/grafico_documentos_diarios/'.$Libro->periodo?>" alt="Gráfico compras diarias del período" class="img-responsive thumbnail center" />
    <br/>
    <img src="<?=$_base.'/dte/dte_compras/grafico_tipos/'.$Libro->periodo?>" alt="Gráfico con tipos de compras del período" class="img-responsive thumbnail center" />
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

<!-- INICIO REVISIÓN -->
<div role="tabpanel" class="tab-pane" id="revision">
<p>Aquí puede subir el XML con el resultado de la revisión del libro de compras envíado al SII.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_compras/subir_revision/'.$Libro->periodo, 'onsubmit'=>'Form.check()']);
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
