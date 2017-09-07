<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace TransferPayment;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Model\Order;
use Thelia\Module\BaseModule;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\ModuleQuery;
use TransferPayment\Model\TransferPaymentConfigQuery;

/**
 * Class TransferPayment
 * @package TransferPayment
 * author Thelia <info@thelia.net>
 */
class TransferPayment extends BaseModule implements PaymentModuleInterface
{
    /**
     * @param Order $order
     */
    public function pay(Order $order)
    {
        // nothing to do here.
    }

    /**
     * This method is called on Payment loop.
     *
     * If you return true, the payment method will de display
     * If you return false, the payment method will not be display
     *
     * @return boolean
     */
    public function isValidPayment()
    {
        /*
         * Check if database values are ok.
         */
        $cond = true;

        $query = TransferPaymentConfigQuery::create();
        $name = $query->findPk("companyName");
        $iban = $query->findPk("iban");
        $bic = $query->findPk("bic");

        $cond &= $name !== null && $iban !== null && $bic !== null;

        // TODO: check if iban is valid

        return boolval($cond);
    }

    /**
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        /* insert the images from image folder if first module activation */
        $module = $this->getModuleModel();
        if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
        }

        /* set module title */
        $this->setTitle(
            $module,
            array(
                "en_US" => "Transfer",
                "fr_FR" => "Virement",
            )
        );

        $database = new Database($con->getWrappedConnection());

        $database->insertSql(null, array(__DIR__."/Config/thelia.sql"));
    }

    public static function getModCode()
    {
        $mod_code = "TransferPayment";
        $search = ModuleQuery::create()
            ->findOneByCode($mod_code);

        return $search->getId();
    }

    public function manageStockOnCreation()
    {
        return false;
    }
}
