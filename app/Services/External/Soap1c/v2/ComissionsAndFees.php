<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 31.01.2019
 * Time: 16:56
 */

namespace App\Services\External\Soap1c\v2;


use App\Services\External\Soap1c\Request;

/**
 * Class ComissionsAndFees
 * @method static ExportFees($params)
 * @method static ExportSpecials($params)
 * @package App\Services\External\Soap1c\v2
 */
class ComissionsAndFees extends Request
{
    protected static $basePath = 'Trivago_comissions_and_fees';

    protected static $methods = [
        'ExportFees',
        'ExportSpecials'
    ];

    protected static $version = 't2019';
}