<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla contribuyente de la base de
 * datos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-19
 */
class Controller_Contribuyentes extends \Controller_App
{

    private $permisos_usuarios = [
        'todos' => 'Todos los permisos',
        /*'folios' => 'Cargar folios',
        'emitir' => 'Emitir documentos tributarios electrónicos',
        'libros' => 'Generar y consultar libros de compra y venta',
        'intercambio' => 'Intercambio entre contribuyentes',
        'respaldos' => 'Realizar respaldos de los datos',*/
    ];

    /**
     * Método que selecciona la empresa con la que se trabajará en el módulo DTE
     * @param rut Si se pasa un RUT se tratará de seleccionar
     * @param url URL a la que redirigir después de seleccionar el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-25
     */
    public function seleccionar($rut = null, $url = null)
    {
        $referer = \sowerphp\core\Model_Datasource_Session::read('referer');
        // si se está pidiendo una empresa en particular se tratará de usar
        if ($rut) {
            $Emisor = new Model_Contribuyente($rut);
            if (!$Emisor->exists()) {
                \sowerphp\core\Model_Datasource_Session::message('Empresa solicitada no existe', 'error');
                $this->redirect($this->request->request);
            }
            if (!$Emisor->usuarioAutorizado($this->Auth->User->id)) {
                \sowerphp\core\Model_Datasource_Session::message('No está autorizado a operar con la empresa solicitada', 'error');
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            if (!$url)
                \sowerphp\core\Model_Datasource_Session::message('Desde ahora estará operando con '.$Emisor->razon_social);
        }
        // si no se indicó una empresa por su rut se tratará de usar la que
        // esté configurada (si existe) o bien se mostrará listado de las
        // empresas a las que el usuario tiene acceso para poder elegir alguna
        else {
            // si hay una empresa forzada a través de la configuración se crea
            $empresa = \sowerphp\core\Configure::read('dte.empresa');
            if ($empresa) {
                $Emisor = new Model_Contribuyente();
                $Emisor->set($empresa);
                \sowerphp\core\Model_Datasource_Session::message(); // borrar mensaje de sesión si había
            }
        }
        // si se llegó acá con un emisor se guarda en la sesión
        if (isset($Emisor)) {
            $this->setContribuyente($Emisor);
            // redireccionar
            if ($referer)
                \sowerphp\core\Model_Datasource_Session::delete('referer');
            else if ($url)
                $referer = base64_decode($url);
            else
                $referer = '/dte';
            $this->redirect($referer);
        }
        // asignar variables para la vista
        $this->set([
            'empresas' => (new Model_Contribuyentes())->getByUsuario($this->Auth->User->id),
        ]);
    }

    /**
     * Método que permite registrar un nuevo contribuyente y asociarlo a un usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-30
     */
    public function registrar()
    {
        // asignar variables para la vista
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
        ]);
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            // verificar campos mínimos
            foreach (['rut', 'razon_social', 'giro', 'actividad_economica', 'direccion', 'comuna', 'certificacion_resolucion'] as $attr) {
                if (empty($_POST[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar: '.$attr, 'error'
                    );
                    return;
                }
            }
            // crear objeto del contribuyente con el rut y verificar que no esté ya asociada a un usuario
            list($rut, $dv) = explode('-', str_replace('.', '', $_POST['rut']));
            $Contribuyente = new Model_Contribuyente($rut);
            if ($Contribuyente->usuario) {
                if ($Contribuyente->usuario==$this->Auth->User->id) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Ya tiene asociada la empresa a su usuario'
                    );
                    $this->redirect('/dte/contribuyentes/seleccionar');
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'La empresa ya está registrada a nombre del usuario '.$Contribuyente->getUsuario()->nombre.' ('.$Contribuyente->getUsuario()->email.'). Si cree que esto es un error o bien puede ser alguien suplantando la identidad de su empresa por favor <a href="'.$this->request->base.'/contacto" target="_blank">contáctenos</a>.', 'error'
                    );
                    return;
                }
            }
            // rellenar campos de la empresa
            $Contribuyente->set($_POST);
            $Contribuyente->rut = $rut;
            $Contribuyente->dv = $dv;
            $Contribuyente->certificacion = (int)isset($_POST['certificacion']);
            if (!empty($_POST['sii_pass'])) {
                $Contribuyente->sii_pass = Utility_Data::encrypt($_POST['sii_pass']);
            }
            if (!empty($_POST['intercambio_pass'])) {
                $Contribuyente->intercambio_pass = Utility_Data::encrypt($_POST['intercambio_pass']);
            }
            $Contribuyente->usuario = $this->Auth->User->id;
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            if (!empty($_POST['api_token'])) {
                $Contribuyente->api_token = Utility_Data::encrypt($_POST['api_token']);
            }
            // si está en producción validar fecha y número de resolución
            if (!$Contribuyente->certificacion and (empty($Contribuyente->resolucion_fecha) or empty($Contribuyente->resolucion_numero))) {
                \sowerphp\core\Model_Datasource_Session::message('Para pasar la empresa a producción debe indicar la fecha y número de resolución que la autoriza', 'error');
                return;
            }
            // si se pasó un logo se guarda
            if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
                // si el formano no es PNG error
                if (\sowerphp\general\Utility_File::mimetype($_FILES['logo']['tmp_name'])!='image/png') {
                    \sowerphp\core\Model_Datasource_Session::message('Formato del logo debe ser PNG', 'error');
                    return;
                }
                $config = \sowerphp\core\Configure::read('dte.logos');
                // redimensionar imagen
                \sowerphp\general\Utility_Image::resizeOnFile($_FILES['logo']['tmp_name'], $config['width'], $config['height']);
                // copiar imagen a directorio final
                move_uploaded_file($_FILES['logo']['tmp_name'], $config['dir'].'/'.$Contribuyente->rut.'.png');
            }
            // guardar contribuyente
            try {
                $Contribuyente->save();
                // guardar los DTE por defecto que la empresa podrá usar
                $dtes = \sowerphp\core\Configure::read('dte.dtes');
                foreach ($dtes as $dte) {
                    $ContribuyenteDte = new \website\Dte\Admin\Model_ContribuyenteDte(
                        $Contribuyente->rut, $dte
                    );
                    try {
                        $ContribuyenteDte->save();
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e){}
                }
                // redireccionar
                \sowerphp\core\Model_Datasource_Session::message('Empresa '.$Contribuyente->razon_social.' registrada y asociada a su usuario', 'ok');
                $this->redirect('/dte/contribuyentes/seleccionar');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible registrar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
    }

    /**
     * Método que permite modificar contribuyente previamente registrado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-29
     */
    public function modificar($rut)
    {
        // crear objeto del contribuyente
        try {
            $Contribuyente = new Model_Contribuyente($rut);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message('No se encontró la empresa solicitada', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // verificar que el usuario sea el administrador
        if ($Contribuyente->usuario!=$this->Auth->User->id) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // asignar variables para editar
        $this->set([
            'Contribuyente' => $Contribuyente,
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
        ]);
        // editar
        if (isset($_POST['submit'])) {
            // verificar campos mínimos
            foreach (['razon_social', 'giro', 'actividad_economica', 'direccion', 'comuna', 'certificacion_resolucion'] as $attr) {
                if (empty($_POST[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar: '.$attr, 'error'
                    );
                    return;
                }
            }
            // rellenar campos de la empresa
            foreach (array_keys(Model_Contribuyente::$columnsInfo) as $attr) {
                if (!empty($_POST[$attr]))
                    $Contribuyente->$attr = $_POST[$attr];
            }
            $Contribuyente->certificacion = (int)isset($_POST['certificacion']);
            if (!empty($_POST['sii_pass'])) {
                $Contribuyente->sii_pass = Utility_Data::encrypt($_POST['sii_pass']);
            }
            if (!empty($_POST['intercambio_pass'])) {
                $Contribuyente->intercambio_pass = Utility_Data::encrypt($_POST['intercambio_pass']);
            }
            $Contribuyente->usuario = $this->Auth->User->id;
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            $Contribuyente->api_token = !empty($_POST['api_token']) ? Utility_Data::encrypt($_POST['api_token']) : null;
            // si está en producción validar fecha y número de resolución
            if (!$Contribuyente->certificacion and (empty($Contribuyente->resolucion_fecha) or empty($Contribuyente->resolucion_numero))) {
                \sowerphp\core\Model_Datasource_Session::message('Para pasar la empresa a producción debe indicar la fecha y número de resolución que la autoriza', 'error');
                return;
            }
            // si se pasó un logo se guarda
            if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
                // si el formano no es PNG error
                if (\sowerphp\general\Utility_File::mimetype($_FILES['logo']['tmp_name'])!='image/png') {
                    \sowerphp\core\Model_Datasource_Session::message('Formato del logo debe ser PNG', 'error');
                    return;
                }
                $config = \sowerphp\core\Configure::read('dte.logos');
                // redimensionar imagen
                \sowerphp\general\Utility_Image::resizeOnFile($_FILES['logo']['tmp_name'], $config['width'], $config['height']);
                // copiar imagen a directorio final
                move_uploaded_file($_FILES['logo']['tmp_name'], $config['dir'].'/'.$Contribuyente->rut.'.png');
            }
            // guardar contribuyente
            try {
                $Contribuyente->save();
                \sowerphp\core\Model_Datasource_Session::message('Empresa '.$Contribuyente->razon_social.' ha sido modificada', 'ok');
                $this->redirect('/dte/contribuyentes/seleccionar');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible modificar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
    }

    /**
     * Método que permite editar los usuarios autorizados de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function usuarios($rut)
    {
        // crear objeto del contribuyente
        try {
            $Contribuyente = new Model_Contribuyente($rut);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message('No se encontró la empresa solicitada', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // verificar que el usuario sea el administrador
        if ($Contribuyente->usuario!=$this->Auth->User->id) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // asignar variables para editar
        $this->set([
            'Contribuyente' => $Contribuyente,
            'permisos_usuarios' => $this->permisos_usuarios,
        ]);
        // editar usuarios autorizados
        if (isset($_POST['submit'])) {
            $usuarios = [];
            if (isset($_POST['usuario'])) {
                $n_usuarios = count($_POST['usuario']);
                for ($i=0; $i<$n_usuarios; $i++) {
                    if (!empty($_POST['usuario'][$i]) and !empty($_POST['permiso'][$i])) {
                        if (!isset($usuarios[$_POST['usuario'][$i]]))
                            $usuarios[$_POST['usuario'][$i]] = [];
                        if (!array_key_exists($_POST['permiso'][$i], $this->permisos_usuarios)) {
                            \sowerphp\core\Model_Datasource_Session::message(
                                'El permiso <em>'.$_POST['permiso'][$i].'</em> no existe', 'warning'
                            );
                            return;
                        }
                        $usuarios[$_POST['usuario'][$i]][] = $_POST['permiso'][$i];
                    }
                }
                if (!$usuarios) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No indicó ningún usuario autorizado para agregar', 'warning'
                    );
                    return;
                }
            }
            try {
                $Contribuyente->setUsuarios($usuarios);
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se editaron los usuarios autorizados de la empresa '.$Contribuyente->razon_social, 'ok'
                );
                $this->redirect('/dte/contribuyentes/seleccionar');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible editar los usuarios autorizados<br/>'.$e->getMessage(), 'error'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
            }
        }
    }

    /**
     * Acción que entrega el logo del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-29
     */
    public function logo($rut)
    {
        $Contribuyente = new Model_Contribuyente(substr($rut, 0, -4));
        if (!$Contribuyente->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Contribuyente solicitado no existe'
            );
            $this->redirect('/');
        }
        $dir = \sowerphp\core\Configure::read('dte.logos.dir');
        $logo = $dir.'/'.$Contribuyente->rut.'.png';
        if (!is_readable($logo))
            $logo = $dir.'/default.png';
        header('Content-Type: image/png');
        header('Content-Length: '.filesize($logo));
        header('Content-Disposition: inline; filename="'.$Contribuyente->rut.'.png"');
        print file_get_contents($logo);
        exit;
    }

    /**
     * Método de la API que permite obtener los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-30
     */
    public function _api_info_GET($rut)
    {
        $Contribuyente = new Model_Contribuyente($rut);
        if (!$Contribuyente->exists())
            $this->Api->send('Contribuyente solicitado no existe', 404);
        $clean = ['sii_pass', 'intercambio_pass', 'api_token', 'api_items'];
        foreach($clean as $attr)
            $Contribuyente->$attr = false;
        $this->Api->send($Contribuyente, 200, JSON_PRETTY_PRINT);
    }

}
