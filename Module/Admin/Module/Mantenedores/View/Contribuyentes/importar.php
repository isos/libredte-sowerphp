<h1>Dte &raquo; Admin &raquo; Mantenedores &raquo; Importar contribuyentes</h1>
<p>Aquí se podrán importar datos de contribuyentes. El orden de las columnas es: RUT, email, teléfono dirección y comuna. La razón social, la actividad económica y el giro se determinan a partir de lo entregado por el SII. Sólo se actualizarán contribuyentes no registrados por un usuario y que no tengan el campo previamente asignado.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo',
    'check' => 'notempty',
]);
echo $f->end('Importar datos de contribuyentes');
