<a href="<?=$_base?>/dte/dte_compras" title="Volver a IEC" class="pull-right"><span class="btn btn-default">Volver a IEC</span></a>
<h1>Importar libro de compras (IEC) desde archivo CSV</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend(\'¿Está seguro de importar el libro seleccionado?\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Libro CSV',
    'help' => 'Libro de compras en formato CSV. Puede consultar un <a href="https://raw.githubusercontent.com/LibreDTE/libredte-lib/master/examples/libros/libro_compras.csv">ejemplo del libro</a> o bien revisar la <a href="http://wiki.libredte.cl/doku.php/faq/libredte/general/libro_compras_ventas#detalle_libro_compra">documentación de las columnas</a>.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->end('Importar libro de compras');
