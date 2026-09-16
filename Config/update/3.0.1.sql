-- The seed of this module carried the Thelia 2 wording of its message, which Thelia 3 renders
-- with Twig: a shop installed before 3.0.1 mails the placeholder itself to the customer.

UPDATE `message_i18n`
INNER JOIN `message` ON `message`.`id` = `message_i18n`.`id`
SET `message_i18n`.`subject` = REPLACE(`message_i18n`.`subject`, '{$order_ref}', '{{ order_ref }}'),
    `message_i18n`.`text_message` = REPLACE(`message_i18n`.`text_message`, '{$order_ref}', '{{ order_ref }}'),
    `message_i18n`.`html_message` = REPLACE(`message_i18n`.`html_message`, '{$order_ref}', '{{ order_ref }}')
WHERE `message`.`name` = 'order_confirmation_transferpayment';
