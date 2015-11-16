<h1>Usuario empresa <?=$Contribuyente->razon_social?></h1>
<p>Aquí podrá modificar los usuarios autorizados a operar con la empresa <?=$Contribuyente->razon_social?> RUT <?=num($Contribuyente->rut).'-'.$Contribuyente->dv?>, para la cual usted es el usuario administrador.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.checkSend()']);
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
