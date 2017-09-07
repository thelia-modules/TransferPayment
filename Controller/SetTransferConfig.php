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

namespace TransferPayment\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use TransferPayment\Form\ConfigureTransfer;
use TransferPayment\Model\TransferPaymentConfig;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;

/**
 * Class SetTransferConfig
 * @package TransferPayment\Controller
 * @author Thelia <info@thelia.net>
 */
class SetTransferConfig extends BaseAdminController
{
    public function configure()
    {
        if (null !== $response = $this->checkAuth(
            [AdminResources::MODULE],
            ['TransferPayment'],
            AccessManager::UPDATE)
        ) {
            return $response;
        }

        $form = new ConfigureTransfer($this->getRequest());
        $config = new TransferPaymentConfig();
        $errmes = "";

        try {
            $vform= $this->validateForm($form);

            $name = $vform->get('name')->getData();
            $iban = $vform->get('iban')->getData();
            $bic = $vform->get('bic')->getData();

            $config
                ->setCompanyName($name)
                ->setIban($iban)
                ->setBic($bic)
                ->write();

        } catch (\Exception $e) {
            $errmes = $e->getMessage();
        }

        return $this->generateRedirectFromRoute(
            'admin.module.configure',
            [],
            [
                'module_code' => 'TransferPayment',
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction',
                'errmes' => $errmes
            ]
        );
    }
}
