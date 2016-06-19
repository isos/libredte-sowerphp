<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del controlador
namespace website\Dte\Admin\Mantenedores;

/**
 * Controlador para las acciones de administración de los contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2016-06-18
 */
class Controller_Contribuyentes extends \Controller_Maintainer
{

    protected $namespace = 'website\Dte'; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar'=>['rut', 'razon_social', 'telefono', 'email', 'comuna', 'usuario']
    ]; ///< Columnas que se deben mostrar en las vistas

    /**
     * Acción que permite cargar los datos de contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2016-06-18
     */
    public function importar()
    {
        if (isset($_POST['submit']) and isset($_FILES['archivo']) and !$_FILES['archivo']['error']) {
            $data = \sowerphp\general\Utility_Spreadsheet::read($_FILES['archivo']);
            unset($data[0]);
            $Comunas = new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas();
            $actualizados = 0;
            foreach ($data as $c) {
                if (empty($c[0]))
                    continue;
                $Contribuyente = new \website\Dte\Model_Contribuyente($c[0]);
                if (!$Contribuyente->exists() or $Contribuyente->usuario)
                    continue;
                $actualizado = false;
                if (empty($Contribuyente->email) and !empty($c[1])) {
                    $Contribuyente->email = substr(trim($c[1]), 0, 80);
                    $actualizado = true;
                }
                if (empty($Contribuyente->telefono) and !empty($c[2])) {
                    $Contribuyente->telefono = substr(trim($c[2]), 0, 20);
                    $actualizado = true;
                }
                if (empty($Contribuyente->direccion) and !empty($c[3])) {
                    $Contribuyente->direccion = substr(trim($c[3]), 0, 70);
                    $actualizado = true;
                }
                if (empty($Contribuyente->comuna)) {
                    if (is_numeric($c[4])) {
                        $Contribuyente->comuna = trim($c[4]);
                        $actualizado = true;
                    } else {
                        $comuna = $Comunas->getComunaByName(trim($c[4]));
                        if ($comuna) {
                            $Contribuyente->comuna = $comuna;
                            $actualizado = true;
                        }
                    }
                }
                if ($actualizado) {
                    $Contribuyente->modificado = date('Y-m-d H:i:s');
                    try {
                        if ($Contribuyente->save())
                            $actualizados++;
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    }
                }
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se actualizaron '.num($actualizados).' contribuyentes', 'ok'
                );
            }
        }
    }

}
