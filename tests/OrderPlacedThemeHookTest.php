<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TransferPayment\Tests;

use Propel\Runtime\Propel;
use Thelia\Core\TheliaKernel;
use Thelia\Test\IntegrationTestCase;
use TransferPayment\Hook\Theme\OrderPlacedThemeHook;
use TransferPayment\Model\TransferPaymentConfig;
use TransferPayment\Model\TransferPaymentConfigQuery;
use TwigEngine\Service\ThemeHookRenderer;

/**
 * The buyer who ordered with a bank transfer gets the account to transfer to on the confirmation
 * page of a Twig theme; nothing is said about an order paid otherwise.
 *
 * Boots the shop the module is installed in, on the test database `bin/test-prepare` creates:
 *   vendor/bin/phpunit --bootstrap vendor/autoload.php vendor/thelia/modules/TransferPayment/tests
 */
final class OrderPlacedThemeHookTest extends IntegrationTestCase
{
    /**
     * The table is created by the activation of the module, which the test database does not
     * run: created here, before the transaction of the test is opened (a DDL statement would
     * commit it), and left in place.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        if (TheliaKernel::isInstalled()) {
            Propel::getConnection('TheliaMain')->exec(
                'CREATE TABLE IF NOT EXISTS `transfer_payment_config` (`name` VARCHAR(255) NOT NULL, `value` VARCHAR(255), `placement` INTEGER NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB'
            );
        }

        parent::setUp();

        foreach ([['companyName', 'ACME SAS'], ['iban', 'FR7630006000011234567890189'], ['bic', 'AGRIFRPP']] as $placement => [$name, $value]) {
            (TransferPaymentConfigQuery::create()->findPk($name) ?? (new TransferPaymentConfig())->setName($name))
                ->setValue($value)
                ->setPlacement($placement + 1)
                ->save();
        }
    }

    public function testAnOrderPaidByTransferGetsTheBankAccountInItsOrder(): void
    {
        $html = $this->renderHook($this->createFixtureFactory()->order(overrides: ['paymentModuleCode' => 'TransferPayment']));

        self::assertMatchesRegularExpression('/ACME SAS.*FR7630006000011234567890189.*AGRIFRPP/s', $html);
    }

    public function testAnOrderPaidOtherwiseGetsNothing(): void
    {
        self::assertSame('', trim($this->renderHook($this->createFixtureFactory()->order(overrides: ['paymentModuleCode' => 'Cheque']))));
    }

    public function testNoOrderGetsNothing(): void
    {
        self::assertSame('', trim($this->renderer()->render(OrderPlacedThemeHook::HOOK, ['order' => 42])));
    }

    private function renderHook(object $order): string
    {
        return $this->renderer()->render(OrderPlacedThemeHook::HOOK, ['order' => $order]);
    }

    /**
     * Through the renderer of theme_hook(): the hook must be registered, not only written.
     */
    private function renderer(): ThemeHookRenderer
    {
        return static::getContainer()->get(ThemeHookRenderer::class);
    }
}
