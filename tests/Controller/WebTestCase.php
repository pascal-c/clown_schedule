<?php

namespace App\Tests\Controller;

use App\Entity\Clown;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class WebTestCase extends SymfonyWebTestCase
{
    protected function findNodeByText(Crawler $crawler, string $selector, string $search): Crawler
    {
        $allElements = $crawler->filter($selector)->each(function ($element, $i) use ($search) {
            $text = $element->text();
            return str_contains($text, $search) ? $element : null;
        });
        
        $elements = array_filter($allElements);
        if (empty($elements)) {
             new \InvalidArgumentException('Nothing found for selector "' . $selector . '" with text "' . $search . '"');
        } elseif (count($elements) > 1) {
            throw new \InvalidArgumentException('More than one entry found for selector "' . $selector . '" with text "' . $search . '"');
        }

        return array_shift($elements);
    }

    protected function assertNodeTextContains(Crawler $node, string $search): void
    {
        $this->assertStringContainsString($search, $node->text());
    }

    protected function buildClown(string $name, string $email, $password): Clown
    {
        $clown = new Clown;
        $clown
            ->setGender('female')
            ->setName($name)
            ->setEmail($email)
            ->setPassword(password_hash($password, PASSWORD_DEFAULT))
            ->setIsAdmin(true)
            ;
        return $clown;
    }

    protected function createClown(string $name = 'Hugo', string $email='hugo@test.de', $password = 'secret123'): Clown
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $clown = $this->buildClown($name, $email, $password);
        $entityManager->persist($clown);
        $entityManager->flush();
        return $clown;
    }

    protected function login()
    {
        $client = static::createClient();
        $this->createClown('Emil', 'emil@test.de', 'secret123');
        $client->request('GET', '/login');

        $form = $client->getCrawler()->selectButton('anmelden')->form();
        $form['login_form[email]'] = 'emil@test.de';
        $form['login_form[password]'] = 'secret123';
        $client->submit($form);

        return $client;
    }
}
