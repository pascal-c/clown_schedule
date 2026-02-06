<?php

namespace App\Form;

use App\Entity\PlayDate;
use App\Repository\ClownRepository;
use App\Repository\ConfigRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PlayDateGiveOffRequestCreateFormType extends AbstractType
{
    public function __construct(private ClownRepository $clownRepository, private ConfigRepository $configRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var PlayDate $playDate */
        $playDate = $options['playDateToGiveOff'];

        $choices = [];
        $choices['--- bitte wählen ---'] = null;
        $choices['Gruppe'] = $this->groupChoices($playDate);
        if ($this->hasTeam($playDate)) {
            $choices['Einzelne aus Team '.$playDate->getVenue()->getName()] = $this->teamChoices($playDate);
        }
        $choices['Einzelne Clowns'] = $this->individualChoices($playDate);


        $builder
            ->add('requestedTo', ChoiceType::class, [
                'choices' => $choices,
                'required' => true,
                'label' => 'An wen soll die Anfrage gesendet werden?',
                'expanded' => false,
                'multiple' => false,
                'constraints' => new NotBlank(),
                'help' => '',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Hier kannst Du eine Nachricht an die anderen Clownis hinterlassen.',
                'required' => false,
                'attr' => ['placeholder' => 'optional'],
            ])
            ->add('accept', SubmitType::class, [
                'label' => 'Abgabe-Anfrage senden',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('playDateToGiveOff', null);
    }

    private function groupChoices(PlayDate $playDate): array
    {
        $choices = [];
        $choices['alle Clowns'] = 'all';
        if ($this->hasTeam($playDate)) {
            $choices['alle im Team '.$playDate->getVenue()->getName()] = 'team';
        }

        return $choices;
    }

    private function individualChoices(PlayDate $playDate): array
    {
        $choices = [];
        foreach ($this->clownRepository->allActive() as $clown) {
            // Skip playing clowns
            if ($playDate->getPlayingClowns()->contains($clown)) {
                continue;
            }
            $choices[$clown->getName()] = $clown->getId();
        }

        return $choices;
    }

    private function teamChoices(PlayDate $playDate): array
    {
        $choices = [];
        foreach ($playDate->getVenue()->getTeam() as $clown) {
            // Skip playing clowns
            if ($playDate->getPlayingClowns()->contains($clown)) {
                continue;
            }
            $choices[$clown->getName()] = $clown->getId();
        }

        return $choices;
    }

    private function hasTeam(PlayDate $playDate): bool
    {
        return $this->configRepository->isFeatureTeamsActive() && $playDate->getVenue()?->isTeamActive() && $playDate->getVenue()->getTeam()->count() > 0;
    }
}
