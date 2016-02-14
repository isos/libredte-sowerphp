<h1>Enviar reporte de consumo de folios</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'dia',
    'label' => 'Día',
    'value' => $dia,
    'check' => 'notempty date',
]);
echo $f->end('Enviar reporte de consumo de folios');
