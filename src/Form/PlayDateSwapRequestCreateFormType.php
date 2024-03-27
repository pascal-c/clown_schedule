<?php

namespace App\Form;

use App\Repository\PlayDateRepository;
use App\Service\AuthService;
use App\Service\Scheduler\AvailabilityChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PlayDateSwapRequestCreateFormType extends AbstractType
{
    public function __construct(
        private PlayDateRepository $playDateRepository,
        private AvailabilityChecker $availabilityChecker,
        private AuthService $authService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $month = $options['playDateToGiveOff']->getMonth();
        $playDates = $this->playDateRepository->futureByMonth($month);
        $choices = ['--- bitte wählen ---' => null];
        foreach ($playDates as $playDate) {
            $sameTimeSlotPeriod = $playDate->equalsTimeSlotPeriod($options['playDateToGiveOff']) && $playDate != $options['playDateToGiveOff'];
            $clownAvailability = $options['currentClown']->getAvailabilityFor($month);
            if (!$clownAvailability || (!$sameTimeSlotPeriod && !$this->availabilityChecker->isAvailableOn($playDate, $clownAvailability))) {
                continue;
            }
            $clownChoices = [];
            foreach ($playDate->getPlayingClowns() as $clown) {
                $clownAvailability = $clown->getAvailabilityFor($month);
                if (!$clownAvailability || (!$sameTimeSlotPeriod && !$this->availabilityChecker->isAvailableOn($options['playDateToGiveOff'], $clownAvailability))) {
                    continue;
                }
                $clownChoices[$clown->getName()] = $playDate->getId().'-'.$clown->getId();
            }
            $choices[$playDate->getDate()->format('d.m.Y').' '.$playDate->getName()] = $clownChoices;
        }

        $builder
            ->add('playDateAndClown', ChoiceType::class, [
                'choices' => $choices,
                'required' => true,
                'label' => 'Gegen welchen Termin möchtest Du tauschen?',
                'expanded' => false,
                'multiple' => false,
                'constraints' => new NotBlank(),
                'help' => 'Es werden nur Termine angezeigt, an denen Du verfügbar bist und Clowns, die an dem Tauschtermin verfügbar sind.',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Hier könntest Du noch eine persönliche Nachricht an die angefragte Person hinterlassen',
                'required' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Tauschanfrage senden!'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('currentClown', null);
        $resolver->setDefault('playDateToGiveOff', null);
        $resolver->setDefault('clownAvailability', null);
    }
}
