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

// título del módulo
\sowerphp\core\Configure::write('module.title', 'Panel de administración');

// Menú para el módulo
\sowerphp\core\Configure::write('nav.module', [
    '/dte_folios' => [
        'name' => 'Folios',
        'desc' => 'Mantenedor de códigos de autorización de folios',
        'icon' => 'fa fa-cube',
    ],
    '/firma_electronicas' => [
        'name' => 'Firma electrónica',
        'desc' => 'Mantenedor para poder cargar la firma electrónica del usuario',
        'icon' => 'fa fa-certificate',
    ],
    '/respaldos/exportar' => [
        'name' => 'Exportar datos',
        'desc' => 'Exportar datos del sistema para respaldo o migración',
        'icon' => 'fa fa-download',
    ],
    /*'/respaldos/importar' => [
        'name' => 'Importar datos',
        'desc' => 'Importar datos al sistema para restaurar respaldo o migración',
        'icon' => 'fa fa-upload',
    ],*/
    '/contribuyente_dtes/listar/1/contribuyente/A' => [
        'name' => 'DTEs autorizados por contribuyente',
        'desc' => 'DTEs que los contribuyentes de LibreDTE tienen autorizado emitir en la aplicación',
        'icon' => 'fa fa-list',
    ],
    '/dte_tipos/listar/1/codigo/A' => [
        'name' => 'Documentos tributarios',
        'desc' => 'Tipos de documentos tributarios (electrónicos y no electrónicos)',
        'icon' => 'fa fa-list-alt',
    ],
    '/iva_no_recuperables/listar/1/codigo/A' => [
        'name' => 'IVA no recuperable',
        'icon' => 'fa fa-dollar',
    ],
    '/impuesto_adicionales/listar/1/codigo/A' => [
        'name' => 'Impuestos adicionales',
        'icon' => 'fa fa-dollar',
    ],
]);
