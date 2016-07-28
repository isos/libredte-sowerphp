<h1>Dte &raquo; Admin &raquo; Informes &raquo; Planes plus</h1>
<p>Aquí podrá buscar los usuarios pertenecientes al grupo <em>dte_plus</em> y los contribuyentes que tengan registrados.</pi>
<?php
foreach ($plus as &$p) {
    $p['rut'] = \sowerphp\app\Utility_Rut::addDV($p['rut']);
    $p['en_certificacion'] = $p['en_certificacion'] ? 'Certificación' : 'Producción';
}
array_unshift($plus, ['Usuario', 'Nombre', 'Email', 'Último ingreso', 'RUT', 'Razón social', 'Correo', 'Teléfono', 'Ambiente']);
new \sowerphp\general\View_Helper_Table($plus, 'usuarios_plus', true);
