<?php

namespace App\Tests;

use App\Entity\Clown;
use App\Factory\ClownFactory;
use App\Service\AuthService;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mime\Email;

/**
 * Inherited Methods.
 *
 * @method void                    wantToTest($text)
 * @method void                    wantTo($text)
 * @method void                    execute($callable)
 * @method void                    expectTo($prediction)
 * @method void                    expect($prediction)
 * @method void                    amGoingTo($argumentation)
 * @method void                    am($role)
 * @method void                    lookForwardTo($achieveValue)
 * @method void                    comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public function getCurrentUser(): ?Clown
    {
        return $this->grabService(AuthService::class)->getCurrentClown();
    }

    public function login(string $email, string $password): void
    {
        $I = $this;
        $I->amOnPage('/login');
        $I->fillField('login_form[email]', $email);
        $I->fillField('login_form[password]', $password);
        $I->click('anmelden');
    }

    public function loginAsClown(?string $name = null): void
    {
        $I = $this;
        $clown = $I->grabService(ClownFactory::class)->create(isAdmin: false, password: 'secret', name: $name);
        $I->login($clown->getEmail(), 'secret');
    }

    public function selectTimeOption(string $classNamePrefix, string $time): void
    {
        $I = $this;
        $hourLocator = $classNamePrefix.'[hour]';
        $minuteLocator = $classNamePrefix.'[minute]';
        list($hour, $minute) = explode(':', $time);
        $I->selectOption($hourLocator, $hour);
        $I->selectOption($minuteLocator, $minute);
    }

    public function checkMultipleOption(string $fieldLabel, array $optionLabels): void
    {
        $I = $this;
        foreach ($optionLabels as $label) {
            $I->checkOption('.//fieldset[legend[contains(., "'.$fieldLabel.'")]]//div[@class="form-check" and contains(label, "'.$label.'")]/input');
        }
    }

    public function uncheckMultipleOption(string $fieldLabel, array $optionLabels): void
    {
        $I = $this;
        foreach ($optionLabels as $label) {
            $I->uncheckOption('.//fieldset[legend[contains(., "'.$fieldLabel.'")]]//div[@class="form-check" and contains(label, "'.$label.'")]/input');
        }
    }

    public function clickLinkInEmail(Email $email): void
    {
        $I = $this;
        $crawler = new Crawler(quoted_printable_decode($email->getBody()->toString()));
        $loginLink = $crawler->filter('a');
        $url = $loginLink->attr('href');
        $I->amOnPage($url);
    }
}
