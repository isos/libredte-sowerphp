<?php

/**
 * SowerPHP: Minimalist Framework for PHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

// namespace del controlador
namespace website\Dte\Admin;

/**
 * Clase para el controlador asociado a la tabla dte_folio de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla dte_folio
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Controller_DteFolios extends \Controller_App
{

    /**
     * Acción que muestra la página principal para mantener los folios de la
     * empresa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function index()
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $this->set([
            'Emisor' => $Emisor,
            'folios' => $Emisor->getFolios(),
        ]);
    }

    /**
     * Acción que agrega mantenedor para un nuevo tipo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function agregar()
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $this->set([
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
        ]);
        // procesar creación del mantenedor
        if (isset($_POST['submit'])) {
            // verificar que esté autorizado a cargar folios del tipo de dte
            if (!$Emisor->documentoAutorizado($_POST['dte'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $Emisor->razon_social.' no está autorizada a emitir documentos de tipo '.$_POST['dte'], 'error'
                );
                return;
            }
            // crear mantenedor del folio
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], (int)$Emisor->certificacion);
            if (!$DteFolio->exists()) {
                $DteFolio->siguiente = 0;
                $DteFolio->disponibles = 0;
                $DteFolio->alerta = $_POST['alerta'];
                try {
                    $DteFolio->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible crear el mantenedor del folio: '.$e->getMessage(), 'error'
                    );
                    return;
                }
            }
            // si todo fue bien se redirecciona a la página de carga de CAF
            \sowerphp\core\Model_Datasource_Session::message(
                'Ahora debe subir un archivo CAF para el tipo de documento '.$_POST['dte']
            );
            $this->redirect('/dte/admin/dte_folios/subir_caf');
        }
    }

    /**
     * Acción que permite subir un caf para un tipo de folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function subir_caf()
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $this->set([
            'Emisor' => $Emisor,
        ]);
        // procesar solo si se envió el formulario
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir CAF
            if (!isset($_FILES['caf']) or $_FILES['caf']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir el CAF', 'error'
                );
                return;
            }
            if (\sowerphp\general\Utility_File::mimetype($_FILES['caf']['tmp_name'])!='application/xml') {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Formato del archivo '.$_FILES['caf']['name'].' es incorrecto', 'error'
                );
                return;
            }
            // cargar caf
            $caf = file_get_contents($_FILES['caf']['tmp_name']);
            $Folios = new \sasco\LibreDTE\Sii\Folios($caf);
            // si no se pudo validar el caf error
            if (!$Folios->getTipo()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible cargar el CAF '.$_FILES['caf']['name'].':<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                return;
            }
            // verificar que el caf tenga previamente cargado un mantenedor de folio
            $DteFolio = new Model_DteFolio($Emisor->rut, $Folios->getTipo(), (int)$Folios->getCertificacion());
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Primero debe crear el mantenedor de los folios de tipo '.$Folios->getTipo(), 'error'
                );
                return;
            }
            // verificar que el caf sea del emisor
            if ($Folios->getEmisor()!=$Emisor->rut.'-'.$Emisor->dv) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'RUT del CAF '.$Folios->getEmisor().' no corresponde con el RUT de la empresa '.$Emisor->razon_social.' '.$Emisor->rut.'-'.$Emisor->dv, 'error'
                );
                return;
            }
            // verificar que el folio que se está subiendo sea para el ambiente actual de la empresa
            $ambiente_empresa = $Emisor->certificacion ? 'certificación' : 'producción';
            $ambiente_caf = $Folios->getCertificacion() ? 'certificación' : 'producción';
            if ($ambiente_empresa!=$ambiente_caf) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Empresa está en ambiente de '.$ambiente_empresa.' pero folios son de '.$ambiente_caf, 'error'
                );
                return;
            }
            // crear caf para el folio
            $DteCaf = new Model_DteCaf($DteFolio->emisor, $DteFolio->dte, (int)$Folios->getCertificacion(), $Folios->getDesde());
            if ($DteCaf->exists()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF para el documento de tipo '.$DteCaf->dte.' que inicia en '.$Folios->getDesde().' en ambiente de '.$ambiente_caf.' ya estaba cargado', 'warning'
                );
                return;
            }
            $DteCaf->hasta = $Folios->getHasta();
            $DteCaf->xml = \website\Dte\Utility_Data::encrypt($caf);
            try {
                $DteCaf->save();
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible guardar el CAF: '.$e->getMessage(), 'error'
                );
                return;
            }
            // actualizar mantenedor de folios
            if (!$DteFolio->disponibles) {
                $DteFolio->siguiente = $Folios->getDesde();
                $DteFolio->disponibles = $Folios->getHasta() - $Folios->getDesde() + 1;
            } else {
                $DteFolio->disponibles += $Folios->getHasta() - $Folios->getDesde() + 1;
            }
            $DteFolio->alertado = 'f';
            try {
                $DteFolio->save();
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF para el documento de tipo '.$DteCaf->dte.' que inicia en '.$Folios->getDesde().' en ambiente de '.$ambiente_caf.' fue cargado, el siguiente folio disponible es '.$DteFolio->siguiente, 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF se guardó, pero no fue posible actualizar el mantenedor de folios, deberá actualizar manualmente. '.$e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que permite modificar un mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-29
     */
    public function modificar($dte)
    {
        $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
        $DteFolio = new Model_DteFolio($Emisor->rut, $dte, (int)$Emisor->certificacion);
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el mantenedor de folios solicitado', 'error'
            );
            $this->redirect('/dte/admin/dte_folios');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteFolio' => $DteFolio,
        ]);
        if (isset($_POST['submit'])) {
            // validar que campos existan y asignar
            foreach (['siguiente', 'alerta'] as $attr) {
                if (empty($_POST[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar el campo: '.$attr, 'error'
                    );
                    return;
                }
                $DteFolio->$attr = $_POST[$attr];
            }
            // guardar y redireccionar
            try {
                $DteFolio->calcularDisponibles();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Mantenedor de folios para tipo '.$DteFolio->dte.' actualizado', 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible actualizar el mantenedor de folios: '.$e->getMessage(), 'error'
                );
                return;
            }
        }
    }

}
