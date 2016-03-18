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
     * @version 2016-01-27
     */
    public function registrar()
    {
        // asignar variables para la vista
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'titulo' => 'Registrar nueva empresa',
            'descripcion' => 'Aquí podrá registrar una nueva empresa para la cual usted será el usuario administrador de la misma.',
            'form_id' => 'registrarContribuyente',
            'boton' => 'Registrar empresa',
        ]);
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
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
            $Contribuyente->usuario = $this->Auth->User->id;
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            // guardar contribuyente
            try {
                $Contribuyente->save(true);
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
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible registrar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
        // renderizar vista
        $this->autoRender = false;
        $this->render('Contribuyentes/registrar_modificar');
    }

    /**
     * Método que permite modificar contribuyente previamente registrado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
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
            'titulo' => 'Modificar empresa '.$Contribuyente->razon_social,
            'descripcion' => 'Aquí podrá modificar los datos de la empresa '.$Contribuyente->razon_social.' RUT '.num($Contribuyente->rut).'-'.$Contribuyente->dv.', para la cual usted es el usuario administrador.',
            'form_id' => 'modificarContribuyente',
            'boton' => 'Modificar empresa',
        ]);
        // editar contribuyente
        if (isset($_POST['submit'])) {
            $Contribuyente->set($_POST);
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            try {
                $Contribuyente->save(true);
                \sowerphp\core\Model_Datasource_Session::message('Empresa '.$Contribuyente->razon_social.' ha sido modificada', 'ok');
                $this->redirect('/dte/contribuyentes/seleccionar');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible modificar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
        // renderizar vista
        $this->autoRender = false;
        $this->render('Contribuyentes/registrar_modificar');
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
     * @version 2016-03-18
     */
    public function _api_info_GET($rut)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Contribuyente = new Model_Contribuyente($rut);
        if (!$Contribuyente->exists())
            $this->Api->send('Contribuyente solicitado no existe', 404);
        $Contribuyente->config_ambiente_produccion_fecha;
        $Contribuyente->config_ambiente_produccion_numero;
        $this->Api->send($Contribuyente, 200, JSON_PRETTY_PRINT);
    }

}
