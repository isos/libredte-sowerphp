<h1>Usuarios empresa <?=$Contribuyente->razon_social?></h1>
<p>Aquí podrá modificar los usuarios autorizados a operar con la empresa <?=$Contribuyente->razon_social?> RUT <?=num($Contribuyente->rut).'-'.$Contribuyente->dv?>, para la cual usted es el usuario administrador.</p>
<?php

// mantenedor usuarios
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'id' => 'usuarios',
    'onsubmit' => 'Form.check(\'usuarios\') && Form.checkSend()',
]);
echo $f->input([
    'type' => 'js',
    'id' => 'usuarios',
    'label' => 'Usuarios autorizados',
    'titles' => ['Usuario', 'Permiso'],
    'inputs' => [
        ['name'=>'usuario'],
        ['type'=>'select', 'name'=>'permiso', 'options'=>$permisos_usuarios]
    ],
    'values' => $Contribuyente->getUsuarios(),
    'help' => 'Debe ingresar el nombre del usuario que desea autorizar y el permiso. Si quiere asignar varios permisos a un usuario deberá agregarlo varias veces.',
]);
echo $f->end('Modificar usuarios autorizados');

// transferir empresa
echo '<hr/>',"\n";
echo $f->begin([
    'action' => '../transferir/'.$Contribuyente->rut,
    'id' => 'transferir',
    'onsubmit' => 'Form.check(\'transferir\') && Form.checkSend(\'¿Está seguro de querer transferir la empresa al nuevo usuario?\')',
]);
echo $f->input([
    'name' => 'usuario',
    'label' => 'Administrador',
    'value' => $Contribuyente->getUsuario()->usuario,
    'check' => 'notempty',
    'help' => 'Usuario que actúa como administrador de la empresa en LibreDTE',
]);
echo $f->end('Cambiar usuario administrador');
