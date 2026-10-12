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

namespace TransferPayment\Hook\Theme;

use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Thelia\Domain\Localization\Service\LangService;
use Thelia\Model\Lang;
use Thelia\Model\Order;
use TransferPayment\Model\TransferPaymentConfigQuery;
use TransferPayment\TransferPayment;
use Twig\Environment;

/**
 * Gives the buyer who just ordered with a bank transfer the account to transfer to, on the
 * confirmation page of a Twig front-office theme.
 *
 * A theme hook, not the legacy order-placed.additional-payment-info hook of HookManager: that
 * one is rendered by the Smarty {hook} tag, and a Thelia 3 theme declares its extension points
 * with theme_hook() instead. The theme passes the order it shows, and the module only answers
 * for an order paid by transfer: every payment module is asked at the same point.
 *
 * A theme words the block its own way by putting modules/TransferPayment/<template> in its
 * folder, as it did with the Smarty template; the module's own template is used otherwise.
 */
final readonly class OrderPlacedThemeHook implements ThemeHookInterface
{
    public const HOOK = 'order-placed.additional-payment-info';

    public const TEMPLATE = 'order-placed.additional-payment-info.html.twig';

    private const THEME_TEMPLATE = 'modules/TransferPayment/'.self::TEMPLATE;

    private const MODULE_TEMPLATE = '@TransferPaymentModule/frontOffice/default/'.self::TEMPLATE;

    private const DOMAIN = 'transferpayment';

    public function __construct(
        private Environment $twig,
        private TranslatorInterface $translator,
        private LangService $langService,
    ) {
    }

    public function supports(string $hookName): bool
    {
        return self::HOOK === $hookName;
    }

    public function render(string $hookName, array $parameters): string
    {
        $order = $parameters['order'] ?? null;

        if (!$order instanceof Order || (int) $order->getPaymentModuleId() !== TransferPayment::getModCode()) {
            return '';
        }

        $locale = $this->locale();
        $bankInformation = [];

        foreach (TransferPaymentConfigQuery::create()->orderByPlacement()->find() as $row) {
            $bankInformation[] = [
                'name' => (string) $row->getName(),
                'label' => $this->translator->trans((string) $row->getName(), [], self::DOMAIN, $locale),
                'value' => (string) $row->getValue(),
            ];
        }

        return $this->twig->render($this->template(), [
            'order' => $order,
            'introduction' => $this->translator->trans('You may now do a transfer to this bank account: ', [], self::DOMAIN, $locale),
            'bank_information' => $bankInformation,
        ]);
    }

    private function template(): string
    {
        return $this->twig->getLoader()->exists(self::THEME_TEMPLATE) ? self::THEME_TEMPLATE : self::MODULE_TEMPLATE;
    }

    private function locale(): string
    {
        return (string) ($this->langService->getLang()?->getLocale() ?? Lang::getDefaultLanguage()->getLocale());
    }
}
