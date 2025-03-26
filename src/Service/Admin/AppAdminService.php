<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service global pour l'administration
 */

namespace App\Service\Admin;

use App\Utils\System\Options\OptionSystemKey;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AppAdminService extends AppAdminHandlerService
{

    /**
     * Structure de la réponse d'un appel AJAX
     * @var array|
     */
    private array $ajaxResponse = [
        'success' => false,
        'msg' => ''
    ];

    /**
     * Retourne le repository en fonction de l'entité
     * @param string $entity
     * @return EntityRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getRepository(string $entity): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entity);
    }

    /**
     * Permet de sauvegarder une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function save(mixed $entity, bool $flush = true): void
    {
        try {
            $repo = $this->getRepository($entity::class);
            $repo->save($entity, $flush);
            $this->ajaxResponse['success'] = true;
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
            $this->ajaxResponse['success'] = false;
        }
    }

    /**
     * Construit une réponse AJAX sous la forme d'un tableau avec les clés suivantes : <br />
     * 'success' → true | false<br/>
     * 'msg' → string
     * @param string|null $successMessage
     * @param string|null $errorMessage
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getResponseAjax(string $successMessage = null, string $errorMessage = null): array
    {
        $translator = $this->getTranslator();

        if ($errorMessage === null) {
            $errorMessage = $translator->trans('response.ajax.error');
        }

        if ($successMessage === null) {
            $successMessage = $translator->trans('response.ajax.success');
        }

        if ($this->ajaxResponse['success']) {
            $this->ajaxResponse['msg'] = $successMessage;
        } else {
            $this->ajaxResponse['msg'] = $errorMessage;
        }
        return $this->ajaxResponse;
    }

    /**
     * Permet de supprimer une entité
     * @param mixed $entity
     * @param bool $flush
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function remove(mixed $entity, bool $flush = true): void
    {
        try {
            $repo = $this->getRepository($entity::class);
            $repo->remove($entity, $flush);
            $this->ajaxResponse['success'] = true;
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
            $this->ajaxResponse['success'] = false;
        }
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
        return $this->getContainerBag()->get('security.authorization_checker')->isGranted($attribute, $subject);
    }


    /**
     * Retourne une entité en fonction de son id
     * @param string $entity
     * @param int $id
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findOneBy(string $entity, string $field, mixed $value): ?object
    {
        $repo = $this->getRepository($entity);
        return $repo->findOneBy([$field => $value]);
    }

    /**
     * Retourne une liste d'entité en fonction de critères
     * @param string $entity
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findBy(
        string $entity,
        array  $criteria = [],
        array  $orderBy = [],
        int    $limit = null,
        int    $offset = null
    ): array
    {
        $repo = $this->getRepository($entity);
        return $repo->findBy($criteria, $orderBy, $limit, $offset);
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

        return $serializer->denormalize(
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLocales(): array
    {
        $optionSystemService = $this->getOptionSystemService();
        if ($this->getRequestStack()->getCurrentRequest() !== null) {
            $current =  $this->getRequestStack()->getCurrentRequest()->getLocale();
        }
        else {
            $current = $optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        }

        $locales = explode('|', $this->getParameterBag()->get('app.supported_locales'));
        array_unshift($locales, $current);
        $localesTranslate = [];

        foreach ($locales as $locale) {
            $localesTranslate[$locale] = $this->getTranslator()->trans('global.' . $locale);
        }

        return [
            'locales' => array_values(array_unique($locales)),
            'localesTranslate' => $localesTranslate,
            'current' => $current
        ];
    }

}
