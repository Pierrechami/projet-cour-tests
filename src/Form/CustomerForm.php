<?php

namespace App\Form;

use App\Entity\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('defaultShippingAddress', AddressForm::class, [
                'label' => 'Adresse de livraison',
            ])
            ->add('defaultBillingAddress', AddressForm::class, [
                'label' => 'Adresse de facturation',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
