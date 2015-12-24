<h1>DTE emitidos</h1>
<p>Aquí podrá consultar todos los documentos emitidos por la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver DTE"><span class="fa fa-search btn btn-default"></span></a>';
    $acciones .= ' <a href="dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del DTE"><span class="fa fa-file-pdf-o btn btn-default"></span></a>';
    $d[] = $acciones;
    $d['total'] = num($d['total']);
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, null, 100]);
echo $t->generate($documentos);
