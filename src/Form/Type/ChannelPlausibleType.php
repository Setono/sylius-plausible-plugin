<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Form\Type;

use Setono\SyliusPlausiblePlugin\Form\DataTransformer\ScriptIdentifierTransformer;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<ChannelInterface> */
final class ChannelPlausibleType extends AbstractType
{
    public function __construct(private readonly string $dataClass)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plausibleScriptIdentifier', TextareaType::class, [
            'label' => 'setono_sylius_plausible.form.channel.plausible_script_identifier',
            'required' => false,
            'attr' => [
                'placeholder' => 'pa-hb0WlWkUb5U3qhSS-vd-a or full URL or HTML snippet',
                'rows' => 5,
            ],
        ]);

        $builder->get('plausibleScriptIdentifier')->addModelTransformer(new ScriptIdentifierTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'setono_sylius_plausible_channel_plausible';
    }
}
