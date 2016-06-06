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

// namespace del modelo
namespace website\Dte\Informes;

/**
 * Modelo para obtener los datos del formulrio 29
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-01
 */
class Model_F29
{

    private $datos; ///< Arreglo con código y valores del formulario 29

    /**
     * Constructor del modelo F29
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-02
     */
    public function __construct(\website\Dte\Model_Contribuyente $Emisor, $periodo)
    {
        $this->Emisor = $Emisor;
        $this->periodo = (int)$periodo;
        $this->datos = [
            '01' => $this->Emisor->razon_social,
            '03' => num($Emisor->rut).'-'.$Emisor->dv,
            '06' => $this->Emisor->direccion,
            '08' => $this->Emisor->getComuna()->comuna,
            '09' => $this->Emisor->telefono,
            '15' => substr($periodo, 4).'/'.substr($periodo, 0, 4),
            '55' => $this->Emisor->email,
            '313' => $this->Emisor->config_extra_contador_rut,
            '314' => $this->Emisor->config_extra_representante_rut,
        ];
    }

    public function setCompras($compras)
    {
    }

    public function setVentas($ventas)
    {
    }

    /**
     * Método que entrega un arreglo con los códigos del F29 y sus valores
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-01
     */
    public function getDatos()
    {
        return $this->datos;
    }

}
