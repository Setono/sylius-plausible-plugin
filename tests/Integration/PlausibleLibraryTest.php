<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\SyliusPlausiblePlugin\Tests\Application\Entity\Channel;
use Sylius\Component\Currency\Model\Currency;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Locale\Model\LocaleInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webmozart\Assert\Assert;

/**
 * Drives a real storefront request end to end: the channel resolved from the hostname carries a
 * script identifier, so the response must contain the Plausible library. Nothing else exercises
 * PlausibleLibrarySubscriber through the kernel, the channel context and the layout together.
 */
final class PlausibleLibraryTest extends WebTestCase
{
    private const CHANNEL_CODE = 'plausible_library_test';

    private const IDENTIFIER = 'pa-integration-test';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->removeChannel();
    }

    protected function tearDown(): void
    {
        $this->removeChannel();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_adds_the_library_for_a_channel_with_an_identifier(): void
    {
        $this->createChannel(self::IDENTIFIER);

        $html = $this->requestHomepage();

        self::assertStringContainsString('https://plausible.io/js/' . self::IDENTIFIER . '.js', $html);
        self::assertStringContainsString('window.plausible=window.plausible||function()', $html);
    }

    /**
     * @test
     */
    public function it_adds_nothing_for_a_channel_without_an_identifier(): void
    {
        $this->createChannel(null);

        $html = $this->requestHomepage();

        self::assertStringNotContainsString('plausible.io/js/', $html);
        self::assertStringNotContainsString('window.plausible=', $html);
    }

    private function requestHomepage(): string
    {
        // The browser strips Accept unless one is given, and the subscriber only acts on HTML requests
        $this->client->request('GET', '/en_US/', [], [], [
            'HTTP_HOST' => 'localhost',
            'HTTP_ACCEPT' => 'text/html',
        ]);

        self::assertResponseIsSuccessful();

        $html = $this->client->getResponse()->getContent();
        self::assertIsString($html);

        return $html;
    }

    private function createChannel(?string $identifier): void
    {
        $manager = $this->manager();

        $locale = $manager->getRepository(Locale::class)->findOneBy(['code' => 'en_US']);
        if (!$locale instanceof LocaleInterface) {
            $locale = new Locale();
            $locale->setCode('en_US');
            $manager->persist($locale);
        }

        $currency = $manager->getRepository(Currency::class)->findOneBy(['code' => 'USD']);
        if (!$currency instanceof CurrencyInterface) {
            $currency = new Currency();
            $currency->setCode('USD');
            $manager->persist($currency);
        }

        $channel = new Channel();
        $channel->setCode(self::CHANNEL_CODE);
        $channel->setName('Plausible library test');
        $channel->setHostname('localhost');
        $channel->setEnabled(true);
        $channel->setTaxCalculationStrategy('order_items_based');
        $channel->setBaseCurrency($currency);
        $channel->addCurrency($currency);
        $channel->setDefaultLocale($locale);
        $channel->addLocale($locale);
        $channel->setPlausibleScriptIdentifier($identifier);

        $manager->persist($channel);
        $manager->flush();
        $manager->clear();
    }

    private function removeChannel(): void
    {
        $manager = $this->manager();

        $channel = $manager->getRepository(Channel::class)->findOneBy(['code' => self::CHANNEL_CODE]);
        if ($channel instanceof ChannelInterface) {
            $manager->remove($channel);
            $manager->flush();
        }

        $manager->clear();
    }

    private function manager(): EntityManagerInterface
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        Assert::isInstanceOf($manager, EntityManagerInterface::class);

        return $manager;
    }
}
