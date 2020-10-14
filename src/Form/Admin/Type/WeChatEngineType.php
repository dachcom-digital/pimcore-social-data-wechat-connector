<?php

namespace SocialData\Connector\WeChat\Form\Admin\Type;

use SocialData\Connector\WeChat\Model\EngineConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeChatEngineType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('appId', TextType::class);
        $builder->add('appSecret', TextType::class);
//        $builder->add('accessToken', TextType::class); todo: ?? https://github.com/garbetjie/wechat-php#caching-access-tokens
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class'      => EngineConfiguration::class
        ]);
    }
}
