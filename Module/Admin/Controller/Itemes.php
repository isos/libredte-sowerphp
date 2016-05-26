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
namespace website\Dte\Admin;

/**
 * Clase para las acciones asociadas a items
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-24
 */
class Controller_Itemes extends \Controller_App
{

    /**
     * Recurso de la API que permite obtener los datos de un item a partir de su
     * código
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-19
     */
    public function _api_info_GET($empresa, $codigo, $fecha = null, $tipo = null)
    {
        // obtener usuario autenticado
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        // crear contribuyente y verificar que exista y tenga api configurada
        $Empresa = new \website\Dte\Model_Contribuyente($empresa);
        if (!$Empresa->exists())
            $this->Api->send('Empresa solicitada no existe', 404);
        // consultar item en servicio web del contribuyente
        if ($Empresa->config_api_url_items) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            if ($Empresa->config_api_auth_user) {
                if ($Empresa->config_api_auth_pass)
                    $rest->setAuth($Empresa->config_api_auth_user, $Empresa->config_api_auth_pass);
                else
                    $rest->setAuth($Empresa->config_api_auth_user);
            }
            $response = $rest->get($Empresa->config_api_url_items.$codigo);
            $this->Api->send($response['body'], $response['status']['code']);
        }
        // consultar item en base de datos local de LibreDTE
        else {
            $this->Api->send('Contribuyente no tiene servicio web de items', 500);
        }
    }

}
