<a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Volver a la IEV del período <?=$periodo?>" class="pull-right"><span class="btn btn-default">Volver a la IEV <?=$periodo?></span></a>

<h1>Rectificación IEV para el período <?=$periodo?></h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_ventas/enviar_sii/'.$periodo,
    'onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de enviar la rectificación del libro?\')'
]);
echo $f->input([
    'name' => 'CodAutRec',
    'label'=>'Autorización rectificación',
    'help' => 'Código de autorización de rectificación obtenido desde el SII',
    'check'=>'notempty',
]);
echo $f->end('Enviar rectificación');