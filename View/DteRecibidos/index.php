<h1>DTE recibidos</h1>
<p>Aquí podrá consultar todos los documentos recibidos por la empresa <?=$Emisor->razon_social?>.</p>

<div class="text-right">
    <a href="dte_recibidos/agregar" class="btn btn-default">
        <span class="fa fa-plus"></span>
        Agregar DTE recibido
    </a>
    <br/><br/>
</div>

<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="dte_recibidos/modificar/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Modificar DTE"><span class="fa fa-edit btn btn-default"></span></a>';
    if ($d['intercambio'])
        $acciones .= ' <a href="dte_intercambios/pdf/'.$d['intercambio'].'" title="Descargar PDF del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['emisor'], $d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Emisor', 'Fecha', 'Total', 'Intercambio', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 100]);
echo $t->generate($documentos);
