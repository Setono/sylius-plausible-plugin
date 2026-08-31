<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Form\Type;

use Setono\SyliusPlausiblePlugin\Form\Type\ChannelPlausibleType;
use Setono\SyliusPlausiblePlugin\Tests\Application\Entity\Channel;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Form\Type\ChannelPlausibleType
 */
final class ChannelPlausibleTypeTest extends TypeTestCase
{
    /**
     * @return list<\Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([new ChannelPlausibleType(Channel::class)], []),
            // the validator extension is what turns a transformation failure into the
            // transformer's own invalid message, exactly as it does in a real Sylius app
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @test
     *
     * @dataProvider acceptedFormats
     */
    public function it_normalizes_every_accepted_format_to_an_identifier(string $input, string $expected): void
    {
        $channel = new Channel();
        $form = $this->factory->create(ChannelPlausibleType::class, $channel);

        $form->submit(['plausibleScriptIdentifier' => $input]);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame($expected, $channel->getPlausibleScriptIdentifier());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function acceptedFormats(): iterable
    {
        yield 'identifier' => ['pa-hb0WlWkUb5U3qhSS-vd-a', 'pa-hb0WlWkUb5U3qhSS-vd-a'];
        yield 'url' => ['https://plausible.io/js/pa-hb0WlWkUb5U3qhSS-vd-a.js', 'pa-hb0WlWkUb5U3qhSS-vd-a'];
        yield 'html snippet' => [
            '<script async src="https://plausible.io/js/pa-hb0WlWkUb5U3qhSS-vd-a.js"></script>',
            'pa-hb0WlWkUb5U3qhSS-vd-a',
        ];
        yield 'self hosted url' => ['https://analytics.example.com/js/pa-test123.js', 'pa-test123'];
    }

    /**
     * @test
     */
    public function it_maps_an_existing_identifier_onto_the_form(): void
    {
        $channel = new Channel();
        $channel->setPlausibleScriptIdentifier('pa-test123');

        $form = $this->factory->create(ChannelPlausibleType::class, $channel);

        self::assertSame('pa-test123', $form->get('plausibleScriptIdentifier')->getViewData());
    }

    /**
     * @test
     */
    public function it_allows_clearing_the_identifier(): void
    {
        $channel = new Channel();
        $channel->setPlausibleScriptIdentifier('pa-test123');

        $form = $this->factory->create(ChannelPlausibleType::class, $channel);
        $form->submit(['plausibleScriptIdentifier' => '']);

        self::assertTrue($form->isSynchronized());
        self::assertNull($channel->getPlausibleScriptIdentifier());
    }

    /**
     * @test
     */
    public function it_is_not_required(): void
    {
        $form = $this->factory->create(ChannelPlausibleType::class, new Channel());

        self::assertFalse($form->get('plausibleScriptIdentifier')->isRequired());
    }

    /**
     * @test
     */
    public function it_rejects_a_value_without_an_identifier(): void
    {
        $channel = new Channel();
        $form = $this->factory->create(ChannelPlausibleType::class, $channel);

        $form->submit(['plausibleScriptIdentifier' => 'https://plausible.io/js/script.js']);

        self::assertFalse($form->get('plausibleScriptIdentifier')->isSynchronized());
        self::assertNull($channel->getPlausibleScriptIdentifier());
    }

    /**
     * @test
     */
    public function it_reports_the_translated_error_message_for_an_invalid_value(): void
    {
        $form = $this->factory->create(ChannelPlausibleType::class, new Channel());

        $form->submit(['plausibleScriptIdentifier' => 'not a plausible script']);

        $errors = $form->get('plausibleScriptIdentifier')->getErrors();

        self::assertCount(1, $errors);
        self::assertSame('setono_sylius_plausible.plausible_script.invalid', $errors[0]->getMessageTemplate());
    }
}
