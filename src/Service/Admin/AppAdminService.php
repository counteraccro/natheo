<?php

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service global pour l'administration
 */

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppAdminService
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * Paramètre globaux de Symfony
     * @var ContainerBagInterface
     */
    protected ContainerBagInterface $containerBag;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var UrlGeneratorInterface
     */
    protected UrlGeneratorInterface $router;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var ParameterBagInterface
     */
    protected ParameterBagInterface $parameterBag;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack,
        ParameterBagInterface  $parameterBag
    )
    {
        $this->requestStack = $requestStack;
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->router = $router;
        $this->security = $security;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Retourne le repository en fonction de l'entité
     * @param string $entity
     * @return EntityRepository
     */
    protected function getRepository(string $entity): EntityRepository
    {
        return $this->entityManager->getRepository($entity);
    }

    /**
     * Permet de sauvegarder une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function save(mixed $entity, bool $flush = true): void
    {
        $repo = $this->getRepository($entity::class);
        $repo->save($entity, $flush);
    }

    /**
     * Permet de supprimer une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     */
    public function remove(mixed $entity, bool $flush = true): void
    {
        $repo = $this->getRepository($entity::class);
        $repo->remove($entity, $flush);
    }


    /**
     * Permet de vérifier les droits
     * @param mixed $attribute
     * @param mixed|null $subject
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->containerBag->get('security.authorization_checker')->isGranted($attribute, $subject);
    }


    /**
     * Retourne une entité en fonction de son id
     * @param string $entity
     * @param int $id
     * @return object|null
     */
    public function findOneById(string $entity, int $id): ?object
    {
        return $this->findOneBy($entity, 'id', $id);
    }

    /**
     * Retourne une entité en fonction de son champ et valeur associé
     * @param string $entity
     * @param string $field
     * @param mixed $value
     * @return object|null
     */
    public function findOneBy(string $entity, string $field, mixed $value): ?object
    {
        $repo = $this->getRepository($entity);
        return $repo->findOneBy([$field => $value]);
    }

    /**
     * Convertie une entité en tableau PHP
     * @param object $object
     * @param array $ignoredAttributes
     * @return array
     * @throws ExceptionInterface
     */
    public function convertEntityToArray(object $object, array $ignoredAttributes = []): array
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], []);

        return $serializer->normalize($object, null);
    }

    /**
     * Convertie tout type de donnée en Json
     * @param $objects
     * @param array $ignoredAttributes
     * @return string
     */
    public function convertObjectsToJson($objects, array $ignoredAttributes = []): string
    {

        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredAttributes
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objects, 'json', $defaultContext);
    }


    /**
     * Met à jour l'objet entity avec les données de $array
     * @param array $array
     * @param string $objectClass
     * @param object $object
     * @return object
     */
    public function convertArrayToEntity(array $array, string $objectClass, object $object): object
    {
        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $normalizer = new ObjectNormalizer(null, null, null, $extractor);
        $serializer = new Serializer([$normalizer, new ArrayDenormalizer()]);

        return  $serializer->denormalize(
            $array,
            $objectClass,
            null,
            [
                AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                AbstractNormalizer::OBJECT_TO_POPULATE => $object
            ]
        );
    }

    /**
     * Retourne un tableau contenant 3 données en fonctions des locales <br />
     *  ['locales'] => liste des locales du site <br />
     *  ['localesTranslate'] => listes des locales avec traduction dans la langue courante<br />
     * ['current'] => la langue courante
     * @return array
     */
    public function getLocales(): array
    {
        $current = $this->requestStack->getCurrentRequest()->getLocale();

        $locales = explode('|', $this->parameterBag->get('app.supported_locales'));
        array_unshift($locales, $current);
        $localesTranslate = [];

        foreach ($locales as $locale) {
            $localesTranslate[$locale] = $this->translator->trans('global.' . $locale);
        }

        return [
            'locales' => array_values(array_unique($locales)),
            'localesTranslate' => $localesTranslate,
            'current' => $current
        ];
    }

}
