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
namespace website\Dte;

/**
 * Controlador de dte recibidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-27
 */
class Controller_DteRecibidos extends \Controller_App
{

    /**
     * Acción que permite mostrar los documentos recibidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-25
     */
    public function listar($pagina = 1)
    {
        if (!is_numeric($pagina)) {
            $this->redirect('/dte/'.$this->request->params['controller'].'/listar');
        }
        $Emisor = $this->getContribuyente();
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search'])?('?search='.$_GET['search']):'';
        try {
            $documentos_total = $Emisor->countDocumentosRecibidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = ceil($documentos_total/$filtros['limit']);
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            } else $paginas = 1;
            $documentos = $Emisor->getDocumentosRecibidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
            );
            $documentos_total = 0;
            $documentos = [];
        }
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite agregar un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function agregar()
    {
        $Emisor = $this->getContribuyente();
        // asignar variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'tipos_documentos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new \website\Dte\Admin\Mantenedores\Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getList(),
        ]);
        // procesar formulario si se pasó
        if (isset($_POST['submit']))
            $this->save();
    }

    /**
     * Acción que permite editar un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-01
     */
    public function modificar($emisor, $dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener dte recibido
        $DteRecibido = new Model_DteRecibido($emisor, $dte, $folio, (int)$Emisor->config_ambiente_en_certificacion);
        if (!$DteRecibido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE recibido solicitado no existe', 'error'
            );
            $this->redirect('/dte/dte_recibidos/listar');
        }
        // agregar variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteRecibido' => $DteRecibido,
            'tipos_documentos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new \website\Dte\Admin\Mantenedores\Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())->getList(),
        ]);
        // procesar formulario si se pasó
        if (isset($_POST['submit']))
            $this->save();
    }

    /**
     * Método que agrega o modifica un DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-05-26
     */
    private function save()
    {
        $Emisor = $this->getContribuyente();
        // revisar datos minimos
        foreach(['emisor', 'dte', 'folio', 'fecha', 'tasa'] as $attr) {
            if (!isset($_POST[$attr][0])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe indicar '.$attr, 'error'
                );
                return;
            }
        }
        // crear dte recibido
        list($emisor, $dv) = explode('-', str_replace('.', '', $_POST['emisor']));
        $DteRecibido = new Model_DteRecibido($emisor, $_POST['dte'], (int)$_POST['folio'], (int)$Emisor->config_ambiente_en_certificacion);
        $DteRecibido->receptor = $Emisor->rut;
        $DteRecibido->tasa = (int)$_POST['tasa'];
        $DteRecibido->fecha = $_POST['fecha'];
        $DteRecibido->exento = !empty($_POST['exento']) ? $_POST['exento'] : null;
        $DteRecibido->neto = !empty($_POST['neto']) ? $_POST['neto'] : null;
        $DteRecibido->iva = round((int)$DteRecibido->neto * ($DteRecibido->tasa/100));
        $DteRecibido->total = (int)$DteRecibido->exento + (int)$DteRecibido->neto + $DteRecibido->iva;
        $DteRecibido->usuario = $this->Auth->User->id;
        // iva uso común, no recuperable e impuesto adicional
        $DteRecibido->iva_uso_comun = !empty($_POST['iva_uso_comun']) ? $_POST['iva_uso_comun'] : null;
        $DteRecibido->iva_no_recuperable = !empty($_POST['iva_no_recuperable']) ? $_POST['iva_no_recuperable'] : null;
        if (!empty($_POST['impuesto_adicional']) and !empty($_POST['impuesto_adicional_tasa'])) {
            $DteRecibido->impuesto_adicional = $_POST['impuesto_adicional'];
            $DteRecibido->impuesto_adicional_tasa = $_POST['impuesto_adicional_tasa'];
        } else {
            $DteRecibido->impuesto_adicional = null;
            $DteRecibido->impuesto_adicional_tasa = null;
        }
        $DteRecibido->impuesto_tipo = $_POST['impuesto_tipo'];
        $DteRecibido->anulado = isset($_POST['anulado']) ? 'A' : null;
        $DteRecibido->impuesto_sin_credito = !empty($_POST['impuesto_sin_credito']) ? $_POST['impuesto_sin_credito'] : null;
        $DteRecibido->monto_activo_fijo = !empty($_POST['monto_activo_fijo']) ? $_POST['monto_activo_fijo'] : null;
        $DteRecibido->monto_iva_activo_fijo = !empty($_POST['monto_iva_activo_fijo']) ? $_POST['monto_iva_activo_fijo'] : null;
        $DteRecibido->iva_no_retenido = !empty($_POST['iva_no_retenido']) ? $_POST['iva_no_retenido'] : null;
        $DteRecibido->periodo = !empty($_POST['periodo']) ? $_POST['periodo'] : null;
        // si el DTE es de producción y es electrónico entonces se consultará su
        // estado antes de poder guardar, esto evitará agregar documentos que no
        // han sido recibidos en el SII o sus datos son incorrectos
        if (!$Emisor->config_ambiente_en_certificacion and $DteRecibido->getTipo()->electronico) {
            // obtener firma
            $Firma = $Emisor->getFirma($this->Auth->User->id);
            if (!$Firma) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar), debe agregar su firma antes de generar DTE', 'error'
                );
                $this->redirect('/dte/admin/firma_electronicas');
            }
            // consultar estado dte
            $estado = $DteRecibido->getEstado($Firma);
            if ($estado===false) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No se pudo obtener el estado del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                return;
            } else if (is_string($estado)) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Estado DTE: '.$estado, 'error'
                );
                return;
            }
        }
        // todo ok con el dte así que se agrega a los dte recibidos
        try {
            $DteRecibido->save();
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE recibido guardado', 'ok'
            );
            $this->redirect('/dte/dte_recibidos/listar');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible guardar el DTE: '.$e->getMessage(), 'error'
            );
        }
    }

}
