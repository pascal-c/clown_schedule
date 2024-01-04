<?php

namespace App\Tests\Controller;

class VenueControllerTest extends WebTestCase
{
    public function testcreateAndShow(): void
    {
        $client = $this->login();
        $erika = $this->createClown('Erika');

        $client->followRedirects();
        $client->request('GET', '/venues');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Spielorte');

        $crawler = $client->clickLink('Spielort anlegen');
        $buttonCrawlerNode = $crawler->selectButton('Spielort speichern');
        $form = $buttonCrawlerNode->form();

        $form['venue_form[name]'] = 'DRK Leipzig';
        $form['venue_form[contactEmail]'] = 'erika@leipzig.de';
        $form['venue_form[responsibleClowns][1]']->tick();

        $form['venue_form[daytimeDefault]'] = 'am';
        $form['venue_form[meetingTime][hour]'] = '9';
        $form['venue_form[meetingTime][minute]'] = '30';
        $form['venue_form[playTimeFrom][hour]'] = '10';
        $form['venue_form[playTimeFrom][minute]'] = '0';
        $form['venue_form[playTimeTo][hour]'] = '12';
        $form['venue_form[playTimeTo][minute]'] = '0';

        $crawler = $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'DRK Leipzig');

        $emailsRow = $this->findNodeByText($crawler, 'table tr', 'Email');
        $this->assertNodeTextContains($emailsRow, 'erika@leipzig.de');

        $this->assertNodeTextContains(
            $this->findNodeByText($crawler, 'table tr', 'Verantwortliche Clowns'),
            'Erika'
        );

        $this->assertNodeTextContains(
            $this->findNodeByText($crawler, 'table tr', 'Standard Tageszeit für Spieltermine'),
            'vormittags'
        );
        $this->assertNodeTextContains(
            $this->findNodeByText($crawler, 'table tr', 'Treffen'),
            '09:30'
        );
        $this->assertNodeTextContains(
            $this->findNodeByText($crawler, 'table tr', 'Spielzeit'),
            '10:00 - 12:00'
        );
    }

    /**
     * @depends testcreateAndShow
     */
    public function tetIndex(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/venues');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4', 'Spielorte');

        $this->assertNodeTextContains(
            $this->findNodeByText($crawler, 'table tr', 'DRK Leipzig'),
            'nachmittags'
        );
    }
}
