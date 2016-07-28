<h1>Dte &raquo; Informes &raquo; Propuesta F29</h1>
<p>Aquí podrá generar una propuesta al formulario 29 de acuerdo a sus compras y ventas del mes. Se recomienda que esta propuesta sea revisada por un contador, ya que puede requerir modificaciones.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'placeholder' => date('Ym'),
    'check' => 'notempty integer',
    'help' => 'Período en formato AAAAMM',
]);
echo $f->end('Descargar propuesta');
