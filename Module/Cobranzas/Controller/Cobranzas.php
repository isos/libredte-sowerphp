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
namespace website\Dte\Cobranzas;

/**
 * Clase para el controlador asociado a la tabla cobranza de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla cobranza
 * @author SowerPHP Code Generator
 * @version 2016-02-28 18:10:55
 */
class Controller_Cobranzas extends \Controller_App
{

    /**
     * Acción que permite buscar los pagos pendientes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-17
     */
    public function buscar()
    {
        if (isset($_POST['submit'])) {
            $Emisor = $this->getContribuyente();
            $this->set([
                'cobranza' => (new Model_Cobranzas())->getPendientes(
                    $Emisor->rut,
                    $Emisor->config_ambiente_en_certificacion,
                    $_POST['desde'],
                    $_POST['hasta'],
                    $_POST['receptor']
                ),
            ]);
        }
    }

    /**
     * Acción que permite editar los pagos para marcarlos como pagados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function ver($dte, $folio, $fecha)
    {
        $Emisor = $this->getContribuyente();
        $Pago = new Model_Cobranza($Emisor->rut, $dte, $folio, $Emisor->config_ambiente_en_certificacion, $fecha);
        if (!$Pago->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Pago programado solicitado no existe', 'error'
            );
            $this->redirect('/dte/cobranzas/buscar');
        }
        $this->set([
            '_header_extra' => ['js'=>['/dte/cobranzas/js/cobranzas.js']],
            'Emisor' => $Emisor,
            'Pago' => $Pago
        ]);
        if (isset($_POST['submit'])) {
            $Pago->pagado = $_POST['pagado'];
            $Pago->observacion = $_POST['observacion'];
            $Pago->usuario = $this->Auth->User->id;
            $Pago->modificado = $_POST['modificado'];
            if ($Pago->save()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Pago registrado exitosamente', 'ok'
                );
                $this->redirect('/dte/dte_emitidos/ver/'.$Pago->dte.'/'.$Pago->folio.'#cobranza');
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible guardar el pago', 'error'
                );
            }
        }
    }
}
