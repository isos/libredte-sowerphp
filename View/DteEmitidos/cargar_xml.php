<a href="<?=$_base?>/dte/dte_emitidos/listar" title="Volver a los documentos emitidos" class="pull-right"><span class="btn btn-default">Volver a documentos emitidos</span></a>
<h1>Cargar XML como DTE emitido</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de cargar el XML seleccionado?\')']);
echo $f->input([
    'type'=> 'file',
    'name' => 'xml',
    'label'=> 'Archivo XML',
    'help' => 'Archivo XML del DTE emitido por la empresa que se desea cargar al sistema',
    'check'=>'notempty'
]);
echo $f->end('Cargar XML');
