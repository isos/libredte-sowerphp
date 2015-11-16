<h1>Modificar mantenedor de folios</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'siguiente',
    'label' => 'Siguiente folio',
    'value' => $DteFolio->siguiente,
    'help' => 'Número de folio que es el siguiente que se debe asignar al documento que se emita',
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'value' => $DteFolio->alerta,
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se notificará al administrador de la empresa',
    'check' => 'notempty integer',
]);
echo $f->end('Modificar mantenedor de folios');
