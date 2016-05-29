<ul class="nav nav-pills pull-right">
    <li>
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Volver a los documentos emitidos">
            Volver a documentos emitidos
        </a>
    </li>
</ul>
<h1>Cargar XML como DTE emitido</h1>
<p>Si ha emitido un DTE en una aplicación externa aquí podrá cargarlo para que sea agregado a sus otros documentos generados en el sistema.</p>
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
