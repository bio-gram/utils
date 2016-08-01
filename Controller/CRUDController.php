<?php

namespace PrivateDev\Utils\Controller;

use Doctrine\ORM\EntityRepository;
use PageBundle\ErrorCodes;
use PrivateDev\Utils\Form\FormErrorAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class CRUDController extends Controller
{
    const ACTION_CREATE = 1;
    const ACTION_READ = 2;
    const ACTION_UPDATE = 3;
    const ACTION_DELETE = 4;

    /**
     * Roles for actions
     *
     * Note: Null role means no restrictions
     *
     * @var array
     */
    protected static $roles = [];

    /**
     * Get repository of the Entity
     *
     * @return EntityRepository
     */
    abstract protected function getEntityRepository();

    /**
     * Create Form for the Entity
     *
     * @param object $entity
     *
     * @return FormInterface
     */
    abstract protected function createEntityForm($entity) : FormInterface;

    /**
     * Create transformer for the Entity
     *
     * @return object
     */
    abstract protected function createEntityTransformer();

    /**
     * Create an empty Entity
     *
     * @return object
     */
    abstract protected function createEntity();

    /**
     * @param int $action
     *
     * @return string|null
     */
    protected function getAccessRole(int $action)
    {
        return isset($this::$roles[$action])
            ? $this::$roles[$action]
            : null;
    }

    /**
     * @param object  $entity
     * @param Request $request
     *
     * @return JsonResponse
     */
    protected function doUpdate($entity, Request $request)
    {
        $responseBuilder = $this->get('json_response_builder');

        $form = $this->createEntityForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($entity);
            $em->flush($entity);
            $response = $responseBuilder
                ->setTranformableItem($entity, $this->createEntityTransformer())
                ->build();
        } else {
            $response = $responseBuilder
                ->addErrorList(new FormErrorAdapter($form->getErrors(true), ErrorCodes::VALIDATION_ERROR))
                ->build(JsonResponse::HTTP_BAD_REQUEST);
        }

        return $response;
    }

    /**
     * @Route()
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $role = $this->getAccessRole(self::ACTION_CREATE);

        if ($role && !$this->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $entity = $this->createEntity();

        return $this->doUpdate($entity, $request);
    }

    /**
     * @Route(path="/{id}")
     * @Method({"GET"})
     *
     * @param $id
     *
     * @return Response
     */
    public function readAction($id)
    {
        $entity = $this->getEntityRepository()->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $this->get('json_response_builder')
            ->setTranformableItem($entity, $this->createEntityTransformer())
            ->build();
    }

    /**
     * @Route(path="/{id}")
     * @Method({"PUT", "PATCH"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request, $id)
    {
        $role = $this->getAccessRole(self::ACTION_UPDATE);

        if ($role && !$this->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $entity = $this->getEntityRepository()->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $this->doUpdate($entity, $request);
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        $role = $this->getAccessRole(self::ACTION_DELETE);

        if ($role && !$this->isGranted($role)) {
            throw new AccessDeniedHttpException();
        }

        $entity = $this->getEntityRepository()->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->remove($entity);
        $em->flush($entity);

        return $this
            ->get('json_response_builder')
            ->build();
    }
}