<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderDate', null, [
                'widget' => 'single_text',
            ])
            ->add('carrierId')
            ->add('paymentId')
            ->add('orderTotal')
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'id',
            ])
            ->add('shippingAddress', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
            ])
            ->add('billingAddress', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
