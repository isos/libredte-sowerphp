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
 * Clase para las acciones asociadas a items
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-21
 */
class Controller_Items extends \Controller_App
{

    /**
     * Recurso de la API que permite obtener los datos de un item a partir de su
     * código
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-30
     */
    public function _api_info_GET($codigo, $empresa)
    {
        // crear contribuyente y verificar que exista y tenga api configurada
        $Empresa = new Model_Contribuyente($empresa);
        if (!$Empresa->exists())
            $this->Api->send('Empresa solicitada no existe', 404);
        if (!$Empresa->config_api_url_items)
            $this->Api->send('Empresa no tiene configurada API para consultar items', 500);
        // consultar item
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

}
