<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;

class TransporterSelectionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];

        foreach ($options['carrier_choices'] as $carrier) {
            $label = sprintf(
                '<strong>%s</strong> (%s€) - %s<br>Zone : %s | Note : %s/5<br>Contact : %s, %s',
                htmlspecialchars($carrier['name']),
                htmlspecialchars($carrier['price']),
                htmlspecialchars($carrier['service_type']),
                htmlspecialchars($carrier['area_served']),
                htmlspecialchars($carrier['average_rating']),
                htmlspecialchars($carrier['contact_email']),
                htmlspecialchars($carrier['phone'])
            );

            if (!empty($carrier['features'])) {
                $label .= '<br><em>Options :</em> ' . implode(', ', array_map('htmlspecialchars', $carrier['features']));
            }

            $choices[$label] = $carrier['id']; 
        }

        $builder
            ->add('carrierId', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Choisissez un transporteur',
                'label_html' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('carrier_choices');
        $resolver->setDefaults([
            'carrier_choices' => [],
        ]);
    }
}
