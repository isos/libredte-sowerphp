<a href="<?=$_base?>/dte/dte_ventas" title="Volver a IEV" class="pull-right"><span class="btn btn-default">Volver a IEV</span></a>

<h1>Enviar libro de ventas (IEV) sin movimientos</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de enviar el libro sin movimientos?\')']);
echo $f->input(['name'=>'periodo', 'label'=>'Período', 'placeholder'=>date('Ym'), 'help'=>'Período en formato AAAAMM, ejemplo: '.date('Ym'), 'check'=>'notempty integer']);
echo $f->end('Enviar libro sin movimientos');
