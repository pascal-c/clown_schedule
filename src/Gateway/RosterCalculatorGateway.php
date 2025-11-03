<?php

declare(strict_types=1);

namespace App\Gateway;

use App\Entity\Clown;
use App\Entity\ClownAvailability;
use App\Entity\ClownAvailabilityTime;
use App\Entity\ClownVenuePreference;
use App\Entity\PlayDate;
use App\Entity\Venue;
use App\Gateway\RosterCalculator\RosterResult;
use App\Repository\ConfigRepository;
use App\Repository\VenueRepository;
use App\Value\Preference;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RosterCalculatorGateway
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ConfigRepository $configRepository,
        private ParameterBagInterface $params,
        private VenueRepository $venueRepository,
    ) {
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return RosterResult the calculated roster
     */
    public function calcuate(array $playDates, array $clownAvailabilities): RosterResult
    {

        $options = [
            'timeout' => 90,
            'max_duration' => 90,
            'json' => $this->serialize($playDates, $clownAvailabilities),
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->params->get('app.roster_calculator_url').'/roster',
            $options,
        );

        if (201 === $response->getStatusCode()) {
            $json = $response->toArray();
            $rosterResult = new RosterResult(
                assignments: $json['assignments'],
                personalResults: $json['personalResults'],
                rating: $json['rating'],
                firstResultTotalPoints: $json['firstResultTotalPoints'],
                counter: $json['counter'],
                isTimedOut: $json['isTimedOut'],
            );
        } else {
            $rosterResult = new RosterResult(
                success: false,
                statusCode: $response->getStatusCode(),
                errorMessage: $response->getContent(false),
            );
        }

        return $rosterResult;
    }

    /**
     * @param array<PlayDate>          $playDates
     * @param array<ClownAvailability> $clownAvailabilities
     *
     * @return RosterResult the calculated roster
     */
    public function rating(array $playDates, array $clownAvailabilities): ?array
    {
        $options = [
            'timeout' => 10,
            'max_duration' => 10,
            'json' => $this->serialize($playDates, $clownAvailabilities),
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->params->get('app.roster_calculator_url').'/rating',
            $options,
        );

        try {
            if (201 === $response->getStatusCode()) {
                return $response->toArray();
            }
        } catch (TransportExceptionInterface $_exception) {
        }

        return null;
    }

    private function serialize(array $playDates, array $clownAvailabilities): array
    {
        $config = $this->configRepository->find();
        $venues = $this->venueRepository->all();

        return [
            'locations' => array_map(
                fn (Venue $venue): array => [
                    'id' => strval($venue->getId()),
                    'blockedPeopleIds' => $venue->getBlockedClowns()->map(fn (Clown $clown): string => strval($clown->getId()))->toArray(),
                ],
                $venues
            ),
            'shifts' => array_map(fn (PlayDate $playDate): array => $this->serializePlayDate($playDate), $playDates),
            'people' => array_map(fn (ClownAvailability $clownAvailability): array => $this->serializeClownAvailability($clownAvailability), $clownAvailabilities),
            'ratingPointWeightings' => [
                'pointsPerMissingPerson' => $config->getPointsPerMissingPerson(),
                'pointsPerMaybePerson' => $config->getPointsPerMaybePerson(),
                'pointsPerTargetShiftsMissed' => $config->getPointsPerTargetShifts(),
                'pointsPerMaxPerWeekExceeded' => $config->getPointsPerMaxPerWeek(),
            ],
        ];
    }

    private function serializeClownAvailability(ClownAvailability $clownAvailability): array
    {
        return [
            'id' => strval($clownAvailability->getClown()->getId()),
            'gender' => $clownAvailability->getClown()->getGender(),
            'constraints' => [
                'wishedShiftsPerMonth' => $clownAvailability->getWishedPlaysMonth(),
                'maxShiftsPerMonth' => $clownAvailability->getMaxPlaysMonth(),
                'maxShiftsPerDay' => $clownAvailability->getMaxPlaysDay(),
                'maxShiftsPerWeek' => $this->configRepository->isFeatureMaxPerWeekActive() ? $clownAvailability->getSoftMaxPlaysWeek() : null,
                'targetShifts' => $clownAvailability->getTargetPlays() ?? 0,
                'locationPreferences' => $clownAvailability->getClown()->getClownVenuePreferences()->map(
                    fn (ClownVenuePreference $preference): array => [
                        'locationId' => strval($preference->getVenue()->getId()),
                        'points' => $this->configRepository->getPointsForPreference($preference->getPreference()),
                    ]
                )->toArray(),
                'locationPreferenceDefaultPoints' => $this->configRepository->getPointsForPreference(Preference::OK),
            ],
            'availabilities' => $clownAvailability->getClownAvailabilityTimes()->map(fn (ClownAvailabilityTime $timeSlot): array => $this->serializeTimeSlot($timeSlot))->toArray(),
        ];
    }

    private function serializeTimeSlot(ClownAvailabilityTime $timeSlot): array
    {
        return [
            'date' => $timeSlot->getDate()->format('Y-m-d'),
            'daytime' => $timeSlot->getDaytime(),
            'availability' => $timeSlot->getAvailability(),
        ];
    }

    private function serializePlayDate(PlayDate $playDate): array
    {
        return [
            'id' => strval($playDate->getId()),
            'date' => $playDate->getDate()->format('Y-m-d'),
            'daytime' => $playDate->getDaytime(),
            'personIds' => $playDate->getPlayingClowns()->map(fn (Clown $clown): string => strval($clown->getId()))->toArray(),
            'locationId' => strval($playDate->getVenue()->getId()),
        ];
    }
}
