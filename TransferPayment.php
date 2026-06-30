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
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Model\Order;
use Thelia\Module\BaseModule;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Module\AbstractPaymentModule;
use TransferPayment\Model\TransferPaymentConfigQuery;
use TransferPayment\Tools\Regex;
use Thelia\Core\Install\Database;

/**
 * Class TransferPayment
 * @package TransferPayment
 * author Thelia <info@thelia.net>
 */
class TransferPayment extends AbstractPaymentModule
{
    /**
     * @param Order $order
     */
    public function pay(Order $order): ?Response
    {
        return null;
    }

    /**
     *
     * This method is called on Payment loop.
     *
     * If you return true, the payment method will de display
     * If you return false, the payment method will not be display
     *
     * @return boolean
     */
    public function isValidPayment(): bool
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

        if(round($this->getCurrentOrderTotalAmount(), 4) == 0){
            $cond = false;
        }

        return boolval($cond);
    }

    /**
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        $module = $this->getModuleModel();

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

    public static function getModCode(): int
    {
        $search = ModuleQuery::create()
            ->findOneByCode('TransferPayment');

        return $search?->getId() ?? 0;
    }

    public function manageStockOnCreation(): bool
    {
        return false;
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
